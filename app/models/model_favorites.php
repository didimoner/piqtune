<?php

class Model_Favorites extends Model
{
	
	/**
	 * Добавляет пост в избранное
	 * @param $login
	 * Логин пользовтаеля
	 * @param $post_id
	 * Идентификатор поста
	 * @return bool
	 */
	public function add($login, $post_id)
	{
		if (!$login) return false;
		if (!$post_id) return false;

		// таблица-связка
		$db_table = 'posts_to_users';

		// проверяю, есть ли уже в избранном
		$sql = "SELECT 1 FROM $db_table WHERE user_login=?s AND post_id=?s LIMIT 1";

		try {
			if (!$this->db->getOne($sql, $login, $post_id)) {
				// если нет - добавляю
				$sql = "INSERT INTO $db_table SET user_login=?s, post_id=?s";
				
				$this->db->query($sql, $login, $post_id);
				return true;
			}
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}

	/**
	 * Удаляет пост из избранного
	 * @param $login
	 * Логин пользовтаеля
	 * @param $post_id
	 * Идентификатор поста
	 * @return bool
	 */
	public function remove($login, $post_id)
	{
		if (!$login) return false;
		if (!$post_id) return false;

		// таблица-связка
		$db_table = 'posts_to_users';

		// убираю из избранного
		$sql = "DELETE FROM $db_table WHERE user_login=?s AND post_id=?s";

		try {
			$this->db->query($sql, $login, $post_id);
			return true;
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}

	/**
	 * Удаляет пост из избранного у всех пользователей
	 * @param $post_id
	 * Идентификатор поста
	 * @return bool
	 */
	public function remove_all($post_id)
	{
		if (!$post_id) return false;

		// таблица-связка
		$db_table = 'posts_to_users';

		// убираю из избранного
		$sql = "DELETE FROM $db_table WHERE post_id=?s";

		try {
			$this->db->query($sql, $post_id);
			return true;
		}
		catch (Exception $e) {
			debug($e);
		}

		return false;
	}

	/**
	 * Проверка поста на наличие в избранном
	 * @param $login
	 * Логин пользовтаеля
	 * @param $post_id
	 * Идентификатор поста
	 * @return bool
	 */
	public function check_favorites($login, $post_id)
	{
		if (!$login || !$post_id) return false;
		
		
		// инициализирую необходимые перменные
		$db_table = 'posts_to_users';
		
		// проверяю, есть ли уже в избранном
		$sql = "SELECT 1 FROM $db_table WHERE user_login=?s AND post_id=?s LIMIT 1";
		
		try {
			if ($this->db->getOne($sql, $login, $post_id)) return true;
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
}