<?
$sp       = &$data['savepost'];
$messages = &$data['messages'];

$post_emotion = isset($sp['emotion']) ? $sp['emotion'] : 'happy';

$emotions_list = [
	'happy'   => 'Радость',
	'lol'     => 'Смех',
	'annoyed' => 'Раздражение',
	'sad'     => 'Печаль',
	'wtf'     => 'Озадаченность',
	'wow'     => 'Удивление',
	'scared'  => 'Испуг',
	'cry'     => 'Плач',
	'angry'   => 'Злость'
];
?>

<?
// показываю ошибки, если они есть
if ($messages) echo create_messagebox($messages);
?>

<div class="container">
	<div class="section">
		<div class="form-wrap card">
			<h4 class="header grey-text text-darken-1">Новый пост</h4>
			
			<form method="post" enctype="multipart/form-data" class="creator-form">
				
				<input type="hidden" name="_token" class="_token" value="<?= $_SESSION['_token'] ?>">
				<input type="hidden" name="author" value="<?= $_SESSION['user']['login'] ?>">
				
				<div class="input-field col s12">
					<input type="text" name="title" id="title" class="title validate" pattern="^.{5,64}$"
					       value="<?= isset($sp['title']) ? $sp['title'] : '' ?>" required>
					<label for="title" data-error="Ошибка: допустимая длина от 5 до 64 символов">Заголовок</label>
				</div>
				
				<div class="file-field input-field">
					<div class="btn">
						<span>Файл</span>
						<input type="file" name="image" accept="image/jpeg,image/png">
					</div>
					<div class="file-path-wrapper">
						<input class="file-path image validate" type="text" id="image" pattern="^.+\.(jpg|jpeg|png|JPG|JPEG|PNG)$"
						       placeholder="Изображение (jpg, png)" required>
						<label for="image" data-error="Ошибка: неподдерживаемый формат">&nbsp</label>
					</div>
				</div>
				
				<div class="file-field input-field">
					<div class="btn">
						<span>Файл</span>
						<input type="file" name="audio" accept="audio/*">
					</div>
					<div class="file-path-wrapper">
						<input class="file-path audio validate" type="text" id="audio" pattern="^.+\.(mp3|aac|ogg|3gp|mp4|wav|m4a|amr|MP3|AAC|OGG|3GP|MP4|WAV|M4A|AMR)$"
						       placeholder="Звук (до 15 сек.)" required>
						<label for="audio" data-error="Ошибка: неподдерживаемый формат">&nbsp</label>
					</div>
				</div>
				
				<div class="emotions">
					<label>Выберите эмоцию, которая характеризует ваш пост:</label>
					<ul>
						<? foreach ($emotions_list as $name => $desc): ?>
							<?
							$is_active = '';
							if ($post_emotion == $name) $is_active = ' class="active"';
							?>
							<li<?= $is_active ?>><img class="<?= $name ?>" src="/images/emotions/small/<?= $name ?>.png"
							                          alt="<?= $name ?>" title="<?= $desc ?>"></li>
						<? endforeach; ?>
					</ul>
					<input type="hidden" name="emotion" value="happy">
				</div>
				
				<textarea class="tags" name="tags" placeholder="От 2 до 8 тегов через запятую"></textarea>
				
				Рекомендую прочитать <a href="/info/help#17" target="_blank">несколько советов и полезных ссылок для создания поста</a>.
				
				<br><br>
				<button class="btn form_btn waves-effect waves-light" type="submit">Отправить
					<i class="material-icons right">send</i>
				</button>
				<a href="<?= $_SERVER['HTTP_REFERER'] ?>" class="btn form_btn waves-effect waves-light">Отмена</a>
				
				<input type="hidden" class="initial_tags" value="<?= isset($sp['tags']) ? e($sp['tags']) : '' ?>">
			</form>
		</div>
	</div>
</div>