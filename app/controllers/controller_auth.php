<?php

class Controller_Auth extends Controller
{
	public function action_index()
	{
		Router::redirect('/auth/login');
	}
	
	// регистрация
	public function action_register()
	{
		if ($_SESSION['user']['auth']) Router::redirect('/');
		
		// создаем объект модели
		$model = new Model_Auth();
		// создаем объект отображения
		$view = new View();
		
		// если пришла форма - обрабатываем
		if (count($_POST) > 0) {
			// получаем данные с модели
			if ($model->register($_POST)) {
				$_SESSION['messages']['info'] = $model->get_messages('info');
				Router::redirect('/auth/register');
			} else {
				$_SESSION['savepost']          = $_POST;
				$_SESSION['messages']['error'] = $model->get_messages('error');
				Router::redirect('/auth/register');
			}
		}
		
		// savepost
		if (isset($_SESSION['savepost'])) {
			$this->data['savepost'] = $_SESSION['savepost'];
			unset($_SESSION['savepost']);
		}
		// получаю ошибки, если они есть
		if (isset($_SESSION['messages'])) {
			$this->data['messages'] = $_SESSION['messages'];
			unset($_SESSION['messages']);
		}
		
		$this->data['page_title'] = 'Регистрация';

		$this->data['js'] = '<script src="https://www.google.com/recaptcha/api.js"></script>';
		// передаем данные во вью, рисуем ее
		$view->draw('auth/view_register.php', 'templates/template.php', $this->data);
	}
	
	// подтверждение аккаунта
	public function action_confirm()
	{
		// создаем объект модели
		$model = new Model_Auth();
		// создаем объект отображения
		$view = new View();
		
		$uri_login = isset($_GET['login']) ? $_GET['login'] : '';
		$uri_code  = isset($_GET['code']) ? $_GET['code'] : '';
		
		$this->data['is_activated'] = false;
		
		// получаем данные с модели
		if ($model->activate_account($uri_login, $uri_code)) {
			$this->data['is_activated'] = true;
		}
		
		$this->data['page_title'] = 'Подтверждение регистрации';
		// передаем данные во вью, рисуем ее
		$view->draw('auth/view_confirm.php', 'templates/template.php', $this->data);
	}
	
	// вход
	public function action_login()
	{
		if ($_SESSION['user']['auth'] == true) Router::redirect('/');
		
		// создаем объекты модели и вьюшки
		$model = new Model_Auth;
		$view  = new View();
		
		// ip пользователя
		$client_ip = $_SERVER['REMOTE_ADDR'];
		
		// если пришла форма - обрабатываем
		if (count($_POST) > 0) {
			if ($info = $model->authorize($_POST)) {
				$_SESSION['user']['auth']     = true;
				$_SESSION['user']['login']    = $info['login'];
				$_SESSION['user']['is_admin'] = $info['is_admin'];
				$_SESSION['user']['attempts'] = 0;
				Router::redirect('/');
			} else {
				// получаю из конфига количество попыток входа
				$attempts = Config::get('attempts', 3);
				// добавляю неудачную попытку
				$_SESSION['user']['attempts'] += 1;
				// если попыток входа более установленного - блочим
				if ($_SESSION['user']['attempts'] > $attempts) {
					if ($model->block_user($client_ip)) $_SESSION['user']['attempts'] = 0;
				}
				
				$_SESSION['savepost']          = $_POST;
				$_SESSION['messages']['error'] = $model->get_messages('error');
				Router::redirect('/auth/login');
			}
		}
		
		// -----------------------------
		
		// проверяю блок у юзера
		$block_info = $model->block_check($client_ip);
		if ($block_info) {
			$this->data['content']['is_disabled']   = 'disabled';
			$this->data['content']['block_message'] = 'Ваш IP адрес заблокирован до ' . date("d.m.Y H:i:s", $block_info);
		}
		
		// savepost
		if (isset($_SESSION['savepost'])) {
			$this->data['savepost'] = $_SESSION['savepost'];
			unset($_SESSION['savepost']);
		}
		// получаю ошибки, если они есть
		if (isset($_SESSION['messages'])) {
			$this->data['messages'] = $_SESSION['messages'];
			unset($_SESSION['messages']);
		}
		
		$this->data['page_title'] = 'Авторизация';
		// передаем данные во вью, рисуем ее
		$view->draw('auth/view_login.php', 'templates/template.php', $this->data);
	}
	
