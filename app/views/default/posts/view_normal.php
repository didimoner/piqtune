<?
$content = &$data['content'];

// ссылка для встраивания
$host_url   = 'http://' . $_SERVER['HTTP_HOST'] . '/';
$embed_code = '<iframe width="560" height="315" src="' . $host_url . 'posts/embed/' . $content['id'] . '" frameborder="0" allowfullscreen></iframe>';
$post_link  = $host_url . 'posts/' . $content['id'];

$images_path = 'images/pics/';
$audios_path = 'audio/';

// формирую файлы для просмотра
$image_file = $images_path . $content['id'] . '.' . $content['image'];
$audio_file = $audios_path . $content['id'] . '.' . $content['audio'];
// проверяю их наличие
if (!is_file(Config::get('www') . $image_file)) $image_file = $images_path . 'not_found.jpg';
if (!is_file(Config::get('www') . $audio_file)) $audio_file = '';

// если есть виджеты - подключаю классы к шаблону
if (isset($data['widgets']) && !empty($data['widgets'])) {
	$widgets = [];
	foreach ($data['widgets'] as $widget) {
		if ($widget['dir']) $widget['dir'] .= '/';
		// подключаю класс контроллера виджета
		include_once Config::get('app').'widgets/controllers/' . $widget['dir'] . 'controller_' . $widget['name'] . '.php';
		$controller_name          = 'Controller_' . $widget['name'];
		$widgets[$widget['name']] = new $controller_name($widget['data']);
	}
}
?>

<div class="container">
	<div class="section">
		<div class="row full-item watch-wrap normal">
			<div class="col l8 s12 left-col">

				<h5 class="title"><?= isset($content['title']) ? e($content['title']) : 'Неизвестно' ?></h5>

				<p class="small-text edit">

					<? if ($_SESSION['user']['auth']): ?>
						<? if ($content['is_favorite']): ?>
							<a class="star grey-text" mode="dec" id="<?= $content['id'] ?>" title="Убрать из избранного">
								<i class="material-icons yellow-text text-darken-1">star</i>
								<span class="a-text">Убрать из избранного</span>
							</a>
						<? else: ?>
							<a class="star grey-text" mode="inc" id="<?= $content['id'] ?>" title="Добавить в избранное">
								<i class="material-icons yellow-text text-darken-1">star_border</i>
								<span class="a-text">Добавить в избранное</span>
							</a>
						<? endif; ?>
					<? endif; ?>

					<? if ($content['edit']): ?>
						<a href="/posts/edit/<?= $content['id'] ?>">[Изменить]</a>
					<? endif; ?>
					<? if ($content['delete']): ?>
						<a class="red-text" href="/posts/delete/<?= $content['id'] ?>"
						   onclick="return confirm('Вы действительно хотите удалить пост?')">[Удалить]</a>
					<? endif; ?>
				</p>
				
				<div class="player">
					<img class="responsive-img" src="/<?= $image_file ?>" alt="<?= $content['title'] ?>">
					<audio autoplay>
						<source src="/<?= $audio_file ?>" type='audio/mpeg; codecs="mp3"'>
					</audio>
					<div class="controls">
						<i class="material-icons play" title="Воспроизвести/остановить">play_circle_filled</i>
						<i class="material-icons volume" title="Управление громкостью">volume_up</i>
						<i class="material-icons loop inactive" title="Зациклить воспроизведение">repeat</i>
						<? if ($_SESSION['user']['auth']): ?>
							<i class="material-icons report right" id="<?= $content['id'] ?>" title="Пожаловаться">flag</i>
						<? endif; ?>
					</div>
				</div>
				
				<div class="sub-info">
					<a class="emotion"
					   href="/search/results?emotion=<?= (isset($content['emotion']) ? $content['emotion'] : '') ?>"><img src="/images/emotions/small/<?= isset($content['emotion']) ? $content['emotion'] : '' ?>.png"></a>

					<div class="low_info-wrap">
						<p class="author small-text grey-text">
							Создал: <?= isset($content['author']) ? '<a href="/users/profile/'.e($content['author']).'">'.e($content['author']).'</a>' : 'Неизвестно' ?>,
							<span class="rating-text"><?= isset($content['rating']) ? declination(e($content['rating']), 'звезда', 'звезды', 'звезд') : '0 звезд' ?> </span>
						</p>
						<p class="date small-text grey-text">
							Опубликовано: <?= isset($content['date']) ? date("d.m.Y г.", $content['date']) : 'Неизвестно' ?></p>
					</div>
					
					<div class="clear"></div>
					<br>
					
					<div class="tags">
						<? // вывожу теги
						if (isset($content['tags'])) {
							$tag_list = explode(',', $content['tags']);
							
							foreach ($tag_list as $tag) {
								echo '<a href="/search/results?tags=' . e($tag) . '"><p class="tag grey darken-1 white-text">' . e($tag) . '</p></a>';
							}
						}
						?>
					</div>
					
					<div class="share">
						<label>Ссылка на пост: (<a class="copy" data-clipboard-target="#post-link">Копировать</a>)</label>
						<div id="post-link" contentEditable="true"><?= e($post_link) ?></div>
						<label>HTML-код для встраивания: (<a class="copy" data-clipboard-target="#embed-link">Копировать</a>)</label>
						<div id="embed-link" contentEditable="true"><?= e($embed_code) ?></div>
					</div>
				
				</div>
				
				<input type="hidden" class="_token" value="<?= $_SESSION['_token'] ?>">
			</div>
			
			<div class="col l4 s12 right-col">
				<?php if (isset($widgets['similar'])) $widgets['similar']->insert(); ?>
			</div>
		</div>
	</div>
</div>