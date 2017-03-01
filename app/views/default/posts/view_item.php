<?php

function make_item($row)
{
	$images_path = 'images/pics/';
	$audios_path = 'audio/';
	
	// формирую файлы для просмотра
	$image_file = $images_path . $row['id'] . '.' . $row['image'];
	$audio_file = $audios_path . $row['id'] . '.' . $row['audio'];

	// проверяю их наличие
	if (!is_file(Config::get('www') . $image_file)) $image_file = $images_path . 'not_found.jpg';
	if (!is_file(Config::get('www') . $audio_file)) $audio_file = '';
	
	$output         = '';
	$favorites_star = '';
	
	if ($_SESSION['user']['auth']) {
		// проверяю на наличие в избарнном поста
		if ($row['is_favorite']) {
			$favorites_star = '<a class="star grey-text text-darken-3" mode="dec" id="' . $row['id'] . '" title="Убрать из избранного"><i class="material-icons yellow-text text-darken-1">star</i></a>';
		} else {
			$favorites_star = '<a class="star grey-text text-darken-3" mode="inc" id="' . $row['id'] . '" title="Добавить в избранное"><i class="material-icons yellow-text text-darken-1">star_border</i></a>';
		}
	}
	
	$output .= '
    <div class="item card hoverable watch-wrap">
	    <div class="top-info">
	        ' . $favorites_star . '
			<a href="/posts/' . $row['id'] . '">
			<h5 class="title ' . (!empty($favorites_star) ? 'short' : 'long') . ' grey-text text-darken-1 truncate" title="' . (isset($row['title']) ? e($row['title']) : 'Неизвестно') . '">' . (isset($row['title']) ? e($row['title']) : 'Неизвестно') . '</h5>
			</a>
		</div>
      
      <div class="player">
        <img class="responsive-img" src="/' . $image_file . '" alt="' . (isset($row['title']) ? e($row['title']) : 'Неизвестно') . '">
        <audio preload="metadata">
           <source src="/' . $audio_file . '" type=\'audio/mpeg; codecs="mp3"\'>
        </audio>
        <div class="controls">
          <i class="material-icons play" title="Воспроизвести/остановить">play_circle_filled</i>
          <i class="material-icons volume" title="Управление громкостью">volume_up</i>
          <i class="material-icons loop inactive" title="Зациклить воспроизведение">repeat</i>
          '.($_SESSION['user']['auth'] ? '<i class="material-icons report right" id="'. $row['id'] .'" title="Пожаловаться">flag</i>' : '').'
        </div>
      </div>

      <div class="sub-info">
        <a class="emotion" href="/search/results?emotion=' . (isset($row['emotion']) ? $row['emotion'] : '') . '">
            <img src="/images/emotions/small/' . (isset($row['emotion']) ? $row['emotion'] : '') . '.png"></a>
        <div class="low_info-wrap">
          <p class="nomargin small-text grey-text">
            Создал: <a href="/users/profile/' . $row['author'] . '">' . e($row['author']) . '</a>,
            <span class="rating-text">' . (isset($row['rating']) ? declination(e($row['rating']), 'звезда', 'звезды', 'звезд') : '0 звезд') . '</span>
          </p>
          <p class="nomargin small-text grey-text">Опубликовано: ' . (isset($row['date']) ? date("d.m.Y г.", $row['date']) : 'Неизвестно') . '</p>
        </div>
      </div>
    </div>
  ';
	
	return $output;
}