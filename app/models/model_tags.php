<?php

class Model_Tags extends Model
{
	
	/**
	 * Возвращает список тегов для автодополнения
	 * @param $tag
	 * Начало тега
	 * @return array|bool
	 */
	public function get_autocomplete($tag)
	{
		if (!$tag) return false;
		$tag = trim($tag);
		
		
		// инициализирую необходимые перменные
		$db_table = 'tags';
		$sql      = "SELECT name FROM $db_table WHERE name LIKE ?s LIMIT 10";
		
		try {
			return $this->db->getAll($sql, "%$tag%");
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Добавляет теги в пост и БД
	 * @param $tags
	 * Список тегов через запятую
	 * @param $post_id
	 * Идентификатор поста
	 * @return bool
	 */
	public function add($tags, $post_id)
	{
		if (!$post_id || !$tags) return false;
		
		
		// таблицы, с которыми работаю
		$db_table_tags = 'tags';
		$db_table_tie  = 'tags_to_posts';
		
		$sql_tags = "CREATE TABLE IF NOT EXISTS $db_table_tags (
		    id INT(11) NOT NULL AUTO_INCREMENT,
		    name VARCHAR(32) NOT NULL DEFAULT '0',
		    PRIMARY KEY(id),
		    UNIQUE INDEX name (name)
		    )
		    ENGINE = InnoDB
		    DEFAULT CHARACTER SET = utf8
		    COLLATE = utf8_general_ci";
		
		$sql_tie = "CREATE TABLE IF NOT EXISTS $db_table_tie (
			id INT AUTO_INCREMENT,
			post_id VARCHAR(13) NOT NULL DEFAULT '0',
			tag_id INT(11) NOT NULL DEFAULT '0',
			INDEX post_id (post_id),
			INDEX tag_id (tag_id),
			PRIMARY KEY (id),
			CONSTRAINT tags_to_posts_ibfk_1 FOREIGN KEY (post_id) REFERENCES posts (id),
			CONSTRAINT tags_to_posts_ibfk_2 FOREIGN KEY (tag_id) REFERENCES tags (id)
			)
			COLLATE = 'utf8_general_ci'
			ENGINE = InnoDB
		";
		
		try // кидаю запрос на создание таблицы, если ее нет
		{
			$this->db->query($sql_tags);
			$this->db->query($sql_tie);
			
			// перебираю все теги
			$tags_array = explode(",", $tags);
			foreach ($tags_array as $tag) {
				$tag = trim($tag);
				if (!$tag) continue;
				
				// добавляю новый тег в таблицу
				$sql_tag = "INSERT INTO $db_table_tags SET name=?s ON DUPLICATE KEY UPDATE name=name";
				// добавляю запись в связку
				$sql_tie = "INSERT INTO $db_table_tie SET post_id=?s,tag_id=?i ON DUPLICATE KEY UPDATE post_id=post_id";

				$this->db->query($sql_tag, $tag);
				// получаю id тега
				$sql = "SELECT id FROM $db_table_tags WHERE name=?s LIMIT 1";
				$tag_id = $this->db->getOne($sql, $tag);
				// проверяю, есть ли такая связка в бд
				if ($this->check_tags_tie($post_id, $tag_id)) {
					//доабвляю связку
					$this->db->query($sql_tie, $post_id, $tag_id);
				}
			}
			return true;
		}
		catch (Exception $e) {
			$this->msg_error[] = 'Ошибка подключения к БД.';
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Обновляет теги поста в БД
	 * @param $tags
	 * Список тегов через запятую
	 * @param $post_id
	 * Идентификатор поста
	 * @return bool
	 */
	public function update($tags, $post_id)
	{
		if (!$post_id || !$tags) return false;
		
		
		// таблицы, с которыми работаю
		$db_table_tags = 'tags';
		$db_table_tie  = 'tags_to_posts';
		
		// формирую запрос на получения списка тегов поста
		$sql = "SELECT GROUP_CONCAT(name) AS tags_list
			FROM $db_table_tie
			JOIN $db_table_tags ON tags.id = tag_id

			WHERE post_id = ?s
			GROUP BY post_id
			LIMIT 1
		";
		
		try {
			$tags_list = $this->db->getOne($sql, $post_id);
			// разбиваю теги на массивы
			$post_tags_array = explode(",", $tags_list);
			$new_tags_array  = explode(",", $tags);
			
			// нахожу пересечение массивов
			$intersection = array_intersect($post_tags_array, $new_tags_array);
			// объединяю уникальные элементы
			$merge = array_unique(array_merge($post_tags_array, $new_tags_array));
			
			$for_remove = [];
			// перебираю по общему массиву
			foreach ($merge as $key => $value) {
				// убираю элемент, если его не меняли
				if (in_array($value, $intersection)) {
					unset($merge[$key]);
				}
				// сохраняю элемент, который хотят удалить
				if (!in_array($value, $new_tags_array)) {
					$for_remove[] = $value;
					unset($merge[$key]);
				}
			}
			
			// добавляю теги к посту
			$this->add(implode(",", $merge), $post_id);
			
			// если есть теги для удаления
			if (!empty($for_remove)) {
				// делаю выборку ИД тегов, которые удалили
				$sql_tags = '';
				foreach ($for_remove as $key => $value) {
					$sql_tags .= $this->db->parse("name=?s", $value);
					if ($key + 1 != count($for_remove)) $sql_tags .= " OR ";
				}
				// формирую запрос
				$sql = "SELECT GROUP_CONCAT(id) AS id_list FROM $db_table_tags WHERE ?p";

				// получаю идентификаторы тегов, которые удалили
				$tags_for_delete = $this->db->getOne($sql, $sql_tags);
				// удаляю теги из связки
				$this->remove_from_tie($post_id, $tags_for_delete);
			
				// удаляю неиспользуемые теги
				$this->remove_unused(implode(",", $for_remove));
			}
			
			return true;
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Удаляет теги из таблицы-связки
	 * @param $post_id
	 * Идентификатор поста
	 * @param string $tags
	 * [Optional] Список тегов через запятую
	 * @return bool
	 */
	public function remove_from_tie($post_id, $tags = '')
	{
		if (!$post_id) return false;
		$post_id = trim($post_id);
		
		
		// инициализирую необходимые перменные
		$db_table = 'tags_to_posts';
		$sql_tags = "";
		
		if (!empty($tags)) {
			$tags_array = explode(",", $tags);
			$sql_tags .= "AND (";
			
			foreach ($tags_array as $key => $tag_id) {
				$sql_tags .= $this->db->parse("tag_id=?i", (int)$tag_id);
				if ($key + 1 != count($tags_array)) $sql_tags .= " OR ";
			}
			
			$sql_tags .= ")";
		}
		
		$sql = "DELETE FROM $db_table WHERE post_id=?s ?p";
		try {
			$this->db->query($sql, $post_id, $sql_tags);
			
			// удаляю лишние теги
			$this->remove_unused($tags);
			
			return true;
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Проверяет наличие тега в связке
	 * @param $post_id
	 * Идентификатор поста
	 * @param $tag_id
	 * Идентификатор тега
	 * @return bool
	 */
	private function check_tags_tie($post_id, $tag_id)
	{
		if (!$post_id || !$tag_id) return false;
		
		
		// таблицы, с которыми работаю
		$db_table = 'tags_to_posts';
		
		$sql = "SELECT 1 FROM $db_table WHERE post_id=?s AND tag_id=?i LIMIT 1";
		try {
			if (!$this->db->getOne($sql, $post_id, $tag_id)) {
				return true;
			}
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Проверяет наличие неиспользуемых тегов
	 * @param $tags
	 * Список тегов через запятую
	 * @return bool
	 */
	public function remove_unused($tags)
	{
		if (!$tags) return false;
		
		
		// таблицы, с которыми работаю
		$db_table_tags = 'tags';
		$db_table_tie  = 'tags_to_posts';
		
		// формирую общий запрос
		$sql = "SELECT COUNT(*) AS count
			FROM $db_table_tags
			JOIN $db_table_tie AS tie ON tags.id = tie.tag_id
			WHERE tags.name = ?s
		";
		
		// разбиваю строку с тегами на массив
		$tags_array = explode(",", $tags);
		// перебираю все теги
		foreach ($tags_array as $value) {
			try {
				// проверяю, используется ли тег
				if (!$this->db->getOne($sql, $value)) {
					$sql_delete = "DELETE FROM $db_table_tags WHERE name=?s";

					// если нет - удаляю
					$this->db->query($sql_delete, $value);
				}
			}
			catch (Exception $e) {
				debug($e);
			}
		}
		
		return false;
	}
}