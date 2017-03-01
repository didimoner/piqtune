<?php
// файл со всей конфтгурацией системы
$config = [];

// директории
$config['www']     = '../public/';
$config['libs']    = '../libs/';
$config['app']     = '../app/';
$config['configs'] = '../configs/';

// БД
$config['db'] = [
	'host'     => 'localhost',
	'login'    => 'mysql',
	'password' => 'mysql',
	'name'     => 'piqtune',
	'charset'  => 'utf8'
];

// шаблон по умолчанию
$config['default_view'] = 'default';

// время на редактирование поста (минуты)
$config['posts_allowed_time'] = 30 * 60;

// количество попыток входа
$config['attempts'] = 5;
// время блокировки
$config['block_duration'] = 3 * 60;

// время жизни свежего
$config['new_posts_period'] = strtotime("-1 month");

// время жизни неактивных учеток
$config['inactive_users_time'] = strtotime("-3 days");

return $config;