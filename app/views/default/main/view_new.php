<?
$content  = &$data['content'];
$quantity = &$data['quantity'];
$settings = &$data['settings'];

if (empty($content)) $content = [];
// подключаю функцию генерации поста
include Config::get('app').'views/default/posts/view_item.php';
?>

<div class="container">
	<div class="section favorites center">
		
		<div class="main-links card center">
			<a href="/main/best" class="link grey-text waves-effect waves-dark"><i class="material-icons left">stars</i>Лучшее</a>
			<a href="#" class="link grey-text text-darken-1 waves-effect waves-dark sel"><i class="material-icons left">new_releases</i>Новое</a>
		</div>
		
		<br>
		
		<? if (!empty($content)): ?>
			
			<? foreach ($content as $row): ?>
				<?= make_item($row) ?>
			<? endforeach; ?>
		<? else: ?>
			<p class="sub-text grey-text">Тут ничего нет...</p>
		<? endif; ?>
		
		<input type="hidden" class="_token" value="<?= $_SESSION['_token'] ?>">
	</div>
	
	<? if ($quantity > $settings['limit']): ?>
		<div class="load-more center" type="new" offset="<?= $settings['offset'] ?>" limit="<?= $settings['limit'] ?>">
			<a class="btn waves-effect waves-light">Загрузить еще<i class="material-icons right">autorenew</i></a>
		</div>
	<? endif; ?>
</div>