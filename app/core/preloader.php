<?php

class Preloader
{
	public static function check_cookie()
	{
		include_once Config::get('app').'models/model_auth.php';
		$model = new Model_Auth;
		
		//  проверяю куки
		$user_r = isset($_COOKIE['user_r']) ? $_COOKIE['user_r'] : false;
		// если есть кука - идем дальше
		if ($user_r) {
			// разбиваем ее на подстроки
			list($identifier, $token) = explode('___', $user_r);
			
			$identifier = trim($identifier);
			$token      = trim($token);
			
			// проверяем на пустоту
			if (!empty($identifier) && !empty($token)) {
				// пробуем получить запись пользователя по remember id
				if ($user = $model->check_remember_cred($identifier, $token)) {
					$_SESSION['user']['auth']     = true;
					$_SESSION['user']['login']    = $user['login'];
					$_SESSION['user']['is_admin'] = $user['is_admin'];
				} else {
					$model->clear_remembers($token, $identifier);
					setcookie("user_r", "", time() - 3600, '/');
				}
			}
		}
	}
}