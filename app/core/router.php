<?php

class Router
{
	/**
	 * Запускает маршрутизацию
	 */
	static function start()
	{
		// контроллер и действие по умолчанию
		$controller_name = 'Main';
		$action_name     = 'index';
		
		$routes = explode('/', $_SERVER['REQUEST_URI']);
		
		// получаем имя контроллера
		if (!empty($routes[1])) $controller_name = $routes[1];
		// получаем имя экшена
		if (!empty($routes[2])) $action_name = explode("?", $routes[2])[0];
		
		// добавляем префиксы
		$model_name      = 'Model_' . $controller_name;
		$controller_name = 'Controller_' . $controller_name;
		$action_name     = 'action_' . $action_name;
		
		// подцепляем файл с классом модели (файла модели может и не быть)
		$model_file = strtolower($model_name) . '.php';
		$model_path = "../app/models/" . $model_file;
		if (file_exists($model_path)) {
			include_once Config::get('app')."models/" . $model_file;
		}
		
		// подцепляем файл с классом контроллера
		$controller_file = strtolower($controller_name) . '.php';
		$controller_path = Config::get('app')."controllers/" . $controller_file;
		if (file_exists($controller_path)) {
			require_once Config::get('app')."controllers/" . $controller_file;
		} else Router::redirect('404');
		
		// создаем контроллер
		$controller = new $controller_name;
		$action     = $action_name;
		
		if (method_exists($controller, $action)) {
			// вызываем действие контроллера
			$controller->$action();
		} else {
			if (method_exists($controller, 'action_index')) $controller->action_index();
			else Router::redirect('404');
		}
	}

	/**
	 * Перенаправялет на нужную страницу
	 * @param $url
	 * Адрес страницы
	 */
	public static function redirect($url = '')
	{
		header('Location: /' . trim($url, '/'));
		exit();
	}
	 
	/**
	 * Получение сегмента из url
	 * @param $pos
	 * Номер позиции сегмента
	 * @param bool $return
	 * Будет возвращено это значение, в случае отстутствия сегмента
	 * @return bool
	 */
	public static function get_segment($pos, $return = false)
	{
		if (empty($pos)) return false;
		
		$args = explode('/', $_SERVER['REQUEST_URI']);
		return isset($args[$pos]) ? $args[$pos] : $return;
	}
	
	/**
	 * Возвращает все аргументы из адрескной строки, кроме маршрутов
	 * @return array
	 */
	public static function get_args()
	{
		$result = [];
		$args   = explode('/', $_SERVER['REQUEST_URI']);
		
		for ($i = 0; $i < 3; $i++) {
			if (isset($args[$i])) unset($args[$i]);
		}
		
		foreach ($args as $value) {
			$result[] = $value;
		}
		
		return $result;
	}
	
	/**
	 * Возвращает на предыдущий адрес
	 */
	public static function go_back()
	{
		$last_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$last_uri = str_replace('http://' . $_SERVER['HTTP_HOST'], '', $last_url);
		Router::redirect($last_uri);
	}
}