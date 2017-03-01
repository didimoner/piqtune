<?
$similar_posts = &$data['similar']['posts']['similar'];
$random_posts  = &$data['similar']['posts']['random'];

$images_path = 'images/pics/';
// ищу в папке нужную картинку
$files = scandir(Config::get('www') . $images_path);
?>

<div class="similar-wrap center">
	<? if ($similar_posts): ?>
		<p class="header-text">Похожие</p>
		
		<? foreach ($similar_posts as $key => $row): ?>
			<div class="post-preview row">
				<a href="/posts/<?= $row['id'] ?>" class="grey-text text-darken-3">
					<div class="col s12">
						<img class="image" src="/
						<? foreach ($files as $value)
							if (substr_count($value, $row['id']) > 0) {
								echo $images_path . $value;
								break;
							}
						?>">
						<p class="title"><?= $row['title'] ?></p>
						<p class="rating grey-text"><?= declination($row['rating'], 'звезда', 'звезды', 'звезд') ?></p>
					</div>
				</a>
			</div>
		<? endforeach; ?>
	<? endif; ?>
	
	<? if ($random_posts): ?>
		<p class="header-text">Случайные</p>
		
		<? foreach ($random_posts as $key => $row): ?>
			<div class="post-preview row">
				<a href="/posts/<?= $row['id'] ?>" class="grey-text text-darken-3">
					<div class="col s12">
						<img class="image" src="/
						<? foreach ($files as $value)
							if (substr_count($value, $row['id']) > 0) {
								echo $images_path . $value;
								break;
							}
						?>">
						<p class="title"><?= $row['title'] ?></p>
						<p class="rating grey-text"><?= declination($row['rating'], 'звезда', 'звезды', 'звезд') ?></p>
					</div>
				</a>
			</div>
		<? endforeach; ?>
	<? endif; ?>

</div>
