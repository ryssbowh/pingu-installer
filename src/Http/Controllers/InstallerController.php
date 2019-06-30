<?php

namespace PinguInstaller\Http\Controllers;

use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use PinguInstaller\Components\RequirementChecker;
use PinguInstaller\Events\PinguInstalled;
use PinguInstaller\Http\Requests\EnvRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class InstallerController extends Controller
{
    protected function checkSession(Request $request)
    {
        if(!session('installer.modules', false)){
            throw new HttpException(422, "Go away");
        }
    }

    protected function runArtisanCommand(string $command, array $options = [])
    {
        \Artisan::call($command, $options);
        return [];
    }

    protected function runCommand($command)
    {
        chdir(base_path());
        $process = new Process($command);
        $process->run();
        if(!$process->isSuccessful()){
            throw new ProcessFailedException($process);
        }
        return str_replace("\n", '', $process->getOutput());
    }

    public function stepEnv(Request $request)
    {
        $this->checkSession($request);
        $env = "";
        foreach(session('installer.env') as $name => $value){
            $env .= $name.'='.$value."\n";
        }
        \File::put(base_path('.env'), $env);
        return [];
    }

    public function stepConfig(Request $request)
    {
        $this->checkSession($request);
        return $this->runArtisanCommand('module:publish-config');
    }

    public function stepModules(Request $request)
    {
        $this->checkSession($request);
        foreach(session('installer.modules') as $name){
            \Module::find($name)->enable();
        }
        return [];
    }

    public function stepMigrate(Request $request)
    {
        $this->checkSession($request);
        return $this->runArtisanCommand('migrate');
    }

    public function stepMigrateModules(Request $request)
    {
        $this->checkSession($request);
        return $this->runArtisanCommand('module:migrate');
    }

    public function stepSeed(Request $request)
    {
        $this->checkSession($request);
        return $this->runArtisanCommand('module:seed');
    }

    public function stepNode(Request $request)
    {
        $this->checkSession($request);
        $this->runCommand('npm run merge');
        $this->runCommand('npm install');
        return [];
    }

    public function stepAssets(Request $request)
    {
        $this->checkSession($request);
        $script = env('APP_ENV', 'local') == 'local' ? 'development' : 'production';
        $this->runCommand('npm run '.$script);
        return [];
    }

    public function stepSymStorage(Request $request)
    {
        $this->checkSession($request);
        return $this->runArtisanCommand('storage:link');
    }

    public function stepSymModules(Request $request)
    {
        $this->checkSession($request);
        return $this->runArtisanCommand('module:link');
    }

    public function stepSymThemes(Request $request)
    {
        $this->checkSession($request);
        return $this->runArtisanCommand('theme:link');
    }

    public function stepCache(Request $request)
    {
        $this->checkSession($request);
        $this->runArtisanCommand('cache:clear');
        return $this->stepFinal($request);
    }

    protected function stepFinal(Request $request)
    {
        $this->checkSession($request);
        \File::put(storage_path('installed'),'');
        event(new PinguInstalled);
        $request->session()->forget('installer');
        \File::append(base_path('.env'), 'APP_KEY='.$this->generateRandomKey());
        return [];
    }

    protected function generateRandomKey()
    {
        return 'base64:'.base64_encode(
            Encrypter::generateKey(config('app.cipher'))
        );
    }
}
