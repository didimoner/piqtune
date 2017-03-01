<?php

class Controller_Tools extends Controller
{
	public function action_index()
	{
		exit();
	}
	
	public function action_clear_inactive_users()
	{
		include_once Config::get('app').'models/model_auth.php';
		// создаем объект модели
		$model = new Model_Auth();
		
		$time_period = Config::get('inactive_users_time', strtotime("-1 day"));
		$model->remove_inactive_users($time_period);
		
		echo 'OK';
	}
}