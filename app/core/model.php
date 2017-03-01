<?php

class Model
{
	protected $msg_error;
	protected $msg_info;
	protected $db;
	
	public function __construct()
	{
		$this->msg_error = [];
		$this->msg_info  = [];
		$this->db        = $this->connect_db();
	}
	
	/**
	 * Устанавлиает соединение с БД
	 * @return SafeMySQL
	 */
	public function connect_db()
	{
		if ($cfg = Config::get('db')) {
			
			$opts = array(
				'user'    => $cfg['login'],
				'pass'    => $cfg['password'],
				'host'    => $cfg['host'],
				'db'      => $cfg['name'],
				'charset' => $cfg['charset']
			);
			return new SafeMySQL($opts); // with some of the default settings overwritten
		} else die('Не указана информация для подключения к БД в кофигурации!');
	}

	/**
	 * Возвращает сообщения модели
	 * @param $type
	 * Тип сообщения
	 * @return array|bool
	 */
	public function get_messages($type)
	{
		if ($type == 'error') return $this->msg_error;
		elseif ($type == 'info') return $this->msg_info;
		
		return false;
	}
}