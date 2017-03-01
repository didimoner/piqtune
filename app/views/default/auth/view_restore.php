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
			<h4 class="header grey-text text-darken-1">Восстановление</h4>
			
			<form method="post" class="registration-form">
				<input type="hidden" name="_token" value="<?= $_SESSION['_token'] ?>">
				
				<div class="input-field col s12">
					<input type="email" name="email" id="email" value="<?= isset($sp['email']) ? $sp['email'] : '' ?>"
					       required>
					<label for="email">E-mail</label>
				</div>

				<div class="g-recaptcha" data-sitekey="6LeR1SQTAAAAAMd1jmWAA54J44zi81insAaYlDwF"></div>
				
				<button class="btn form_btn waves-effect waves-light" type="submit">Продолжить
					<i class="material-icons right">send</i>
				</button>
				<a href="/auth/login" class="btn form_btn waves-effect waves-light">Назад</a>
			</form>
		</div>
	</div>
</div>