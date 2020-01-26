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
use Vkovic\LaravelCommando\Handlers\Database\WithDbHandler;

class InstallPreparationController extends Controller
{
    use WithDbHandler;
    /**
     * Publish installer assets and check server requirements
     * 
     * @param  Request $request
     * @return view
     */
    public function requirements(Request $request)
    {
        \Artisan::call('vendor:publish', ['--tag' => 'installer-assets', '--force' => true]);
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
        $mandatory = \Module::getCoreModules();
        $optionnal = \Module::getNonCoreModules();
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
        $modules = $request->validated()['modules'] ?? [];
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
        if (!is_array(session('installer.modules', false))) {
            return redirect()->route('install');
        }
        return view('installer::perform');
    }

    /**
     * Checks if a database exists 
     * 
     * @param  Request $request
     * @return array
     */
    public function checkDatabase(Request $request)
    {
        $name = $request->get('database', false);
        if ($name) {
            if ($this->dbHandler()->databaseExists($name)) {
                return ['exists' => true];
            }
        }
        return ['exists' => false];
    }
}
