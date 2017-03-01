<?
$messages     = &$data['messages'];
$content      = &$data['content'];
$quantity     = &$data['quantity'];
$target       = &$data['target'];
$emotion      = &$data['emotion'];
$initial_tags = &$data['initial_tags'];
$settings     = &$data['settings'];

$emotion = isset($emotion) && !empty($emotion) ? $emotion : 'none';

$emotions_list = [
	'happy'   => 'Радость',
	'lol'     => 'Смех',
	'annoyed' => 'Раздражение',
	'sad'     => 'Печаль',
	'wtf'     => 'Озадаченность',
	'wow'     => 'Удивление',
	'scared'  => 'Испуг',
	'cry'     => 'Плач',
	'angry'   => 'Злость',
	'none'    => 'Без эмоции'
];

if (empty($content)) $content = [];
// подключаю функцию генерации поста
include Config::get('app').'views/default/posts/view_item.php';

// показываю ошибки, если они есть
if ($messages) echo create_messagebox($messages);
?>

<div class="container">
	<div class="section center">
		
		<form method="get" id="search-form" class="form-wrap search-wrapper card">
			<h4 class="header grey-text text-darken-1">Поиск</h4>
			
			<div class="input-field nomargin">
				<input type="search" name="target" value="<?= isset($target) ? $target : '' ?>" placeholder="Введите запрос...">
			</div>
			
			<i class="material-icons close">close</i>
			<textarea class="tags" name="tags"></textarea>
			
			<div class="emotions">
				<label>Выберите эмоцию:</label>
				<ul>
					<? foreach ($emotions_list as $name => $desc): ?>
						<?
						$is_active = '';
						if ($emotion == $name) $is_active = ' class="active"';
						?>
						<li<?= $is_active ?>><img class="<?= $name ?>" src="/images/emotions/small/<?= $name ?>.png"
						                          alt="<?= $name ?>" title="<?= $desc ?>"></li>
					<? endforeach; ?>
				</ul>
				<input type="hidden" name="emotion"
				       value="<?= (isset($emotion) && $emotion != 'none') ? $emotion : '' ?>">
			</div>
			
			<button class="btn form_btn waves-effect waves-light" type="submit">Искать
				<i class="material-icons right">search</i>
			</button>
			<a class="btn form_btn waves-effect waves-light reset">Сбросить</a>
		</form>
		
		<? if (!empty($content)): ?>
			<h4 class="header page-title grey-text text-darken-1">Найденные посты:</h4>
		<? else: ?>
			<h4 class="header page-title grey-text text-darken-1">Ничего не найдено</h4>
		<? endif; ?>
		
		<? foreach ($content as $row): ?>
			<?= make_item($row) ?>
		<? endforeach; ?>
		
		<input type="hidden" class="_token" value="<?= $_SESSION['_token'] ?>">
		<input type="hidden" class="initial_tags" value="<?= isset($initial_tags) ? e($initial_tags) : '' ?>">
	</div>
	
	<? if ($quantity > $settings['limit']): ?>
		<div class="load-more center" type="search" offset="<?= $settings['offset'] ?>"
		     limit="<?= $settings['limit'] ?>">
			<a class="btn waves-effect waves-light">Загрузить еще<i class="material-icons right">autorenew</i></a>
		</div>
	<? endif; ?>
</div>