<?php

class Controller_Main extends Controller
{
	public function action_index()
	{
		Router::redirect('/main/best');
	}
	
	// лучшие посты
	public function action_best()
	{
		include_once Config::get('app').'models/model_posts.php';
		// создаем объекты модели и вьюшки
		$model = new Model_Posts;
		$view  = new View();
		
		// количество постов на страницу
		$limit = 15;
		// время периода, за который берутся посты
		$period = isset($_GET['period']) ? $_GET['period'] : 'week';
		
		// получаю контент
		if ($content = $model->get_best($period, $_SESSION['user']['login'], 0, $limit + 1)) {
			$this->data['quantity'] = count($content);
			unset($content[$limit]);
			$this->data['content'] = $content;
		}
		
		$this->data['settings']['offset'] = $limit;
		$this->data['settings']['limit']  = $limit;
		$this->data['settings']['period'] = $period;
		
		// получаем данные с модели
		$this->data['page_title'] = 'Лучшие посты';
		// подключаю необходимые скрипты
		$this->data['js'] = '<script src="/js/player.js"></script>';
		$this->data['js'] .= '<script src="/js/loading_posts.js"></script>';
		// передаем данные во вью, рисуем ее
		$view->draw('main/view_best.php', 'templates/template.php', $this->data);
	}
	
	// новые посты
	public function action_new()
	{
		include_once Config::get('app').'models/model_posts.php';
		// создаем объекты модели и вьюшки
		$model = new Model_Posts;
		$view  = new View();
		
		// количество постов на страницу
		$limit = 15;
		
		// получаю контент
		if ($content = $model->get_new($_SESSION['user']['login'], 0, $limit + 1)) {
			$this->data['quantity'] = count($content);
			unset($content[$limit]);
			$this->data['content'] = $content;
		}
		
		$this->data['settings']['offset'] = $limit;
		$this->data['settings']['limit']  = $limit;
		
		// получаем данные с модели
		$this->data['page_title'] = 'Новые посты';
		// подключаю необходимые скрипты
		$this->data['js'] = '<script src="/js/player.js"></script>';
		$this->data['js'] .= '<script src="/js/loading_posts.js"></script>';
		// передаем данные во вью, рисуем ее
		$view->draw('main/view_new.php', 'templates/template.php', $this->data);
	}
	
	public function action_ajax_send_report()
	{
		if (count($_POST) == 0) return;
		
		$post_msg = isset($_POST['message']) ? $_POST['message'] : '';

		if (mb_strlen($post_msg, "UTF-8") < 5 || mb_strlen($post_msg, "UTF-8") > 512) {
			return;
		}
		else {
			include_once Config::get('libs').'send_mail.php';
			
			// отправляю письмо
			$title   = "Отчет об ошибке на сайте PiqTune.com";
			$to      = "tracker@piqtune.com";
			$subject = "Bug Report";
			$message = '
			<p>' . $_POST['message'] . '</p>
		';
			
			send_email($title, $subject, $to, $message);
		}
		
		echo $_SESSION['_token'];
	}
}