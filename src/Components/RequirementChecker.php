<?php

namespace PinguInstaller\Components;

use Symfony\Component\Process\Process;

class RequirementChecker
{
	protected $checks = [];
	protected $failure = false;

	/**
	 * Checks all requirements
	 * 
	 * @return array
	 */
	public function checkAll(){
		$this->checkPhp();
		$this->checkPhpExtensions();
		$this->checkApacheExtensions();
		$this->checkPermissions();
		$this->checkNpm();
		$this->checkApacheFolderPermissions();
		return $this->checks;
	}

	/**
	 * Has at least one requirement failed
	 * 
	 * @return boolean
	 */
	public function hasFailed()
	{
		return $this->failure;
	}

	/**
	 * Checks php min version
	 */
	public function checkPhp()
	{
		if(version_compare(phpversion(), config('installer.phpMinVersion')) >= 0){
            $this->checks['php'] = true;
        }
        else{
        	$this->checks['php'] = false;
        	$this->failure = true;
        }
	}

	/**
	 * Runs a bash command and return output
	 * 
	 * @param  string|array $command
	 * @return string
	 */
	protected function runCommand($command)
	{
		$command = new Process($command);
		$command->run();
		$output = $command->getOutput();
		$output = str_replace("\n", '', $output);
		return preg_replace('/\s\s+/', '', $output);
	}

	/**
	 * Checks that apache can write on its home folder (usually /var/www), it will need it to run npm
	 */
	public function checkApacheFolderPermissions()
	{
		$user = $this->runCommand('whoami');
		$folder = $this->runCommand('echo ~'.$user);
		$owner = $this->runCommand('ls -ld '.$folder.' | awk \'{print $3}\'');
		$title = 'Apache ('.$user.') can write on '.$folder;
		$permission = substr(sprintf('%o', fileperms($folder)), -4);
		$this->checks['apachePerm']['title'] = $title;
		session(['installer.apacheFolder' => $folder]);
		if($user != $owner and $permission > 700){
			$this->checks['apachePerm']['pass'] = false;
			$this->failure = true;
		}
		else{
			$this->checks['apachePerm']['pass'] = true;
		}
	}

	/**
	 * Checks php extensions
	 */
	public function checkPhpExtensions()
	{
		foreach(config('installer.requirements.php') as $extension){
            if(phpversion($extension)){
                $this->checks['phpExtension'][$extension] = true;
            }
            else{
            	$this->checks['phpExtension'][$extension] = false;
            	$this->failure = true;
            }
        }
	}

	/**
	 * Checks apache extensions
	 */
	public function checkApacheExtensions()
	{
		$modules = apache_get_modules();
        foreach(config('installer.requirements.apache') as $module){
            if(in_array($module, $modules)){
                $this->checks['apacheExtension'][$module] = true;
            }
            else{
            	$this->checks['apacheExtension'][$module] = false;
            	$this->failure = true;
            }
        }
	}

	/**
	 * Checks folder permissions
	 */
	public function checkPermissions()
	{
		foreach(config('installer.permissions') as $folder => $perm){
            $permission = substr(sprintf('%o', fileperms(base_path($folder))), -4);
            if($permission >= $perm){
                $this->checks['permissions'][$folder] = true;
            }
            else{
				$this->checks['permissions'][$folder] = false;
				$this->failure = true;
            }
        }
	}

	/**
	 * Checks npm version
	 */
	public function checkNpm()
	{
		$minVersion = config('installer.minNpmVersion');
		$npmVersion = $this->runCommand('npm --version');
		if(version_compare($npmVersion, $minVersion) < 0){
			$this->checks['commands']['npm version '.$minVersion] = false;
			$this->failure = true;
		}
		else{
			$this->checks['commands']['npm version '.$minVersion] = true;
		}

	}
}