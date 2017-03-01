<?php

class Controller_Info extends Controller
{
	/**
	 * Страница "О сайте"
	 */
	public function action_index()
	{
		$this->data['page_title'] = 'О сайте';
		
		$view = new View();
		$view->draw('info/view_about.php', 'templates/template.php', $this->data);
	}

	/**
	 * Страница "Помощь"
	 */
	public function action_help()
	{
		$this->data['page_title'] = 'Помощь';
		
		$view = new View();
		$view->draw('info/view_help.php', 'templates/template.php', $this->data);
	}
	
	/**
	 * Страница "Контакты"
	 */
	public function action_contacts()
	{
		$this->data['page_title'] = 'Контакты';
		
		$view = new View();
		$view->draw('info/view_contacts.php', 'templates/template.php', $this->data);
	}
}
