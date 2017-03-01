<?
$is_activated = &$data['is_activated'];
?>

<div class="container">
	<div class="section">
		<h4 class="center grey-text text-darken-1">
			<? if ($is_activated): ?>
				Учетная запись успешно активирована!
				<br>
				Вы можете войти в систему, используя логин и пароль.
			<? else: ?>
				Ссылка активации недействительна.
			<? endif; ?>
		</h4>
	</div>
</div>