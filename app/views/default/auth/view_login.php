<?
$sp       = &$data['savepost'];
$messages = &$data['messages'];
$content  = &$data['content'];

// показываю ошибки, если они есть
if ($messages) echo create_messagebox($messages);
?>

<div class="container">
	<div class="section">
		<div class="form-wrap authorization card">
			<h4 class="header grey-text text-darken-1">Авторизация</h4>
			
			<form method="post" class="registration-form">
				<input type="hidden" name="_token" value="<?= $_SESSION['_token'] ?>">
				
				<div class="input-field col s12">
					<input type="text" name="login" id="login" value="<?= isset($sp['login']) ? $sp['login'] : '' ?>"
					       required>
					<label for="login">Логин</label>
				</div>
				
				<div class="input-field col s12">
					<input type="password" name="password" id="password"
					       value="<?= isset($sp['password']) ? $sp['password'] : '' ?>" required>
					<label for="password">Пароль</label>
				</div>
				
				<div class="col s12">
					<input type="checkbox" class="filled-in" id="filled-in-box" name="remember_me">
					<label for="filled-in-box">Запомнить меня</label>
				</div>
				<br>
				
				<?= isset($content['block_message']) ? $content['block_message'] . '<br>' : '' ?>
				
				<button
					class="btn form_btn waves-effect waves-light <?= isset($content['is_disabled']) ? $content['is_disabled'] : '' ?>"
					type="submit" <?= isset($content['is_disabled']) ? $content['is_disabled'] : '' ?>>Войти
					<i class="material-icons right">send</i>
				</button>
				<a href="/auth/register" class="btn form_btn waves-effect waves-light">Создать аккаунт</a>
				<a href="/auth/restore" class="btn_restore">Забыли пароль?</a>
			</form>
		</div>
	</div>
</div>