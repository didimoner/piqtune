<?php

class Controller_404 extends Controller
{
	public function action_index()
	{
		$this->data['page_title'] = 'Ошибка 404';
		
		$view = new View();
		$view->draw('view_404.php', 'templates/template.php', $this->data);
	}
}
