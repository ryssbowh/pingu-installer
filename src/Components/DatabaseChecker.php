<?php
namespace PinguInstaller\Components;

class DatabaseChecker
{
	/**
	 * Checks a database connection
	 * 
	 * @param  string $driver
	 * @param  string $host
	 * @param  string $name
	 * @param  string $username
	 * @param  string $password
	 * @return bool
	 */
	public static function checkConnection(string $driver, string $host, string $name, string $username, string $password)
	{
		switch ($driver) {
			case 'mysql':
				return static::checkMysql($host, $name, $username, $password);
		}
		return false;
	}

	/**
	 * Checks a Mysql connection
	 * 
	 * @param  string $host
	 * @param  string $name
	 * @param  string $username
	 * @param  string $password
	 * @return bool
	 */
	public static function checkMysql(string $host, string $name, string $username, string $password)
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