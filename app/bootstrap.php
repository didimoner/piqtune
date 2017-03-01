<?php
// стартую сессию
session_start();

// подключаю конфиг
include_once '../libs/config.class.php';
// подключаю phpconsole
include_once Config::get('libs').'phpconsole_connector.php';

// подключение ядра и вспомогательных функций
require_once Config::get('libs').'help_functions.php';
require_once 'core/model.php';
require_once 'core/view.php';
require_once 'core/controller.php';

// подключаю класс для работы с БД
include_once Config::get('libs').'safemysql.class.php';

// CSRF защита
if (!isset($_SESSION['_token'])) $_SESSION['_token'] = bin2hex(openssl_random_pseudo_bytes(16));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!isset($_POST['_token']) || ($_POST['_token'] !== $_SESSION['_token'])) {
		debug('Invalid CSRF token!');
		die('Invalid CSRF token!');
	}
	// генерирую новый токен
	$_SESSION['_token'] = bin2hex(openssl_random_pseudo_bytes(16));
}

// инициализирую переменые для авторизации
if (!isset($_SESSION['user']['auth'])) $_SESSION['user']['auth'] = false;
if (!isset($_SESSION['user']['login'])) $_SESSION['user']['login'] = '';
if (!isset($_SESSION['user']['is_admin'])) $_SESSION['user']['is_admin'] = false;
if (!isset($_SESSION['user']['attempts'])) $_SESSION['user']['attempts'] = 0;

// проверяю куки
require_once 'core/preloader.php';
if (!$_SESSION['user']['auth']) Preloader::check_cookie();;

require_once 'core/router.php';
Router::start(); // запускаем маршрутизатор