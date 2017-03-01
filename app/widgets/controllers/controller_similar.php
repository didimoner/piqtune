<?php

class Controller_Similar extends Controller
{
	public function __construct($data)
	{
		$this->data['similar'] = $data;
	}
	
	public function insert()
	{
		include_once Config::get('app').'models/model_posts.php';
		$model = new Model_Posts();
		
		// количество постов
		$quantity = 10;
		
		// кидаю запрос в модель для получения элементов
		$data_by_emotion = $model->get_by_emotion($this->data['similar']['emotion'], $this->data['similar']['id'], $quantity);

		$data_random = [];
		if (count($data_by_emotion) < $quantity) $data_random = $model->get_missing_posts($this->data['similar']['emotion'], $this->data['similar']['id'], $quantity - count($data_by_emotion));
		
		$this->data['similar']['posts']['similar'] = $data_by_emotion;
		$this->data['similar']['posts']['random']  = $data_random;
		
		$view = new Widget_View();
		$view->draw('view_similar.php', $this->data);
	}
}