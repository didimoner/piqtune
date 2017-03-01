<?
$sp       = &$data['savepost'];
$messages = &$data['messages'];

// показываю ошибки, если они есть
if ($messages) echo create_messagebox($messages);
?>

<div class="container">
	<div class="section">
		<div class="form-wrap registration card">
			<h4 class="header grey-text text-darken-1">Регистрация</h4>
			
			<form method="post" class="registration-form">
				<input type="hidden" name="_token" value="<?= $_SESSION['_token'] ?>">
				
				<div class="input-field col s12">
					<input type="text" name="login" id="login" class="login validate" pattern="^[a-zA-Z0-9_]{5,16}$"
					       value="<?= isset($sp['login']) ? $sp['login'] : '' ?>" required>
					<label for="login" data-error="Ошибка: от 5 до 16 цифр и лат. символов.">Логин</label>
				</div>
				
				<div class="input-field col s12">
					<input type="password" class="password validate" name="password" id="password"
					       pattern="^[a-zA-Z0-9@!?]{6,20}$" value="<?= isset($sp['password']) ? $sp['password'] : '' ?>"
					       required>
					<label for="password" data-error="Ошибка: от 6 до 20 цифр и лат. символов + '!?@'.">Пароль</label>
				</div>
				
				<div class="input-field col s12">
					<input type="email" name="email" id="email" class="email validate"
					       pattern="^[-a-z0-9!#$%&'*+/=?^_`{|}~]+(?:\.[-a-z0-9!#$%&'*+/=?^_`{|}~]+)*@(?:[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])?\.)*(?:aero|arpa|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|[a-z][a-z])$"
					       value="<?= isset($sp['email']) ? $sp['email'] : '' ?>" required>
					<label for="email" data-error="Ошибка: некорректный email.">E-mail</label>
				</div>
				
				<div class="g-recaptcha" data-sitekey="6LeR1SQTAAAAAMd1jmWAA54J44zi81insAaYlDwF"></div>
				
				<button class="btn form_btn waves-effect waves-light" type="submit">Отправить
					<i class="material-icons right">send</i>
				</button>
				<a href="/auth/login" class="btn form_btn waves-effect waves-light">Отмена</a>
			</form>
		</div>
	</div>
</div>