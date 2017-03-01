<?php

class View
{
	public $template;
	
	function __construct()
	{
		// устанавливаю шаблон по умолчанию
		if ($default_view = Config::get('default_view', 'default')) {
			$this->template = $default_view;
		} else die('Не указан шаблон по умолчанию в конфигурациях!');
	}
	
	/**
	 * Выводит весь HTML
	 * @param $content_view
	 * Шаблон контента
	 * @param $template_view
	 * Шаблн страницы
	 * @param null $data
	 * Данные
	 */
	function draw($content_view, $template_view, $data = null)
	{
		include_once Config::get('app').'views/' . $this->template . '/' . $template_view;
	}
}

// класс виджета
class Widget_View
{
	/**
	 * Вывод виджеты на экран
	 * @param $widget_view
	 * Шаблон виджета
	 * @param null $data
	 * Данные
	 */
	function draw($widget_view, $data = null)
	{
		include_once Config::get('app').'widgets/views/' . $widget_view;
	}
}