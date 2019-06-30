<?php
namespace PinguInstaller\Components;

class DatabaseChecker
{
	public static function checkConnection($driver, $host, $name, $username, $password)
	{
		switch ($driver) {
			case 'mysql':
				return static::checkMysql($host, $name, $username, $password);
		}
		return false;
	}

	public static function checkMysql($host, $name, $username, $password)
	{
		try{
            $con = mysqli_connect($host, $username, $password, $name);
        }
        catch(\ErrorException $e){
            return false;
        }
        return true;
	}
}