	// выход
	public function action_logout()
	{
		// создаем объекты модели и вьюшки
		$model = new Model_Auth;
		
		// вытаскиваю из куки данные
		$user_r = isset($_COOKIE['user_r']) ? $_COOKIE['user_r'] : false;
		
		if ($user_r) {
			// разбиваем ее на подстроки
			list($identifier, $token) = explode('___', $user_r);
			
			$identifier = trim($identifier);
			$token      = trim($token);
			
			// чищу инфу о remember_me
			$model->clear_remembers($token);
			setcookie("user_r", "", time() - 3600, '/');
		}
		
		
		// очищаю сессию
		$_SESSION['user']['auth']     = false;
		$_SESSION['user']['is_admin'] = false;
		$_SESSION['user']['login']    = '';
		
		Router::redirect("/auth/login");
	}
	
	// восстановление пароля
	public function action_restore()
	{
		if ($_SESSION['user']['auth'] == true) Router::redirect('/');
		
		// создаем объекты модели и вьюшки
		$model = new Model_Auth;
		$view  = new View();

		// если пришла форма - обрабатываем
		if (count($_POST) > 0) {
			if ($info = $model->send_restore_link($_POST)) {
				$_SESSION['messages']['info'] = $model->get_messages('info');
			} else {
				$_SESSION['savepost']          = $_POST;
				$_SESSION['messages']['error'] = $model->get_messages('error');
			}
			
			Router::redirect('/auth/restore');
		}
		
		// savepost
		if (isset($_SESSION['savepost'])) {
			$this->data['savepost'] = $_SESSION['savepost'];
			unset($_SESSION['savepost']);
		}
		// получаю ошибки, если они есть
		if (isset($_SESSION['messages'])) {
			$this->data['messages'] = $_SESSION['messages'];
			unset($_SESSION['messages']);
		}
		
		$this->data['page_title'] = 'Восстановление доступа';
		$this->data['js'] = '<script src="https://www.google.com/recaptcha/api.js"></script>';
		// передаем данные во вью, рисуем ее
		$view->draw('auth/view_restore.php', 'templates/template.php', $this->data);
	}
	
	// сброс пароля
	public function action_reset()
	{
		if ($_SESSION['user']['auth'] == true) Router::redirect('/');
		
		// создаем объекты модели и вьюшки
		$model = new Model_Auth;
		$view  = new View();
		
		// если пришла форма - обрабатываем
		if (count($_POST) > 0) {
			if ($info = $model->reset_password($_POST)) {
				$_SESSION['messages']['info'] = $model->get_messages('info');
				Router::redirect('/auth/login');
			} else {
				$_SESSION['savepost']          = $_POST;
				$_SESSION['messages']['error'] = $model->get_messages('error');
				Router::redirect('/auth/reset?email=' . $_POST['email'] . '&code=' . $_POST['code']);
			}
			
		}
		
		// забираю переменный их адресной строки
		$uri_email = isset($_GET['email']) ? $_GET['email'] : '';
		$uri_code  = isset($_GET['code']) ? $_GET['code'] : '';
		
		if (!$model->check_hash($uri_email, $uri_code)) {
			Router::redirect('/');
		}
		
		// savepost
		if (isset($_SESSION['savepost'])) {
			$this->data['savepost'] = $_SESSION['savepost'];
			unset($_SESSION['savepost']);
		}
		// получаю ошибки, если они есть
		if (isset($_SESSION['messages'])) {
			$this->data['messages'] = $_SESSION['messages'];
			unset($_SESSION['messages']);
		}
		
		$this->data['email'] = $uri_email;
		$this->data['code']  = $uri_code;
		
		$this->data['page_title'] = 'Сброс пароля';
		// подключаю необходимые скрипты
		$this->data['js'] = '<script src="/js/pass_check.js"></script>';
		// передаем данные во вью, рисуем ее
		$view->draw('auth/view_reset.php', 'templates/template.php', $this->data);
	}
}