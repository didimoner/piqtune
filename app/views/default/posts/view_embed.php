<?
$content = &$data['content'];

$images_path = 'images/pics/';
$audios_path = 'audio/';

// формирую файлы для просмотра
$image_file = $images_path . $content['id'] . '.' . $content['image'];
$audio_file = $audios_path . $content['id'] . '.' . $content['audio'];
// проверяю их наличие
if (!is_file(Config::get('www') . $image_file)) $image_file = $images_path . 'not_found.jpg';
if (!is_file(Config::get('www') . $audio_file)) $audio_file = '';

$host_adr = 'http://' . $_SERVER['HTTP_HOST'];

?>

<div class="player embed">
	<a class="title" href="<?= (isset($content['id']) ? $host_adr.'/posts/'.$content['id'] : $host_adr) ?>" target="_blank"
	   rel="noopener noreferrer"><?= isset($content['title']) ? e($content['title']) : '' ?></a>
	<img src="/<?= $image_file ?>">
	<audio>
		<source src="/<?= $audio_file ?>" type='audio/mpeg; codecs="mp3"'>
	</audio>
	<div class="controls">
		<i class="material-icons play" title="Воспроизвести/остановить">play_circle_filled</i>
		<i class="material-icons volume" title="Управление громкостью">volume_up</i>
		<i class="material-icons loop inactive" title="Зациклить воспроизведение">repeat</i>
		<a class="right" href="<?= (isset($content['id']) ? $host_adr.'/posts/'.$content['id'] : $host_adr) ?>"
		   target="_blank" rel="noopener noreferrer">PiqTune</a>
	</div>
</div>
