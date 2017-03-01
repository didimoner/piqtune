<?
$content  = &$data['content'];
$quantity = &$data['quantity'];
$settings = &$data['settings'];

$page_title = '';

switch ($settings['period']) {
	case 'week':
		$page_title     = 'за неделю';
		$redirect_title = 'за месяц';
		$redirect_url   = '/main/best?period=month';
		break;
	case 'month':
		$page_title     = 'за месяц';
		$redirect_title = 'за год';
		$redirect_url   = '/main/best?period=year';
		break;
	case 'year':
		$page_title     = 'за год';
		$redirect_title = 'за все время';
		$redirect_url   = '/main/best?period=all_time';
		break;
	case 'all_time':
		$page_title = 'за все время';
		break;
}

if (empty($content)) $content = [];
// подключаю функцию генерации поста
include Config::get('app').'views/default/posts/view_item.php';
?>

<div class="container">
	<div class="section favorites center">
		
		<div class="main-links card center">
			<a class="link dropdown-button grey-text waves-effect waves-dark sel" href="#" data-activates="time_menu"><i
					class="material-icons left">stars</i>Лучшее<i class='material-icons right drop'>arrow_drop_down</i></a>
			
			<ul id='time_menu' class='dropdown-content'>
				<li><a href="/main/best?period=week">за неделю</a></li>
				<li><a href="/main/best?period=month">за месяц</a></li>
				<li><a href="/main/best?period=year">за год</a></li>
				<li><a href="/main/best?period=all_time">за все время</a></li>
			</ul>
			
			<a href="/main/new" class="link grey-text waves-effect waves-dark"><i class="material-icons left">new_releases</i>Новое</a>
		</div>
		
		<br>
		
		<h4 class="header page-title grey-text text-darken-1">Лучшие посты <?= $page_title ?>:</h4>
		<? if (!empty($content)): ?>
			
			<? foreach ($content as $row): ?>
				<?= make_item($row) ?>
			<? endforeach; ?>
		<? else: ?>
			<p class="sub-text grey-text">Тут ничего нет...</p>
			<p class="sub-text grey-text"><a href="<?= $redirect_url ?>">Посмотреть лучшее <?= $redirect_title ?></a>
			</p>
		
		
		<? endif; ?>
		
		<input type="hidden" class="_token" value="<?= $_SESSION['_token'] ?>">
	</div>
	
	<? if ($quantity > $settings['limit']): ?>
		<div class="load-more center" type="best" offset="<?= $settings['offset'] ?>" limit="<?= $settings['limit'] ?>">
			<a class="btn waves-effect waves-light">Загрузить еще<i class="material-icons right">autorenew</i></a>
		</div>
	<? endif; ?>
</div>