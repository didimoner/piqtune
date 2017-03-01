<?php

/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 05.07.2016
 * Time: 12:38
 */
class Config
{
	/**
	 * Class Method
	 *
	 * @param string $param name of configs data
	 * @param any $replace if no param in configs - replace with that
	 * @return bool|string
	 */
	public static function get($param, $replace = '')
	{
		if (!$param) return false;
		
		// подключаю файл конфига
		$config = include('../configs/config.php');
		
		if (isset($config[$param])) return $config[$param];
		else return $replace;
	}
}