<?php

class Controller_Users extends Controller
{
	public function action_index()
	{
		Router::redirect('/users/profile');
	}
	
	// посты пользователя
	public function action_profile()
	{
		include_once Config::get('app').'models/model_posts.php';
		// создаем объекты модели и вьюшки
		$model = new Model_Posts;
		$view  = new View();
		
		// количество постов на страницу
		$limit = 15;
		// получаю имя пользователя из адресной строки
		$uri_username = Router::get_segment(3);
		
		// если пользователь авторизован - страница по дефолту ведет на его профиль
		if ($_SESSION['user']['auth']) {
			if (empty($uri_username)) $uri_username = $_SESSION['user']['login'];
		}
		
		// получаю посты
		if ($content = $model->get_by_author($uri_username, 0, $limit + 1)) {
			$this->data['quantity'] = count($content);
			unset($content[$limit]);
			$this->data['content'] = $content;
		}
		
		$this->data['username'] = $uri_username;
		
		$this->data['settings']['offset'] = $limit;
		$this->data['settings']['limit']  = $limit;
		
		// получаю ошибки, если они есть
		if (isset($_SESSION['messages'])) {
			$this->data['messages'] = $_SESSION['messages'];
			unset($_SESSION['messages']);
		}
		
		$this->data['page_title'] = 'Мои посты';
		// подключаю необходимые скрипты
		$this->data['js'] = '<script async src="/js/player.js"></script>';
		$this->data['js'] .= '<script async src="/js/loading_posts.js"></script>';
		// передаем данные во вью, рисуем ее
		$view->draw('users/view_profile.php', 'templates/template.php', $this->data);
	}
	
	// профиль
	public function action_favorites()
	{
		if (!$_SESSION['user']['auth']) Router::redirect('/auth/login');
		
		include_once Config::get('app').'models/model_posts.php';
		// создаем объекты модели и вьюшки
		$model = new Model_Posts;
		$view  = new View();
		
		// количество постов на страницу
		$limit = 15;
		
		// получаю избранное
		if ($content = $model->get_favorites($_SESSION['user']['login'], 0, $limit + 1)) {
			$this->data['quantity'] = count($content);
			unset($content[$limit]);
			$this->data['content'] = $content;
		}
		
		$this->data['settings']['offset'] = $limit;
		$this->data['settings']['limit']  = $limit;
		
		// получаю ошибки, если они есть
		if (isset($_SESSION['messages'])) {
			$this->data['messages'] = $_SESSION['messages'];
			unset($_SESSION['messages']);
		}
		
		$this->data['page_title'] = 'Избранное';
		// подключаю необходимые скрипты
		$this->data['js'] = '<script async src="/js/player.js"></script>';
		$this->data['js'] .= '<script async src="/js/loading_posts.js"></script>';
		// передаем данные во вью, рисуем ее
		$view->draw('users/view_favorites.php', 'templates/template.php', $this->data);
	}
	
	// смена пароля
	public function action_settings()
	{
		if (!$_SESSION['user']['auth']) Router::redirect('/auth/login');
		
		include_once Config::get('app').'models/model_auth.php';
		// создаем объекты модели и вьюшки
		$model = new Model_Auth;
		$view  = new View();
		
		// если пришла форма - обрабатываем
		if (count($_POST) > 0) {
			if ($info = $model->change_password($_POST)) {
				$_SESSION['messages']['info'] = $model->get_messages('info');
			} else {
				$_SESSION['savepost']          = $_POST;
				$_SESSION['messages']['error'] = $model->get_messages('error');
			}
			
			Router::redirect('/users/settings');
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
		
		$this->data['page_title'] = 'Смена пароля';
		// подключаю необходимые скрипты
		$this->data['js'] = '<script async src="/js/pass_check.js"></script>';
		// передаем данные во вью, рисуем ее
		$view->draw('users/view_settings.php', 'templates/template.php', $this->data);
	}
}