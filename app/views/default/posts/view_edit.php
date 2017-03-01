<?
$sp       = &$data['savepost'];
$messages = &$data['messages'];
$content  = &$data['content'];


$post_emotion = isset($sp['emotion']) ? $sp['emotion'] : $content['emotion'];

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
			<h3 class="header grey-text text-darken-1">Изменение поста</h3>
			
			<form method="post" enctype="multipart/form-data" class="creator-form">
				<input type="hidden" name="_token" class="_token" value="<?= $_SESSION['_token'] ?>">
				<input type="hidden" name="id" value="value="<?= $content['id'] ?>"">
				
				<div class="input-field col s12">
					<input type="text" name="title" id="title" class="title validate" pattern="^.{5,64}$"
					       value="<?= isset($sp['title']) ? e($sp['title']) : e($content['title']) ?>"
					       placeholder="Заголовок" required>
					<label for="title" data-error="Ошибка: допустимая длина от 5 до 64 символов">&nbsp</label>
				</div>
				
				<div class="file-field input-field">
					<div class="btn">
						<span>Файл</span>
						<input type="file" name="image" accept="image/jpeg,image/png">
					</div>
					<div class="file-path-wrapper">
						<input class="file-path image validate" type="text" id="image" pattern="^.+\.(jpg|jpeg|png|JPG|JPEG|PNG)$"
						       placeholder="Изображение (jpg, png)">
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
						       placeholder="Звук (до 15 сек.)">
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
					<input type="hidden" class="initial_tags"
					       value="<?= isset($sp['tags']) ? e($sp['tags']) : e($content['tags']) ?>">
				</div>
				
				<textarea class="tags" name="tags"></textarea>
				
				Рекомендую прочитать <a href="/info/help#17" target="_blank">несколько советов и полезных ссылок для создания поста</a>.
				
				<br><br>
				<button class="btn form_btn waves-effect waves-light" type="submit">Сохранить
					<i class="material-icons right">send</i>
				</button>
				<a href="/posts/<?= $content['id'] ?>" class="btn form_btn">Отмена</a>
			</form>
		</div>
	</div>
</div>
