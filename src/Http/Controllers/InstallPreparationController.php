<?php

namespace PinguInstaller\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use PinguInstaller\Components\RequirementChecker;
use PinguInstaller\Events\PinguInstalled;
use PinguInstaller\Http\Requests\EnvRequest;
use PinguInstaller\Http\Requests\ModuleRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class InstallPreparationController extends Controller
{
    /**
     * Publish installer assets and check server requirements
     * 
     * @param  Request $request
     * @return view
     */
    public function requirements(Request $request)
    {
        \Artisan::run('vendor:publish', ['--tag' => 'installer-assets']);
        $checker = new RequirementChecker;
        $checks = $checker->checkAll();
        $failure = $checker->hasFailed();
        $request->session()->forget('installer');
        if(!$failure){
            session(['installer.requirements' => true]);
        }
        return view('installer::requirements')->with(['checks' => $checks, 'failure' => $failure]);
    }

    /**
     * .env file
     * 
     * @param  Request $request
     * @return view
     */
    public function env(Request $request)
    {
        if(!session('installer.requirements', false)){
            return redirect()->route('install');
        }
        return view('installer::env');
    }

    /**
     * .env file post handler
     * 
     * @param  EnvRequest $request
     * @return redirect
     */
    public function postEnv(EnvRequest $request)
    {
        if(!session('installer.requirements', false)){
            return redirect()->route('install');
        }
        $validated = $request->validated();
        session(['installer.env' => $validated]);
        return redirect()->route('install.modules');
    }

    /**
     * Builds module list (mandatory and optionnal) from disk
     * 
     * @param  Request $request
     * @return view
     */
    public function modules(Request $request)
    {
        if(!session('installer.env', false)){
            return redirect()->route('install');
        }
        $modules = \Module::all();
        usort($modules, function($module1, $module2){
            return ($module1->get('order') > $module2->get('order')) ? 1 : 0;
        });
        $mandatory = [];
        $optionnal = [];
        foreach($modules as $module){
            if(in_array($module->getName(), config('installer.mandatoryModules'))){
                $mandatory[] = $module;
            }
            else{
                $optionnal[] = $module;
            }
        }
        return view('installer::modules')->with(['mandatory' => $mandatory, 'optionnal' => $optionnal]);
    }

    /**
     * Modules post handler
     * 
     * @param  ModuleRequest $request
     * @return redirect
     */
    public function postModules(ModuleRequest $request)
    {
        if(!session('installer.env', false)){
            return redirect()->route('install');
        }
        $optionnal = $request->validated()['modules'] ?? [];
        $modules = array_merge(config('installer.mandatoryModules'), $optionnal);
        session(['installer.modules' => $modules]);
        return redirect()->route('install.perform');
    }

    /**
     * Perform install view
     * 
     * @param  Request $request
     * @return view
     */
    public function perform(Request $request)
    {
        if(!session('installer.modules', false)){
            return redirect()->route('install');
        }
        return view('installer::perform');
    }
}
