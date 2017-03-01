<?php

/**
 * Создает директорию с правами 777
 * @param $path
 * Путь и имя каталога
 */
function create_dir($path)
{
	$old_umask = umask(0);
	mkdir($path, 0777);
	umask($old_umask);
}

/**
 * Обезараживание данных
 * @param $value
 * Данные
 * @return string
 */
function e($value)
{
	return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * Ресайз изображений
 * @param $image
 * Путь к изображению
 * @param bool $w_o
 * Ширина выходного изображения
 * @param bool $h_o
 * Высота выходного изображения
 * @return bool
 */
function resize($image, $w_o = false, $h_o = false)
{
	if (($w_o < 0) || ($h_o < 0)) {
		return false;
	}
	list($w_i, $h_i, $type) = getimagesize($image); // Получаем размеры и тип изображения (число)
	$types = array("", "gif", "jpeg", "png"); // Массив с типами изображений
	$ext   = $types[$type]; // Зная "числовой" тип изображения, узнаём название типа
	if ($ext) {
		$func  = 'imagecreatefrom' . $ext; // Получаем название функции, соответствующую типу, для создания изображения
		$img_i = $func($image); // Создаём дескриптор для работы с исходным изображением
	} else {
		debug('Некорректное изображение'); // Выводим ошибку, если формат изображения недопустимый
		return false;
	}
	/* Если указать только 1 параметр, то второй подстроится пропорционально */
	if (!$h_o) $h_o = $w_o / ($w_i / $h_i);
	if (!$w_o) $w_o = $h_o / ($h_i / $w_i);
	$img_o = imagecreatetruecolor($w_o, $h_o); // Создаём дескриптор для выходного изображения
	imagecopyresampled($img_o, $img_i, 0, 0, 0, 0, $w_o, $h_o, $w_i, $h_i); // Переносим изображение из исходного в выходное, масштабируя его
	$func = 'image' . $ext; // Получаем функция для сохранения результата
	return $func($img_o, $image); // Сохраняем изображение в тот же файл, что и исходное, возвращая результат этой операции
}

/**
 * Формирует блоки ошибки и информации
 * @param $messages_array
 * Массив сообщений
 * @return bool|string
 */
function create_messagebox($messages_array)
{
	if (empty($messages_array)) return false;
	if (empty($messages_array['error']) && empty($messages_array['info'])) return false;
	
	$text_class = '';
	$output     = '';
	
	$output .= '<div id="modal-messages" class="modal bottom-sheet">';
	$output .= '<div class="modal-content">';
	$output .= '<ul class="collection with-header">';
	$output .= '<li class="collection-header"><h4>Уведомления</h4></li>';
	
	foreach ($messages_array as $type => $messages) {
		if ($type == 'error') {
			$text_class = 'red-text';
		} else {
			$text_class = 'blue-text';
		}
		
		if (!empty($messages)) {
			foreach ($messages as $message) $output .= '<li class="collection-item ' . $text_class . '">' . $message . '</li>';
		}
	}
	
	$output .= '</ul>';
	$output .= '</div></div>';
	
	$output .= '<a class="btn-floating btn-large waves-effect waves-light blue modal-trigger" href="#modal-messages" title="Уведомления"><i class="material-icons">notifications_active</i></a>';
	
	return $output;
}

/**
 * Склонение слова по формам числительного (например "статья")
 * @param $num
 * Число
 * @param $one
 * "статья"
 * @param $ed
 * "статьи"
 * @param $mn
 * "статей"
 * @param bool $notnumber
 * [Optional] Выводить ли число
 * @return string
 */
function declination($num, $one, $ed, $mn, $notnumber = false)
{
	if ($num === "") print "";
	if (($num == "0") or (($num >= "5") and ($num <= "20")) or preg_match("|[056789]$|", $num))
		if (!$notnumber)
			return "$num $mn";
		else
			return $mn;
	if (preg_match("|[1]$|", $num))
		if (!$notnumber)
			return "$num $one";
		else
			return $one;
	if (preg_match("|[234]$|", $num))
		if (!$notnumber)
			return "$num $ed";
		else
			return $ed;
}

/*
 * Create a random string
 * @author  XEWeb <>
 * @param $length the length of the string to create
 * @return $str the string
 */
function random_string($length = 6)
{
	$str        = "";
	$characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'), ['_', '-']);
	$max        = count($characters) - 1;
	for ($i = 0; $i < $length; $i++) {
		$rand = mt_rand(0, $max);
		$str .= $characters[$rand];
	}
	return $str;
}