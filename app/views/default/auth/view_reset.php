<?
$sp       = &$data['savepost'];
$messages = &$data['messages'];

// показываю ошибки, если они есть
if ($messages) echo create_messagebox($messages);
?>

<div class="container">
	<div class="section">
		<div class="form-wrap authorization card">
			<h4 class="header grey-text text-darken-1">Сброс пароля</h4>
			
			<form method="post" class="registration-form">
				<input type="hidden" name="_token" value="<?= $_SESSION['_token'] ?>">
				
				<input type="hidden" name="email" value="<?= $data['email'] ?>">
				<input type="hidden" name="code" value="<?= $data['code'] ?>">
				
				<div class="input-field col s12">
					<input type="password" class="password validate" name="password" id="password"
					       pattern="^[a-zA-Z0-9@!?]{6,20}$" value="<?= isset($sp['password']) ? $sp['password'] : '' ?>"
					       required>
					<label for="password" data-error="Ошибка: от 6 до 20 цифр и лат. символов + '!?@'.">Новый
						пароль</label>
				</div>
				<div class="input-field col s12">
					<input type="password" class="re-password c_validate" name="re_password" id="re_password"
					       value="<?= isset($sp['re_password']) ? $sp['re_password'] : '' ?>" required>
					<label for="re_password" data-error="Ошибка: Пароли не совпадают.">Повтор пароля</label>
				</div>
				
				<button class="btn form_btn waves-effect waves-light" type="submit">Сохранить
					<i class="material-icons right">send</i>
				</button>
			</form>
		</div>
	</div>
</div>