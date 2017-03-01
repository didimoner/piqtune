<?php

class Model_Posts extends Model
{
	/**
	 * Возвращает список постов
	 * @param $get
	 *  массив данных для поиска
	 * @param int $offset
	 *  сдвиг в БД
	 * @param int $per_page
	 *  количество постов на страницу
	 * @return array|bool
	 */
	public function get_search_results($get, $offset = 0, $per_page = 24)
	{
		$target  = isset($get['target']) ? $get['target'] : '';
		$tags    = isset($get['tags']) ? $get['tags'] : '';
		$emotion = isset($get['emotion']) ? $get['emotion'] : '';

		if (!$target && !$tags && !$emotion) return false;
		
		// инициализирую необходимые перменные
		$db_table = 'posts';

		// разбиваю строку с тегами на массив
		$tags_array = explode(",", $tags);
		$sql_tags   = '';

		// если массив тего вне пуст, ищем по тегам
		if (!empty($tags_array)) {
			$sql_tags .= 'HAVING ';
			// составляю правило для поиска по тегам
			foreach ($tags_array as $key => $value) {
				$sql_tags .= $this->db->parse("tags LIKE ?s", "%$value%");
				if (count($tags_array) != $key + 1) $sql_tags .= " AND ";
			}
		}

		// формирую сам запрос
		$sql = "SELECT
				p.id, p.title, p.author, p.emotion, p.image, p.audio, p.date, p.rating,
				GROUP_CONCAT(tgs.name) AS tags, NOT IFNULL(fav_tie.user_login, 1) AS is_favorite
				FROM $db_table AS p
				LEFT JOIN tags_to_posts AS tie ON tie.post_id = p.id
				LEFT JOIN tags AS tgs ON tgs.id = tie.tag_id
				LEFT JOIN posts_to_users AS fav_tie ON fav_tie.post_id = p.id AND fav_tie.user_login=?s
				WHERE p.title LIKE ?s AND p.emotion LIKE ?s
				GROUP BY p.id ?p
				LIMIT ?i,?i
		";

		// делаю запрос и возвращаю результат
		try {
			return $this->db->getAll($sql, $_SESSION['user']['login'], "%$target%", "%$emotion%", $sql_tags, $offset, $per_page);
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}
	
	/**
	 * Возвращает информацию о посте
	 * @param $id
	 * Идентификатор поста
	 * @return array|bool
	 */
	public function get_by_id($id)
	{
		if (!$id) return false;
		$id = trim($id);

		// инициализирую необходимые перменные
		$db_table = 'posts';

		$sql = "SELECT
				p.id, p.title, p.author, p.emotion, p.image, p.audio, p.date, p.rating,
				GROUP_CONCAT(tgs.name) AS tags, NOT IFNULL(fav_tie.user_login, 1) AS is_favorite
				FROM $db_table AS p
				LEFT JOIN tags_to_posts AS tie ON tie.post_id = p.id
				LEFT JOIN tags AS tgs ON tgs.id = tie.tag_id
				LEFT JOIN posts_to_users AS fav_tie ON fav_tie.post_id = p.id AND fav_tie.user_login=?s
				WHERE p.id=?s
				GROUP BY p.id
				LIMIT 1";

		try {
			return $this->db->getRow($sql, $_SESSION['user']['login'], $id);
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}
	
	/**
	 * Возвращает список постов по автору
	 * @param $author
	 * Логин автора
	 * @param int $offset
	 * [Optional] Сдвиг в БД
	 * @param int $limit
	 * Количество постов
	 * @return array|bool
	 */
	public function get_by_author($author, $offset = 0, $limit = 10)
	{
		if (!$author) return false;

		// инициализирую необходимые перменные
		$db_table = 'posts';

		$sql = "SELECT p.id, p.title, p.author, p.emotion, p.image, p.audio, p.date, p.rating,
				NOT IFNULL(fav_tie.user_login, 1) AS is_favorite
				FROM $db_table AS p
				LEFT JOIN posts_to_users AS fav_tie ON fav_tie.post_id = p.id AND fav_tie.user_login=?s
				WHERE author=?s
				ORDER BY p.date DESC
				LIMIT ?i,?i
		";

		try {
			return $this->db->getAll($sql, $author, $author, $offset, $limit);
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}
	
	/**
	 * Возвращает список избранных постов по автору
	 * @param $login
	 * Логин автора
	 * @param int $offset
	 * [Optional] Сдвиг в БД
	 * @param int $limit
	 * Количество постов
	 * @return array|bool
	 */
	public function get_favorites($login, $offset = 0, $limit = 10)
	{
		if (!$login) return false;
		$login = trim($login);

		$sql = "SELECT
			p.id, p.title, p.author, p.emotion, p.image, p.audio, p.date, p.rating, 1 AS is_favorite
			FROM posts AS p
			LEFT JOIN posts_to_users AS tie ON tie.post_id = p.id
			LEFT JOIN users AS u ON u.login = tie.user_login
			WHERE tie.user_login=?s
			GROUP BY p.id
			LIMIT ?i,?i
		";

		try {
			return $this->db->getAll($sql, $login, $offset, $limit);
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}
	
	/**
	 * Возвращает лучшие посты за время
	 * @param $period
	 * Период времени
	 * @param $login
	 * Логин автора
	 * @param int $offset
	 * [Optional] Сдвиг в БД
	 * @param int $limit
	 * Количество постов
	 * @return array|bool
	 */
	public function get_best($period, $login, $offset = 0, $limit = 10)
	{
		// инициализирую необходимые перменные
		$db_table = 'posts';

		if (empty($period)) $period = 'week';

		// определяю период времени
		$period_time = 0;

		if ($period == 'week') {
			$period_time = strtotime("-1 week");
		} elseif ($period == 'month') {
			$period_time = strtotime("-1 month");
		} elseif ($period == 'year') {
			$period_time = strtotime("-1 year");
		}

		// формирую запрос
		$sql = "SELECT
			p.id, p.title, p.author, p.emotion, p.image, p.audio, p.date, p.rating,
			NOT IFNULL(tie.user_login, 1) AS is_favorite
			FROM $db_table  AS p
			LEFT JOIN posts_to_users AS tie ON tie.post_id = p.id AND tie.user_login=?s
			LEFT JOIN users AS u ON u.login = tie.user_login
			WHERE p.date >= ?i
			GROUP BY p.id
			ORDER BY p.rating DESC
			LIMIT ?i,?i
		";

		try {
			return $this->db->getAll($sql, $login, $period_time, $offset, $limit);
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}
	
	/**
	 * Возвращает новые посты
	 * @param $login
	 * Логин автора
	 * @param int $offset
	 * [Optional] Сдвиг в БД
	 * @param int $limit
	 * Количество постов
	 * @return array|bool
	 */
	public function get_new($login, $offset = 0, $limit = 10)
	{
		// инициализирую необходимые перменные
		$db_table = 'posts';
		// время свежего
		$period_time = Config::get('new_posts_period', strtotime("-1 month"));

		$sql = "SELECT
			p.id, p.title, p.author, p.emotion, p.image, p.audio, p.date, p.rating,
			NOT IFNULL(tie.user_login, 1) AS is_favorite
			FROM $db_table AS p
			LEFT JOIN posts_to_users AS tie ON tie.post_id = p.id AND tie.user_login=?s
			LEFT JOIN users AS u ON u.login = tie.user_login
			WHERE p.date >= ?i
			GROUP BY p.id
			ORDER BY p.date DESC
			LIMIT ?i,?i
		";

		try {
			return $this->db->getAll($sql, $login, $period_time, $offset, $limit);
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}
	
	/**
	 * Возвращает посты по эмоции
	 * @param $emotion
	 * Название эмоции
	 * @param $current_item
	 * Пост, который следует исключить
	 * @param int $limit
	 * Количество постов
	 * @return array|bool
	 */
	public function get_by_emotion($emotion, $current_item, $limit = 10)
	{
		if (!$emotion && !$current_item) return false;
		$emotion = trim($emotion);
		
		// инициализирую необходимые перменные
		$db_table = 'posts';
		$sql      = "SELECT id,title,rating FROM $db_table WHERE emotion=?s AND NOT id=?s ORDER BY rating DESC LIMIT ?i";

		try {
			return $this->db->getAll($sql, $emotion, $current_item, $limit);
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}
	
	/**
	 * Возвращает недостающие посты
	 * @param $emotion
	 * Название эмоции, которую следует исключить
	 * @param $current_item
	 * Пост, который следует исключить
	 * @param int $limit
	 * Количество постов
	 * @return array|bool
	 */
	public function get_missing_posts($emotion, $current_item, $limit = 10)
	{
		if (!$emotion && !$current_item) return false;
		$emotion = trim($emotion);
		
		// инициализирую необходимые перменные
		$db_table = 'posts';
		$sql      = "SELECT id,title,rating FROM $db_table WHERE NOT emotion=?s AND NOT id=?s ORDER BY RAND() DESC LIMIT ?i";

		try {
			return $this->db->getAll($sql, $emotion, $current_item, $limit);
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}

	/**
	 * Возвращает идентимфикаторы случайных постов
	 * @param int $limit
	 * Количество постов
	 * @return array|bool
	 */
	public function get_random($limit = 10)
	{
		// инициализирую необходимые перменные
		$db_table = 'posts';

		$sql  = "SELECT id FROM $db_table ORDER BY RAND() DESC LIMIT ?i";

		try {
			return $this->db->getAll($sql, $limit);
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}
	
	/**
	 * Меняет рейтинг
	 * @param $post
	 * Данные из массива POST
	 * @return bool|string
	 */
	public function change_rating($post)
	{
		if (!$post) return false;

		$id   = isset($post['id']) ? $post['id'] : '';
		$mode = isset($post['mode']) ? $post['mode'] : '';
		
		// инициализирую необходимые перменные
		$db_table  = 'posts';
		$operation = $mode == 'inc' ? 'rating=rating+1' : 'rating=rating-1';

		$sql = "UPDATE $db_table SET $operation WHERE id=?s";

		try {
			$this->db->query($sql, $id);
			$sql = "SELECT rating FROM $db_table WHERE id=?s";
			
			return $this->db->getOne($sql, $id);
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}

	/**
	 * Возвращает дату создания поста
	 * @param $id
	 * Идентификатор поста
	 * @return bool|string
	 */
	public function get_credate($id)
	{
		if (!$id) return false;
		$id = trim($id);
		
		// инициализирую необходимые перменные
		$db_table = 'posts';
		$sql      = "SELECT date FROM $db_table WHERE id=?s";

		try {
			return $this->db->getOne($sql, $id);
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}
	
	/**
	 * Добавляет пост в БД
	 * @param $post
	 * Данные из массива POST
	 * @param $files
	 * Данные из массива FILES
	 * @return bool|string
	 */
	public function add($post, $files)
	{
		// проверка данных
		$post  = $this->verify_data($post);
		$files = $this->verify_files($files);
		if (!$post || !$files) return false;

		// инициализирую нужные перменные
		$db_table   = 'posts';
		$db_tie_ptu = 'posts_to_users';
		$gen_id     = uniqid();
		$unixtime   = time();
		$images_dir = Config::get('www') . 'images/pics/';
		$audios_dir = Config::get('www') . 'audio/';
		// создаю папки, если их еще нет
		if (!is_dir($images_dir)) create_dir($images_dir);
		if (!is_dir($audios_dir)) create_dir($audios_dir);
		
		// создаю таблицу, если ее еще нет
		$sql = "CREATE TABLE IF NOT EXISTS $db_table (
		    id VARCHAR(13),
		    author VARCHAR(16) NOT NULL,
		    title VARCHAR(64),
		    emotion VARCHAR(16),
		    image VARCHAR(5) DEFAULT 'jpg',
		    audio VARCHAR(5) DEFAULT 'mp3',
		    date INT,
		    rating INT DEFAULT 0,
		    active BOOLEAN DEFAULT 1,
		    PRIMARY KEY(id),
		    CONSTRAINT posts_ibfk_1 FOREIGN KEY (author) REFERENCES users (login)
		    )
		    ENGINE = InnoDB
		    DEFAULT CHARACTER SET = utf8
		    COLLATE = utf8_general_ci";

		// формирую запрос для создная связки
		$sql_tie = "CREATE TABLE IF NOT EXISTS $db_tie_ptu (
			id INT AUTO_INCREMENT,
			user_login VARCHAR(16) NOT NULL,
			post_id VARCHAR(13) NOT NULL DEFAULT '0',
			INDEX post_id (post_id),
			INDEX user_login (user_login),
			PRIMARY KEY (id),
			CONSTRAINT posts_to_users_ibfk_1 FOREIGN KEY (post_id) REFERENCES posts (id),
			CONSTRAINT posts_to_users_ibfk_2 FOREIGN KEY (user_login) REFERENCES users (login)
			)
			COLLATE = 'utf8_general_ci'
			ENGINE = InnoDB
		";

		try // кидаю запрос на создание таблицы, если ее нет
		{
			$this->db->query($sql);
			$this->db->query($sql_tie);

			// добавляю данные в таблицу
			$data = [
				'id'      => $gen_id,
				'author'  => $post['author'],
				'title'   => $post['title'],
				'emotion' => $post['emotion'],
				'image'   => $files['image']['file_format'],
				'date'    => $unixtime
			];

			$sql = "INSERT INTO $db_table SET ?u";

			$this->db->query($sql, $data);
			
			$image_format = $files['image']['file_format'];
			// работаю с картинкой
			move_uploaded_file($files['image']['tmp_name'], $images_dir . $gen_id . '.' .$image_format);
			
			// работаю с звуком
			$audio_format = $files['audio']['file_format'];
			if ($audio_format == 'mp3') {
				move_uploaded_file($files['audio']['tmp_name'], $audios_dir . $gen_id . '.' .$audio_format);
			}
			else {
				$input  = $files['audio']['tmp_name'];
				$output = $gen_id . '.mp3';
				
				$this->convert_audio($input, $output, '96k');
			}

			// работаю с тегами
			include_once Config::get('app').'models/model_tags.php';
			// создаем объекты модели и вьюшки
			$model_tags = new Model_Tags;

			$model_tags->add($post['tags'], $gen_id);

			return $gen_id;
		}
		catch (Exception $e) {
			$this->msg_error[] = 'Ошибка подключения к БД.';
			debug($e);
		}

		return false;
	}
	
	/**
	 * Редактирует пост
	 * @param $post
	 * Данные из массива POST
	 * @param $files
	 * Данные из массива FILES
	 * @param $id
	 * Идентификатор поста
	 * @return bool
	 */
	public function edit($post, $files, $id)
	{
		if (!$id) return false;
		$id = trim($id);

		// проверяю, нужно ли менять файлы
		$is_image = $files['image']['size'] != 0;
		$is_audio = $files['audio']['size'] != 0;

		// проверка данных
		$post  = $this->verify_data($post);
		$files = $this->verify_files($files, $is_image, $is_audio);
		if (!$post || !$files) return false;

		// инициализирую нужные перменные
		$db_table   = 'posts';
		$images_dir = Config::get('www').'images/pics/';
		$audios_dir = Config::get('www').'audio/';

		// создаю папки, если их еще нет
		if (!is_dir($images_dir)) create_dir($images_dir);
		if (!is_dir($audios_dir)) create_dir($audios_dir);

		$data = [
			'title'   => $post['title'],
			'emotion' => $post['emotion']
		];

		// работаю с картинкой
		if ($is_image) {
			$data['image'] = $files['image']['file_format'];
		}

		$sql = "UPDATE $db_table SET ?u WHERE id=?s";

		try {
			$this->db->query($sql, $data, $id);
			
			// работаю с картинкой
			if ($is_image) {
				$image_format = $files['image']['file_format'];
				move_uploaded_file($files['image']['tmp_name'], $images_dir . $id . '.' . $image_format);
			}
			// работаю с звуком
			if ($is_audio) {
				$audio_format = $files['audio']['file_format'];
				
				if ($audio_format == 'mp3') {
					move_uploaded_file($files['audio']['tmp_name'], $audios_dir . $id . '.' .$audio_format);
				}
				else {
					$input  = $files['audio']['tmp_name'];
					$output = $id . '.mp3';
					
					$this->convert_audio($input, $output, '96k');
				}
			}

			// обновляю теги
			include_once Config::get('app').'models/model_tags.php';
			$model_tags = new Model_Tags;
			$model_tags->update($post['tags'], $id);

			return true;
		}
		catch (Exception $e) {
			$this->msg_error[] = 'Ошибка при добавлении в БД.';
			debug($e);
		}

		return false;
	}
	
	/**
	 * Удаляет пост по ИД
	 * @param $id
	 * Идентификатор поста
	 * @return bool
	 */
	public function delete($id)
	{
		if (!$id) return false;
		$id = trim($id);
		
		// инициализирую необходимые перменные
		$db_table = 'posts';

		$sql = "SELECT
			pst.image, pst.audio, GROUP_CONCAT(tgs.name) AS tags
			FROM $db_table AS pst
			LEFT JOIN tags_to_posts AS tie ON tie.post_id = pst.id
			LEFT JOIN tags AS tgs ON tgs.id = tie.tag_id
			WHERE pst.id=?s
			GROUP BY pst.id
		";

		try {
			$row = $this->db->getRow($sql, $id);
			// формирую запрос на удаление
			$sql = "DELETE FROM $db_table WHERE id=?s";

			include_once Config::get('app').'models/model_tags.php';
			// создаем объекты модели и вьюшки
			$model_tags = new Model_Tags;
			// удаляю теги
			$model_tags->remove_from_tie($id);
			
			include_once Config::get('app').'models/model_favorites.php';
			// создаем объекты модели
			$model_favorites = new Model_Favorites();
			// удаляю связи в избранном
			$model_favorites->remove_all($id);
			
			// удаляю репорты
			$this->remove_repors($id);

			// удаляю пост
			$this->db->query($sql, $id);

			$images_dir = Config::get('www').'images/pics/';
			$audios_dir = Config::get('www').'audio/';

			// удаляю файлы
			if (!unlink($images_dir . $id . '.' . $row['image'])) {
				$this->msg_error[] = 'Ошибка при удалении изображения.';
			}
			if (unlink($audios_dir . $id . '.' . $row['audio'])) {
				$this->msg_error[] = 'Ошибка при удалении звука.';
			}

			// удаляю лишние теги
			$model_tags->remove_unused($row['tags']);

			return true;
		}
		catch (Exception $e) {
			$this->msg_error[] = 'Ошибка доступа БД.';
			debug($e);
		}

		return false;
	}
	
	/**
	 * проверяет тектосвые поля
	 * @param $post
	 * Данные из массива POST
	 * @return bool
	 */
	private function verify_data($post)
	{
		$title    = isset($post['title']) ? trim($post['title']) : '';
		$tag_list = isset($post['tags']) ? trim($post['tags']) : '';
		$emotion  = isset($post['emotion']) ? trim($post['emotion']) : '';

		$validation = true;

		if (mb_strlen($title, 'utf-8') < 5 || mb_strlen($title, 'utf-8') > 64) {
			$this->msg_error[] = 'Заголовок должен иметь длину от 5 до 64 символов.';
			$validation        = false;
		}

		// проверяю эмоции
		$emotions_array = ['happy', 'sad', 'angry', 'lol', 'wtf', 'wow', 'annoyed', 'cry', 'scared'];
		if (!in_array($emotion, $emotions_array)) {
			$this->msg_error[] = 'Недопустимое значние эмоции.';
			$validation        = false;
		}

		// проверяю теги на количество
		$tags = explode(",", $tag_list);
		if (count($tags) < 2) {
			$this->msg_error[] = 'Введите минимум 2 тега.';
			return false;
		}
		// и на длину
		foreach ($tags as $tag) {
			$tag = trim($tag);
			if (mb_strlen($tag, 'utf-8') < 2 || mb_strlen($tag, 'utf-8') > 24) {
				$this->msg_error[] = 'Теги должены иметь длину от 2 до 24 символов.';
				$validation        = false;
				break;
			}
		}

		// ----------------------------

		if (!$validation) return false;
		return $post;
	}
	
	/**
	 * Проверяет файлы
	 * @param $files
	 * Данные из массива FILES
	 * @param bool $image
	 * Проверять картинку?
	 * @param bool $audio
	 * Проверять звук?
	 * @return bool
	 */
	private function verify_files($files, $image = true, $audio = true)
	{
		$image_file = isset($files['image']) ? $files['image'] : '';
		$audio_file = isset($files['audio']) ? $files['audio'] : '';

		$validation = true;

		// если нужно проверять картинку
		if ($image) {
			// проверяю на наличие файла
			if (!$image_file) {
				$this->msg_error[] = 'Файл изображения не выбран';
				$validation        = false;
			}
			// проверяю на ошибки при загрузке
			if ($image_file['error']) {
				$this->msg_error[] = 'Ошибка при загрузке файла изображения.';
				$validation        = false;
			}
			// проверяю размер
			if ($image_file['size'] > 3072 * 1024) {
				$this->msg_error[] = 'Максимальный размер изображения - 3мб.';
				$validation        = false;
			}

		}

		// если нужно проверять звук
		if ($audio) {
			// проверяю на наличие файла
			if (!$audio_file) {
				$this->msg_error[] = 'Звуковой файл не выбран';
				$validation        = false;
			}
			// проверяю на ошибки при загрузке
			if ($audio_file['error']) {
				$this->msg_error[] = 'Ошибка при загрузке звукового файла.';
				$validation        = false;
			}
			// проверяю размер
			if ($audio_file['size'] > 5120 * 1024) {
				$this->msg_error[] = 'Максимальный размер звукового файла - 5мб.';
//				$validation        = false;
			}
		}

		if (!$validation) return false;

		// -------------------
		if ($image) {
			// проверяю картинку
			list($width, $height) = getimagesize($image_file['tmp_name']);
			// разрешенные форматы
			$allowed_formats = ['jpg', 'jpeg', 'png'];
			
			if ($width < 240 || $height < 240) {
				$this->msg_error[] = 'Минимальный разрешение изображения - 240х240 пикселей.';
				return false;
			} else {
				// меняю размер, если слишком большая
				if ($width > $height) {
					if ($width > 640) {
						resize($image_file['tmp_name'], 640);
					}
					else {
						if ($height > 640) {
							resize($image_file['tmp_name'], false, 640);
						}
					}
				}
			}
			
			// забираю расширение
			preg_match("/^.*\.(.+)$/", strtolower($image_file['name']), $result);
			$file_format = $result[1];
			
			if (!in_array($file_format, $allowed_formats)) {
				$this->msg_error[] = 'Неподдерживаемый формат изображения.';
				return false;
			}
			
			// записываю в массив с файлами формат файла
			$files['image']['file_format'] = $file_format;
		}

		if ($audio) {
			// проверяю звук
			$min_duration     = 2;
			$allowed_duration = 15;
			// разрешенные форматы
			$allowed_formats = ['mp3', 'aac', 'ogg', 'm4a', '3gp', 'mp4', 'wav', 'amr'];

			// include getID3() library (can be in a different directory if full path is specified)
			require_once(Config::get('libs').'getid3/getid3.php');
			// Initialize getID3 engine
			$getID3 = new getID3;
			$ThisFileInfo  = $getID3->analyze($audio_file['tmp_name']);
			// проверяю формат файла
			$file_format   = $ThisFileInfo['fileformat'];
			
			if (!in_array($file_format, $allowed_formats)) {
				$this->msg_error[] = 'Неподдерживаемый формат аудио-файла.';
				return false;
			}
			
			// записываю в массив с файлами формат файла
			$files['audio']['file_format'] = $file_format;
			
			// проверяю длину файла
			$file_duration = $ThisFileInfo['playtime_seconds'];
			
			if ($file_duration > $allowed_duration) {
				$this->msg_error[] = 'Максимальная длина аудиофайла - 15 секунд.';
				return false;
			}
			else if ($file_duration < $min_duration) {
				$this->msg_error[] = 'Минимальная длина аудиофайла - 2 секунды.';
				return false;
			}
		}

		// -------------------------------

		if (!$validation) return false;
		return $files;
	}
	
	/**
	 * Конвертирует аудио файлы посредством avconv
	 * @param $input
	 * Входной файл
	 * @param $output
	 * Выходной файл (filename.ext)
	 * @param $bitrate
	 * [optional] Значение битрейта (в байтах)
	 * @param $quality
	 * [optional] Значение качества звука (в процентах)
	 */
	private function convert_audio($input, $output, $bitrate = false, $quality = false)
	{
		$str_bitrate = '';
		$str_quality = '';
		
		$dir = dirname(dirname(__DIR__)).'/piqtune.com/audio/';
		$output_filename = $dir.$output;
		
		if ($bitrate) {
			$str_bitrate = '-b '.$bitrate;
		}
		if ($quality) {
			$str_quality = '-q '.$quality;
		}

		exec('avconv -i '.$input.' '.$str_bitrate.' '.$str_quality.' '.$output_filename.' &');
	}
	
	/**
	 * Отправляет в БД жалобу на пост
	 * @param $id
	 * Идентификатор поста
	 * @param $login
	 * Логин пользователя
	 * @return bool
	 */
	public function report_post($id, $login)
	{
		if (!$id || !$login) return false;
		
		$db_table = 'reports';

		// формирую запрос
		$sql = "CREATE TABLE IF NOT EXISTS $db_table (
			id INT NOT NULL AUTO_INCREMENT,
			post_id VARCHAR(13) NOT NULL,
			sender_login VARCHAR(16) NOT NULL,
			datetime INT,
			PRIMARY KEY(id),
			CONSTRAINT reports_ibfk_1 FOREIGN KEY (post_id) REFERENCES posts (id),
			CONSTRAINT reports_ibfk_2 FOREIGN KEY (sender_login) REFERENCES users (login)
			)
			ENGINE = InnoDB
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_general_ci
		";
		
		try {
			$this->db->query($sql);
			
			$unixtime = time();
			
			$data = [
				'post_id'      => $id,
				'sender_login' => $login,
				'datetime'     => $unixtime
			];
			
			$sql = "INSERT INTO $db_table SET ?u";
			return $this->db->query($sql, $data);
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}
	
	/**
	 * Отправляет жалобу на пост
	 * @param $id
	 * Идентификатор поста
	 * @param $login
	 * Логин пользователя
	 * @return bool|string
	 */
	public function check_report($id, $login)
	{
		if (!$id || !$login) return false;
		
		$db_table = 'reports';
		
		$sql = "SELECT 1 FROM $db_table WHERE post_id=?s AND sender_login=?s";
		
		try {
			return $this->db->getOne($sql, $id, $login);
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Удаляет из репортов пост
	 * @param $id
	 * Идентификатор поста
	 * @return bool
	 */
	private function remove_repors($id)
	{
		if (!$id) return false;
		
		$db_table = 'reports';
		
		$sql = "DELETE FROM $db_table WHERE post_id=?s";
		
		try {
			return $this->db->query($sql, $id);
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
}