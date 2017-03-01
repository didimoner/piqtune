<?php

class Controller_Posts extends Controller
{
	public function action_index()
	{
		// создаем объект модели
		$model = new Model_Posts();
		// создаем объект отображения
		$view = new View();
		
		$uri_id = Router::get_segment(2) ? Router::get_segment(2) : false;
		// делаем запрос на пост
		if ($result = $model->get_by_id($uri_id)) {
			$this->data['content']    = $result;
			$this->data['page_title'] = $this->data['content']['title'];
		} else Router::redirect('404');
		
		$widget_data['id']      = $uri_id;
		$widget_data['emotion'] = $this->data['content']['emotion'];
		// подключаю виджет
		$this->data['widgets'][] = [
			'name' => 'similar',
			'dir'  => '',
			'data' => $widget_data
		];
		
		// проверяю, может ли пользователь редактировать
		$this->data['content']['edit']   = false;
		$this->data['content']['delete'] = false;
		
		// проверяю
		if ($_SESSION['user']['auth']) {
			if ($_SESSION['user']['is_admin']) { // если админ - можно все
				$this->data['content']['edit']   = true;
				$this->data['content']['delete'] = true;
			} else { // если юзер, смотрим на время создания
				if ($credate = $model->get_credate($uri_id)) {
					// смотрю в конфиге время на редактирование
					$allowed_time = Config::get('posts_allowed_time', 30 * 60);
					
					if (($credate + $allowed_time > time())) {
						$this->data['content']['edit'] = true;
					}
				}
			}
		}
		
		// подключаю необходимые скрипты
		$this->data['js'] = '<script src="/js/player.js"></script>';
		
		$view->draw('posts/view_normal.php', 'templates/template.php', $this->data);
	}
	
	public function action_embed()
	{
		// создаем объект модели
		$model = new Model_Posts();
		// создаем объект отображения
		$view = new View();
		
		// делаем запрос на пост
		if ($result = $model->get_by_id(Router::get_segment(3))) {
			$this->data['content']    = $result;
			$this->data['page_title'] = $this->data['content']['title'];
		} else Router::redirect('404');
		
		$this->data['css'] = '<link href="/css/embed-player.css" rel="stylesheet">';
		$this->data['js']  = '<script src="/js/player.js"></script>';
		$this->data['js'] .= '<script src="/js/embed-player.js"></script>';
		$view->draw('posts/view_embed.php', 'templates/template_blank.php', $this->data);
	}
	
	public function action_random()
	{
		// создаем объект модели
		$model = new Model_Posts();

		// делаем запрос на пост
		if ($result = $model->get_random(1)) {
			Router::redirect('/posts/'.$result[0]['id']);
		} else Router::redirect('/');
	}
	
	public function action_change_rating()
	{
		if (!isset($_POST)) return;
		// создаем объект модели
		$model = new Model_Posts();
		// подключаю модель пользователй
		include_once Config::get('app').'models/model_favorites.php';
		$model_favorites = new Model_Favorites();
		
		// меняю рейтинг
		$rating = $model->change_rating($_POST);
		// добавлюя/удаляю из избранного
		$post_id = isset($_POST['id']) ? $_POST['id'] : '';
		$mode    = isset($_POST['mode']) ? $_POST['mode'] : '';

		if ($mode == 'inc') {
			$model_favorites->add($_SESSION['user']['login'], $post_id);
		}
		else {
			$model_favorites->remove($_SESSION['user']['login'], $post_id);
		}

		$output['_token'] = $_SESSION['_token'];
		$output['rating'] = declination($rating, 'звезда', 'звезды', 'звезд');
		
		echo json_encode($output);
	}
	
