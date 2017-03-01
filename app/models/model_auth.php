<?php

class Model_Auth extends Model
{
	/**
	 * Возвращает инфо о пользователе
	 * @param $login
	 * Логин пользователя
	 * @return array|bool
	 */
	public function get_user_by_id($login)
	{
		// проверка данных
		if (!$login) return false;
		
		// инициализирую нужные перменные
		$db_table = 'users';
		
		// готовлю запрос
		$sql = "SELECT login,is_admin FROM $db_table WHERE login=?s LIMIT 1";
		try {
			return $this->db->getRow($sql, $login);
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Вносит данные о новом пользователе в БД
	 * @param $post
	 * Данные с массива POST
	 * @return bool
	 */
	public function register($post)
	{
		// инициализирую нужные перменные
		$unixtime = time();
		// имя таблица
		$db_table     = 'users';
		$db_table_tie = 'tokens_to_users';
		
		// формирую запрос
		$sql = "CREATE TABLE IF NOT EXISTS $db_table (
			login VARCHAR(16) NOT NULL,
			password VARCHAR(64) NOT NULL,
			email VARCHAR(320) NOT NULL,
			remember_identifier VARCHAR(128) NULL DEFAULT NULL,
			recover_hash VARCHAR(32) NULL DEFAULT NULL,
			reg_date INT NOT NULL,
			last_date INT DEFAULT 0,
			subs TEXT DEFAULT NULL,
			is_admin BOOLEAN DEFAULT 0,
			banned BOOLEAN DEFAULT 0,
			verified BOOLEAN DEFAULT 0,
			code VARCHAR(32),
			PRIMARY KEY(login),
			INDEX remember_identifier (remember_identifier))
			ENGINE = InnoDB
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_general_ci
		";
		
		$sql_ttu = "CREATE TABLE IF NOT EXISTS $db_table_tie (
			id INT NOT NULL AUTO_INCREMENT,
			identifier VARCHAR(128) NOT NULL,
			token VARCHAR(32) NOT NULL,
			PRIMARY KEY(id),
			CONSTRAINT tokens_to_users_ibfk_1 FOREIGN KEY (identifier) REFERENCES users (remember_identifier)
			)
			ENGINE = InnoDB
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_general_ci
		";
		
		try // кидаю запрос на создание таблица, если ее нет
		{
			$this->db->query($sql);
			$this->db->query($sql_ttu);
			
			// проверка поста
			if (!$post = $this->verify_reg_data($post)) return false;
			
			// шифрую пароль
			$password_hash = password_hash($post['password'], PASSWORD_DEFAULT);
			// генерирую код для подтверждения реги
			$generated_code = random_string(128);
			$hashed_code    = md5($generated_code);
			$post_login     = $post['login'];
			$post_email     = $post['email'];
			// готовлю запрос на добовление в таблицу
			$data = [
				'login'    => $post_login,
				'password' => $password_hash,
				'email'    => $post_email,
				'code'     => $hashed_code
			];
			
			$sql = "INSERT INTO $db_table SET ?u, reg_date=$unixtime";
			
			// вставляю данные о блоке в базу
			$this->db->query($sql, $data);
			$host_adr = 'http://' . $_SERVER['HTTP_HOST'];
			
			// подключаю класс email рассылок
			include_once Config::get('libs') . 'send_mail.php';
			
			// отправляю письмо
			$title   = "Подтверждение регистрации на сайте PiqTune.com";
			$subject = "Подтверждение регистрации на сайте PiqTune.com";
			$message = '
	            <p>Приветствуем, ' . $post_login . '!</p>
	            <p>Благодарим вас за регистрацию.</p>
	            <p>Для активации учетной записи вам необходимо перейти в течении 3 дней по <a style="text-decoration: none;" href="' . $host_adr . '/auth/confirm?login=' . $post_login . '&code=' . $generated_code . '">данной ссылке.</a></p>
	            <p>Если первый способ не работает, скопируйте полную ссылку в адресную строку: ' . $host_adr . '/auth/confirm?login=' . $post_login . '&code=' . $generated_code . '</p>
	          ';
			
			send_email($title, $subject, $post_email, $message);
			
			// ----------------------------
			$this->msg_info[] = "Учетная запись успешно создана, но ее необходимо активировать. На адрес <b>$post_email</b> отправлено письмо с дальнейшими инструкциями. Если сообщение не придет в течениe 5 минут, проверьте папку \"Спам\".

			<br>$hashed_code <br>$generated_code
			";
			
			debug($hashed_code);
			debug($generated_code);
			
			return true;
			
		}
		catch (Exception $e) {
			$this->msg_error[] = 'Ошибка БД.';
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Активация нового пользователя
	 * @param $login
	 * Логин пользователя
	 * @param $code
	 * Уникальный код для активации
	 * @return bool
	 */
	public function activate_account($login, $code)
	{
		if (!$login || !$code) return false;
		
		// проверка на существование пользователя с таким же логином
		$db_table = 'users';
		
		// формирую запрос
		$sql = "SELECT email,code,verified FROM $db_table WHERE login=?s";
		try // вставляю данные о блоке в базу
		{
			$row = $this->db->getRow($sql, $login);
			
			debug(md5($code));
			debug($row['code']);
			
			if ($row['verified']) {
				debug('Учетная запись уже активирована.');
				return false;
			} // если код совпал - активируем акк
			elseif (md5($code) == $row['code']) {
				// формирую запрос
				$sql = "UPDATE $db_table SET verified=1,code=NULL WHERE login=?s";
				
				$this->db->query($sql, $login);
				
				// подключаю класс email рассылок
				include_once Config::get('libs') . 'send_mail.php';
				
				// отправляю письмо
				$title   = "Регистрация на сайте PiqTune.com";
				$subject = "Регистрация на сайте PiqTune.com";
				$message = '
		            <p>' . $login . ', Ваша учетная запись успешно активирована.</p>
		        ';
				
				send_email($title, $subject, $row['email'], $message);
				
				return true;
				
			} else {
				debug('Неверный код активации.');
			}
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Проверка данных формы регистрации
	 * @param $post
	 * Данные с массива POST
	 * @return bool
	 */
	private function verify_reg_data($post)
	{
		$login  = isset($post['login']) ? trim($post['login']) : '';
		$passwd = isset($post['password']) ? trim($post['password']) : '';
		$email  = isset($post['email']) ? trim($post['email']) : '';
		
		$validation = true;
		
		// captcha check
		$secret_key       = '6LeR1SQTAAAAAD5X35SIwgu5vNYRHulrSkwqOLbe';
		$captcha_response = $_POST['g-recaptcha-response'];
		
		$myCurl = curl_init();
		curl_setopt_array($myCurl, array(
			CURLOPT_URL            => 'https://www.google.com/recaptcha/api/siteverify',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => http_build_query([
				'secret'   => $secret_key,
				'response' => $captcha_response
			])
		));
		$response = curl_exec($myCurl);
		curl_close($myCurl);
		
		$response_array = json_decode($response, true);
		
		if (!$response_array['success']) {
			$this->msg_error[] = 'Captcha не пройдена.';
			return false;
		}
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->msg_error[] = 'Введен некорректный e-mail.';
			$validation        = false;
		}
		
		if (!preg_match("/^[a-zA-Z0-9]{4,16}$/i", $login)) {
			$this->msg_error[] = 'Логин должен состоять как минимум из 4 символов латинского алфавита и/или цифр.';
			$validation        = false;
		}
		
		if (!preg_match("/^[a-zA-Z0-9!-_]{6,20}$/", $passwd)) {
			$this->msg_error[] = 'Пароль должен состоять как минимум из 6 символов латинского алфавита и цифр.';
			$validation        = false;
		}
		
		if (!$validation) return false;
		
		// проверка на занятость логина
		if (!$this->check_field('login', $login)) {
			$this->msg_error[] = 'Данный логин занят.';
			$validation        = false;
		}
		
		// проверка на занятость логина
		if (!$this->check_field('email', $email)) {
			$this->msg_error[] = 'Данный email занят.';
			$validation        = false;
		}
		
		if (!$validation) return false;
		
		$post['login']    = $login;
		$post['password'] = $passwd;
		$post['email']    = $email;
		return $post;
	}
	
	/**
	 * Проверка поля на существования в БД
	 * @param $field
	 * Поле
	 * @param $login
	 * Значение
	 * @return bool
	 */
	public function check_field($field, $login)
	{
		if (!$field || !$login) return false;
		
		// проверка на существование пользователя с таким же логином
		$db_table = 'users';
		
		// формирую запрос
		$sql = "SELECT 1 FROM $db_table WHERE ?n=?s";
		try // вставляю данные о блоке в базу
		{
			if (!$this->db->getOne($sql, $field, $login)) {
				return true;
			}
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Авторизует пользователя в системе
	 * @param $post
	 * Данные для авторизации
	 * @return array|bool
	 */
	public function authorize($post)
	{
		// проверка данных
		$post = $this->verify_auth_data($post);
		if (!$post) return false;
		
		// инициализирую нужные перменные
		$db_table     = 'users';
		$db_table_tie = 'tokens_to_users';
		$unixtime     = time();
		
		// готовлю запрос
		$sql = "SELECT login,password,remember_identifier,is_admin,banned,verified FROM $db_table WHERE login=?s";
		
		try // получаю строку по логину
		{
			if ($row = $this->db->getRow($sql, $post['login'])) {
				// проверяю пароль
				if (password_verify($post['password'], $row['password'])) {
					if (!$row['verified']) {
						$this->msg_error[] = 'Учетная запись не активирована.';
						return false;
					}
					if ($row['banned']) {
						$this->msg_error[] = 'Учетная запись заблокирована.';
						return false;
					}
					
					$data = ['last_date' => $unixtime];
					$sql  = "UPDATE $db_table SET ?u WHERE login=?s";
					$this->db->query($sql, $data, $row['login']);
					
					// работаю с remember me
					// устанавливаю куки, если нужно
					if (isset($post['remember_me'])) {
						// проверяю наличие идентификатора пользователя
						if (!empty($row['remember_identifier'])) {
							$remember_identifier = $row['remember_identifier'];
						} else {
							// генерируем идентификатор
							$remember_identifier = random_string(128);
							// кладем их в базу данных
							$sql_id = "UPDATE $db_table SET remember_identifier=?s WHERE login=?s";
							$this->db->query($sql_id, $remember_identifier, $row['login']);
						}
						
						// генерирую токен для браузера
						$remember_token = random_string(128);
						$hashed_token   = md5($remember_token);
						// добавляю токен для текущего браузера
						$sql_token = "INSERT INTO $db_table_tie SET identifier=?s, token=?s";
						$this->db->query($sql_token, $remember_identifier, $hashed_token);
						// если все ок, создаем куку
						setcookie("user_r", $remember_identifier . '___' . $remember_token, time() + 365 * 24 * 60 * 60, "/");
					}
					
					$output = ['login' => $row['login'], 'is_admin' => $row['is_admin']];
					return $output;
				}
			}
		}
		catch (Exception $e) {
			debug($e);
		}
		
		$this->msg_error[] = 'Неверный логин или пароль.';
		return false;
	}

	/**
	 * Проверка данных формы авторизации
	 * @param $post
	 * Данные с массива POST
	 * @return bool
	 */
	private function verify_auth_data($post)
	{
		$login   = isset($post['login']) ? trim($post['login']) : '';
		$passwd  = isset($post['password']) ? trim($post['password']) : '';
		$save_me = isset($post['save_me']) ? true : false;
		
		$validation = true;
		
		if (empty($login)) {
			$this->msg_error[] = 'Поле "логин" не заполнено.';
			$validation        = false;
		}
		if (empty($passwd)) {
			$this->msg_error[] = 'Поле "пароль" не заполнено.';
			$validation        = false;
		}
		
		if (!$validation) return false;
		
		$post['login']    = $login;
		$post['password'] = $passwd;
		$post['save_me']  = $save_me;
		return $post;
	}
	
	/**
	 * Проверяет идентификатор польователя и уникальный ключ на соответствие
	 * @param $identifier
	 * Идентификатор пользователя для входа
	 * @param $token
	 * Уникальный токен браузера
	 * @return array|bool
	 */
	public function check_remember_cred($identifier, $token)
	{
		if (!$identifier || !$token) return false;
		
		// инициализирую нужные перменные
		$db_table     = 'users';
		$db_table_tie = 'tokens_to_users';
		// хэшируем токен
		$hashed_token = md5($token);
		
		// готовлю запрос
		$sql = "SELECT u.login, u.is_admin, u.banned, t.token AS remember_token
			FROM $db_table AS u
			LEFT JOIN $db_table_tie AS t ON t.identifier = u.remember_identifier
			WHERE u.remember_identifier=?s AND t.token=?s AND u.banned=0
			LIMIT 1
		";
		
		try {
			return $this->db->getRow($sql, $identifier, $hashed_token);
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Очищает данные для автоматического входа в систему
	 * @param $token
	 * Уникальный токен браузера
	 * @param bool $identifier
	 * Идентификатор пользователя для входа
	 * @return bool
	 */
	public function clear_remembers($token, $identifier = false)
	{
		if (!$token) return false;
		
		// инициализирую нужные перменные
		$db_table     = 'users';
		$db_table_tie = 'tokens_to_users';
		
		// если передали идентификатор - полное удаление, выход из всех устройств
		if ($identifier) {
			// готовлю запросы
			$sql_tie   = "DELETE FROM $db_table_tie WHERE identifier=?s";
			$sql_users = "UPDATE $db_table SET remember_identifier=NULL WHERE remember_identifier=?s";
			
			try {
				$this->db->query($sql_tie, $identifier);
				$this->db->query($sql_users, $identifier);
				
				return true;
			}
			catch (Exception $e) {
				debug($e);
			}
		} else {
			// хэширую токен
			$hashed_token = md5($token);
			
			// готовлю запрос
			$sql = "DELETE FROM $db_table_tie WHERE token=?s";
			try {
				$this->db->query($sql, $hashed_token);
				return true;
			}
			catch (Exception $e) {
				debug($e);
			}
		}
		
		return false;
	}
	
	/**
	 * Блокировка пользователя по IP
	 * @param $ip
	 * Адрес клиента
	 * @return bool
	 */
	public function block_user($ip)
	{
		if (empty($ip)) return false;
		
		// имя таблицы
		$db_table = 'blocks';
		
		
		// создаю таблицу, если ее еще нет
		$sql = "CREATE TABLE IF NOT EXISTS $db_table (
			ip VARCHAR(15),
			block_date INT,
			PRIMARY KEY(ip))
			ENGINE = InnoDB
			DEFAULT CHARACTER SET = utf8
			COLLATE = utf8_general_ci";
		
		try // кидаю запрос на создание таблица, если ее нет
		{
			$this->db->query($sql);
			
			$unixtime = time();
			
			$data = ['ip' => $ip, 'block_date' => $unixtime];
			$sql  = "INSERT INTO $db_table SET ?u";
			
			// вставляю данные о блоке в базу
			$this->db->query($sql, $data);
			return true;
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Проверка статуса ip на блок
	 * @param $ip
	 * Адрес клиента
	 * @return bool|resource
	 */
	public function block_check($ip)
	{
		if (empty($ip)) return false;
		
		// получаю из конфига время блокировки
		$duration = Config::get('block_duration', 5 * 60);
		
		// имя таблицы
		$db_table = 'blocks';
		
		
		try {
			$row = $this->db->getRow("SELECT * FROM $db_table WHERE ip='$ip'");
			
			$time_difference = time() - $row['block_date'];
			
			if ($time_difference < $duration) {
				return $row['block_date'] + $duration;
			} else {
				return $this->db->query("DELETE FROM $db_table WHERE ip='$ip'");
			}
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Отправляет ссылку на восстановление пароля
	 * @param $post
	 * Данные с массива POST
	 * @return bool
	 */
	public function send_restore_link($post)
	{
		// проверка поста
		if (!$post) return false;
		
		// captcha check
		$secret_key       = '6LeR1SQTAAAAAD5X35SIwgu5vNYRHulrSkwqOLbe';
		$captcha_response = $_POST['g-recaptcha-response'];
		
		$myCurl = curl_init();
		curl_setopt_array($myCurl, array(
			CURLOPT_URL            => 'https://www.google.com/recaptcha/api/siteverify',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => http_build_query([
				'secret'   => $secret_key,
				'response' => $captcha_response
			])
		));
		$response = curl_exec($myCurl);
		curl_close($myCurl);
		
		$response_array = json_decode($response, true);
		
		if (!$response_array['success']) {
			$this->msg_error[] = 'Captcha не пройдена.';
			return false;
		}
		
		// -----------------------------------------------------------
		
		$email = isset($post['email']) ? trim($post['email']) : '';
		
		if (empty($email)) {
			$this->msg_error[] = 'Поле "E-mail" не может быть пустым.';
			return false;
		}
		
		// инициализирую необходимые перменные
		$db_table       = 'users';
		$generated_code = random_string(128);
		$hashed_code    = md5($generated_code);
		
		// ищу почту в базе
		$sql = "SELECT login,verified FROM $db_table WHERE email=?s LIMIT 1";
		try {
			$row = $this->db->getRow($sql, $email);
			// проверяю, активировали ли учетку
			if (!$row['verified']) {
				$this->msg_error[] = 'Учетная запись не активирована.';
				return false;
			}
			
			// если найдена учетка - высылаю письмо
			if ($row['login']) {
				// обновляю код в базе
				$sql = "UPDATE $db_table SET recover_hash='$hashed_code' WHERE login=?s";
				
				$this->db->query($sql, $row['login']);
				$host_adr = 'http://' . $_SERVER['HTTP_HOST'];
				
				// подключаю класс email рассылок
				include_once Config::get('libs') . 'send_mail.php';
				
				// отправляю письмо
				$title   = "Восстановление пароля на сайте PiqTune.com";
				$subject = "Восстановление пароля на сайте PiqTune.com";
				$message = '
	              <p>Приветствуем, ' . $row['login'] . '!</p>
	              <p>Для создания нового пароля вам необходимо перейти по <a style="text-decoration: none;" href="' . $host_adr . '/auth/reset?email=' . $email . '&code=' . $generated_code . '">данной ссылке.</a></p>
	              <p>Если первый способ не работает, скопируйте полную ссылку в адресную строку: ' . $host_adr . '/auth/reset?email=' . $email . '&code=' . $generated_code . '</p>
	             ';
				
				send_email($title, $subject, $email, $message);
				
				$this->msg_info[] = "На адрес <b>$email</b> отправлено письмо с дальнейшими инструкциями. Если сообщение не придет в течениe 5 минут, проверьте папку \"Спам\".";
				return true;
				
			} else {
				$this->msg_error[] = "Адрес <b>$email</b> не найден.";
				return false;
			}
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Сброс пароля
	 * @param $email
	 * E-mail адрес
	 * @param $code
	 * Уникальный код восстановления
	 * @return bool
	 */
	public function check_hash($email, $code)
	{
		if (!$email || !$code) return false;
		
		// инициализирую необходимые перменные
		$db_table = 'users';
		
		$sql = "SELECT recover_hash FROM $db_table WHERE email=?s";
		
		try {
			if ($db_hash = $this->db->getOne($sql, $email)) {
				$hashed_code = md5($code);
				
				if ($hashed_code == $db_hash) return true;
			}
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Смена пароля
	 * @param $post
	 * Данные с массива POST
	 * @return bool
	 */
	public function change_password($post)
	{
		// проверка введенной инфы
		if (!$this->verify_reset_data($post)) return false;
		
		// инициализирую необходимые перменные
		$db_table = 'users';
		
		// проверяю текущий пароль
		$sql = "SELECT password FROM $db_table WHERE login=?s";
		try {
			// получаю хэш текущего пароля
			$curr_pass_hash = $this->db->getOne($sql, $post['login']);
			// сравниваю с введенной инфой
			if (password_verify($post['old_password'], $curr_pass_hash)) {
				// проверяю новый пароль на соответствие со старым
				if (password_verify($post['password'], $curr_pass_hash)) {
					$this->msg_error[] = 'Новый пароль не должен совподать с текущим.';
					return false;
				}
				
				// формирую запрос
				$sql = "UPDATE $db_table SET password=?s WHERE login=?s";
				// кидаю его, меняю пароль
				$password_hash = password_hash($post['password'], PASSWORD_DEFAULT);
				$this->db->query($sql, $password_hash, $post['login']);
				
				$this->msg_info[] = 'Пароль успешно изменен.';
				return true;
			} else {
				$this->msg_error[] = 'Введен неверный текущий пароль.';
			}
		}
		catch (Exception $e) {
			$this->msg_error[] = 'Произошла ошибка при смене пароля.';
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Сброс пароля
	 * @param $post
	 * Данные с массива POST
	 * @return bool
	 */
	public function reset_password($post)
	{
		if (!$this->verify_reset_data($post)) return false;
		
		// инициализирую необходимые перменные
		$db_table      = 'users';
		$password_hash = password_hash($post['password'], PASSWORD_DEFAULT);
		
		// формирую запрос
		$sql = "UPDATE $db_table SET password=?s,recover_hash=NULL WHERE email=?s";
		// кидаю его, меняю пароль
		try {
			$this->db->query($sql, $password_hash, $post['email']);
			
			$this->msg_info[] = 'Пароль успешно изменен.';
			return true;
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
	
	/**
	 * Проверка формы сброса пароля
	 * @param $post
	 * Данные с массива POST
	 * @return bool
	 */
	private function verify_reset_data($post)
	{
		if (!$post) return false;
		
		$password    = isset($post['password']) ? trim($post['password']) : '';
		$re_password = isset($post['re_password']) ? trim($post['re_password']) : '';
		
		if (!preg_match("/^[a-zA-Z0-9]{6,20}$/", $password)) {
			$this->msg_error[] = 'Пароль должен состоять как минимум из 6 символов латинского алфавита и цифр.';
			return false;
		}
		
		if ($password != $re_password) {
			$this->msg_error[] = 'Пароли не совпадают.';
			return false;
		}
		
		return true;
	}
	
	/**
	 * Проверяет и удаляет неактивные учетки
	 * @param $exp_date
	 * Время для хранения
	 * @return bool
	 */
	public function remove_inactive_users($exp_date)
	{
		if (!$exp_date) return false;
		
		$db_table = 'users';
		
		$sql = "DELETE FROM $db_table WHERE reg_date < ?i AND verified=0";
		
		try {
			return $this->db->query($sql, $exp_date);
		}
		catch (Exception $e) {
			debug($e);
		}
		
		return false;
	}
}