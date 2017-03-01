<?
$messages = &$data['messages'];
$content  = &$data['content'];
$quantity = &$data['quantity'];
$settings = &$data['settings'];
$username = &$data['username'];

if (empty($content)) $content = [];
// подключаю функцию генерации поста
include Config::get('app').'views/default/posts/view_item.php';

// показываю ошибки, если они есть
if ($messages) echo create_messagebox($messages);
?>

<div class="container">
	<div class="section profile center">
		
		<? if (!empty($content)): ?>
			<h4 class="header page-title grey-text text-darken-1">Посты пользователя <?= $username ?></h4>
			<? foreach ($content as $row): ?>
				<?= make_item($row) ?>
			<? endforeach; ?>
		<? else: ?>
			<p class="sub-text grey-text">Тут ничего нет...</p>
		<? endif; ?>
		<input type="hidden" class="_token" value="<?= $_SESSION['_token'] ?>">
		<input type="hidden" class="_username" value="<?= $username ?>">
	</div>
	
	<? if ($quantity > $settings['limit']): ?>
		<div class="load-more center" type="my_posts" offset="<?= $settings['offset'] ?>"
		     limit="<?= $settings['limit'] ?>">
			<a class="btn waves-effect waves-light">Загрузить еще<i class="material-icons right">autorenew</i></a>
		</div>
	<? endif; ?>
</div>