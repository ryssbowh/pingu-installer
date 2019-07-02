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
    public function __construct()
    {
        set_time_limit(300);
    }
    /**
     * Check if last step was done (from session)
     * 
     * @param  Request $request
     * @throws HttpException
     */
    protected function checkSession(Request $request)
    {
        if(!session('installer.modules', false)){
            throw new HttpException(422, "Go away");
        }
    }

    /**
     * Runs an artisan command
     * 
     * @param  string $command
     * @param  array  $options
     * @return array
     */
    protected function runArtisanCommand(string $command, array $options = [])
    {
        \Artisan::call($command, $options);
        return [];
    }

    /**
     * Runs a bash command and returns output
     * 
     * @param  string|array $command
     * @return string
     * @throws ProcessFailedException
     */
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

    /**
     * Writes .env file on disk
     * 
     * @param  Request $request
     * @return array
     */
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

    /**
     * Activates chosen modules
     * 
     * @param  Request $request
     * @return array
     */
    public function stepModules(Request $request)
    {
        $this->checkSession($request);
        foreach(session('installer.modules') as $name){
            \Module::find($name)->enable();
        }
        return [];
    }

    /**
     * Run migrate comamnd
     * 
     * @param  Request $request
     * @return array
     */
    public function stepMigrate(Request $request)
    {
        $this->checkSession($request);
        return $this->runArtisanCommand('migrate');
    }

    /**
     * Run module:migrate command
     * 
     * @param  Request $request
     * @return array
     */
    public function stepMigrateModules(Request $request)
    {
        $this->checkSession($request);
        return $this->runArtisanCommand('module:migrate');
    }

    /**
     * Run module:seed command
     * 
     * @param  Request $request
     * @return array
     */
    public function stepSeed(Request $request)
    {
        $this->checkSession($request);
        return $this->runArtisanCommand('module:seed');
    }

    /**
     * install npm dependencies
     * 
     * @param  Request $request
     * @return array
     */
    public function stepNode(Request $request)
    {
        $this->checkSession($request);
        $this->runCommand('npm install');
        $this->runCommand('npm run merge');
        $this->runCommand('npm install');
        return [];
    }

    /**
     * Builds assets
     * 
     * @param  Request $request
     * @return array
     */
    public function stepAssets(Request $request)
    {
        $this->checkSession($request);
        $script = env('APP_ENV', 'local') == 'local' ? 'development' : 'production';
        $this->runCommand('npm run '.$script);
        return [];
    }

    /**
     * Symlink storage
     * 
     * @param  Request $request
     * @return array
     */
    public function stepSymStorage(Request $request)
    {
        $this->checkSession($request);
        return $this->runArtisanCommand('storage:link');
    }

    /**
     * Symlink themes
     * 
     * @param  Request $request
     * @return array
     */
    public function stepSymThemes(Request $request)
    {
        $this->checkSession($request);
        return $this->runArtisanCommand('theme:link');
    }

    /**
     * Clears cache and calls final method
     * 
     * @param  Request $request
     * @return array
     */
    public function stepCache(Request $request)
    {
        $this->checkSession($request);
        $this->runArtisanCommand('cache:clear');
        return $this->stepFinal($request);
    }

    /**
     * Finalise installation. Creates installed file in storage,
     * throws an event, empties session and generates a key to .env file
     * 
     * @param  Request $request
     * @return array
     */
    protected function stepFinal(Request $request)
    {
        $this->checkSession($request);
        \File::put(storage_path('installed'),'');
        event(new PinguInstalled);
        $request->session()->forget('installer');
        \File::append(base_path('.env'), 'APP_KEY='.$this->generateRandomKey());
        return [];
    }

    /**
     * Generates a random key
     * 
     * @param  Request $request
     * @return string
     */
    protected function generateRandomKey()
    {
        return 'base64:'.base64_encode(
            Encrypter::generateKey(config('app.cipher'))
        );
    }
}
