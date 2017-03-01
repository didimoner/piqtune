<?php

class Controller_Search extends Controller
{
	// перенаправляю на страницу поиска
	public function action_index()
	{
		Router::redirect('/search/results');
	}
	
	// страница поиск
	public function action_results()
	{
		include Config::get('app').'models/model_posts.php';
		// создаем объекты модели и вьюшки
		$model = new Model_Posts;
		$view  = new View();
		
		// количество постов на страницу
		$limit = 15;
		
		// получаю ошибки, если они есть
		if (isset($_SESSION['messages'])) {
			$this->data['messages'] = $_SESSION['messages'];
			unset($_SESSION['messages']);
		}
		
		// если запрос пришел из глобального поиска
		if (isset($_GET['v']) && $_GET['v'] == '1') {
			// если введено только слово - ищу в тегах
			if (substr_count($_GET['target'], ' ') == 0) {
				Router::redirect('/search/results?tags='.$_GET['target']);
			}
		}
		
		// запрашиваю поиск у модели
		if ($content = $model->get_search_results($_GET, 0, $limit + 1)) {
			$this->data['quantity'] = count($content);
			unset($content[$limit]);
			$this->data['content'] = $content;
		}
		// передаю параметры из адресной строки во вьюшку
		$this->data['target']       = isset($_GET['target']) ? $_GET['target'] : '';
		$this->data['initial_tags'] = isset($_GET['tags']) ? $_GET['tags'] : '';
		$this->data['emotion']      = isset($_GET['emotion']) ? $_GET['emotion'] : '';
		
		$this->data['settings']['offset'] = $limit;
		$this->data['settings']['limit']  = $limit;
		
		$this->data['page_title'] = 'Поиск';
		// подключаю необходимые скрипты
		$this->data['js'] = '<script src="/js/player.js"></script>';
		$this->data['js'] .= '<script src="/js/loading_posts.js"></script>';
		$this->data['js'] .= '<script src="/ext/jQuery-tagEditor-master/jquery.caret.min.js"></script>';
		$this->data['js'] .= '<script src="/ext/jQuery-tagEditor-master/jquery.tag-editor.js"></script>';
		$this->data['js'] .= '<script src="/js/search.js"></script>';
		// подключаю css файлы
		$this->data['css'] = '<link href="/ext/jQuery-tagEditor-master/jquery.tag-editor.css" rel="stylesheet">';
		
		$view->draw('search/view_results.php', 'templates/template.php', $this->data);
	}
}