	// создание поста
	public function action_create()
	{
		if (!$_SESSION['user']['auth']) Router::redirect('/users/auth');

		// создаем объект модели
		$model = new Model_Posts();
		// создаем объект отображения
		$view = new View();
		
		// если пришла форма - обрабатываем
		if (count($_POST) > 0) {
			// получаем данные с модели
			if ($item_id = $model->add($_POST, $_FILES)) {
				Router::redirect('/posts/' . $item_id);
			} else {
				$_SESSION['savepost']          = $_POST;
				$_SESSION['messages']['error'] = $model->get_messages('error');
				Router::redirect('/posts/create');
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
		
		$this->data['page_title'] = 'Создать  пост';
		// подключаю нужные js файлы
		$this->data['js'] = '<script src="/ext/jQuery-tagEditor-master/jquery.caret.min.js"></script>';
		$this->data['js'] .= '<script src="/ext/jQuery-tagEditor-master/jquery.tag-editor.js"></script>';
		$this->data['js'] .= '<script src="/js/creator.js"></script>';
		// подключаю css файлы
		$this->data['css'] = '<link href="/ext/jQuery-tagEditor-master/jquery.tag-editor.css" rel="stylesheet">';
		
		// передаем данные во вью, рисуем ее
		$view->draw('posts/view_create.php', 'templates/template.php', $this->data);
	}


	// редактирование поста
	public function action_edit()
	{
		// создаем объект модели
		$model = new Model_Posts();
		// создаем объект отображения
		$view = new View();
		// получаю id
		$uri_id = Router::get_segment(3);
		// время на редактирование
		$allowed_time = Config::get('posts_allowed_time', 30 * 60);
		
		if ($_SESSION['user']['auth']) {
			if ($credate = $model->get_credate($uri_id)) {
				if (($credate + $allowed_time < time()) && !$_SESSION['user']['is_admin']) {
					Router::redirect('/posts/' . $uri_id);
				}
			}
		} else {
			Router::redirect('/posts/' . $uri_id);
		}
		
		// ---------------------------------
		
		// если пришла форма - обрабатываем
		if (count($_POST) > 0) {
			// получаем данные с модели
			if ($model->edit($_POST, $_FILES, $uri_id)) {
				Router::redirect('/posts/' . $uri_id);
			} else {
				$_SESSION['savepost']          = $_POST;
				$_SESSION['messages']['error'] = $model->get_messages('error');
				Router::redirect('/posts/edit/' . $uri_id);
			}
		}
		
		// делаем запрос на пост
		if ($result = $model->get_by_id($uri_id)) {
			$this->data['content'] = $result;
		} else Router::redirect('/posts/' . $uri_id);
		
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
		
		$this->data['page_title'] = 'Редактирование поста';
		// подключаю нужные js файлы
		$this->data['js'] = '<script src="/ext/jQuery-tagEditor-master/jquery.caret.min.js"></script>';
		$this->data['js'] .= '<script src="/ext/jQuery-tagEditor-master/jquery.tag-editor.js"></script>';
		$this->data['js'] .= '<script src="/js/creator.js"></script>';
		// подключаю css файлы
		$this->data['css'] = '<link href="/ext/jQuery-tagEditor-master/jquery.tag-editor.css" rel="stylesheet">';
		
		$view->draw('posts/view_edit.php', 'templates/template.php', $this->data);
	}
	
	// Удаление поста
	public function action_delete()
	{
		if (!$_SESSION['user']['auth'] && !$_SESSION['user']['is_admin'])
			Router::redirect('/posts/' . Router::get_segment(3));
		
		// создаем объект модели
		$model = new Model_Posts();
		
		$model->delete(Router::get_segment(3));
		Router::redirect('/');
	}
	
	// возвращает теги для автодополнения
	public function action_ajax_tag_autocomplete()
	{
		// создаем объект модели
		include_once Config::get('app').'models/model_tags.php';
		$model_tags = new Model_Tags;
		$output     = [];
		
		if (count($_GET) > 0) {
			if ($data = $model_tags->get_autocomplete($_GET['tag'])) {
				foreach ($data as $row) {
					$output[] = $row['name'];
				}
			}
		}
		
		echo json_encode($output);
	}


	public function action_photo_editor()
	{
		if (!$_SESSION['user']['auth']) Router::redirect('/users/auth');

		// создаем объекты модели и вьюшки
		$view = new View();

		// получаем данные с модели
		$this->data['page_title'] = 'Редактор изображений';
		// подключаю нужные css файлы
		$this->data['css'] = '<link href="/css/photo_editor/font-awesome.min.css" rel="stylesheet">';
		$this->data['css'] .= '<link href="/css/photo_editor/cropper.min.css" rel="stylesheet">';
		$this->data['css'] .= '<link href="/css/photo_editor/main.css" rel="stylesheet">';
		// подключаю нужные js файлы
		$this->data['js'] = '<script src="/js/photo_editor/vue.min.js"></script>';
		$this->data['js'] .= '<script src="/js/photo_editor/cropper.min.js"></script>';
		$this->data['js'] .= '<script src="/js/photo_editor/main.js"></script>';

		// передаем данные во вью, рисуем ее
		$view->draw('posts/view_photo_editor.php', 'templates/template_blank.php', $this->data);
	}
	
	// возвращает теги для автодополнения
	public function action_ajax_load_posts()
	{
		// создаем объект модели
		$model           = new Model_Posts();
		$output['token'] = $_SESSION['_token'];
		
		if (count($_POST) > 0) {
			// выбираю, откуда брать посты
			switch ($_POST['type']) {
				case 'my_posts':
					$data = $model->get_by_author($_POST['additional_data']['login'], $_POST['offset'], $_POST['limit'] + 1);
					break;
				case 'favorites':
					$data = $model->get_favorites($_SESSION['user']['login'], $_POST['offset'], $_POST['limit'] + 1);
					break;
				case 'search':
					$data = $model->get_search_results($_POST['additional_data'], $_POST['offset'], $_POST['limit'] + 1);
					break;
				case 'best':
					$data = $model->get_best($_POST['additional_data']['period'], $_SESSION['user']['login'], $_POST['offset'], $_POST['limit'] + 1);
					break;
				case 'new':
					$data = $model->get_new($_SESSION['user']['login'], $_POST['offset'], $_POST['limit'] + 1);
					break;
				
				default:
					$data = [];
					break;
			}
			
			// если что-то пришло - отправляю клиенту
			if ($data) {
				// считаю на один пост больше, потом его убираю
				$output['count'] = count($data);
				unset($data[$_POST['limit']]);
				
				// подключаю функцию генерации поста
				include Config::get('app').'views/default/posts/view_item.php';
				$html_items = '';
				// генерирую посты
				foreach ($data as $key => $row) {
					$html_items .= make_item($row);
				}
				
				// записываю готовый контет и отдаю в фронтенд
				$output['content'] = $html_items;
			}
		}
		
		echo json_encode($output);
	}
	
	public function action_ajax_report_post()
	{
		if (!$_SESSION['user']['auth']) return;
		
		// создаем объект модели
		$model = new Model_Posts();
		
		$status = true;
		
		if (!$model->check_report($_POST['id'], $_SESSION['user']['login'])) {
			$model->report_post($_POST['id'], $_SESSION['user']['login']);
		}
		else {
			$status = false;
		}
		
		$output['_token']  = $_SESSION['_token'];
		$output['status'] = $status;
		
		echo json_encode($output);
	}
}
