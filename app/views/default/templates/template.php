<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0"/>
	<meta name="description" content="PiqTune - это сервис, позволяющий оживить картинку, добавив к ней звук. Здесь вы найдете по-настоящему говорящие картинки!">
	<meta name="keywords" content="картинка,изображение,мем,пост,звук,музыка,смех,юмор,meme,image,picture,sound,fun,lol">
	<meta name="author" content="Dmitry Ibragimov">
	<link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon">
	<title><?= (isset($data['page_title']) ? $data['page_title'] . ' - PiqTune' : 'PiqTune') ?></title>
	
	<!-- CSS  -->
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link href="/css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
	<?= (isset($data['css']) ? $data['css'] : '') ?>
	<link href="/css/style.css" rel="stylesheet">
</head>
<body>
<!-- NAVIGATION BAR  -->
<div class="header navbar-fixed">
	<nav class="white" role="navigation">
		<div class="nav-wrapper container">
			<a id="logo-container" href="/" class="brand-logo grey-text text-darken-3">PiqTune</a>
			<a class="modal-trigger header-icon" title="Сообщить об ошибке" href="#modal-bug"><i class="material-icons right">bug_report</i></a>
			<a class="header-icon search-toggle" title="Поиск"><i class="material-icons right">search</i></a>

			<ul class="right hide-on-med-and-down">
				<li><a href="/posts/random" title="Случайный пост"><i class="material-icons left">shuffle</i>Рандом</a></li>

				<? if ($_SESSION['user']['auth']): ?>
					<li><a href="/posts/create" title="Новый пост"><i class="material-icons left">add</i>Создать</a></li>
					<li>
						<a class="dropdown-button" href="#" data-activates="user_menu" title="Меню пользователя"><i class="material-icons left">person</i><?= $_SESSION['user']['login'] ?><i class='material-icons right'>arrow_drop_down</i></a>
						<!-- Dropdown Structure -->
						<ul id='user_menu' class='dropdown-content'>
							<li><a href="/users/profile">Мои посты</a></li>
							<li><a href="/users/favorites">Избранное</a></li>
							<li class="divider"></li>
							<li><a href="/users/settings">Настройки</a></li>
							<li><a href="/auth/logout" title="Выход"
							       onclick="return confirm('Вы действительно хотите выйти?')">Выход</a></li>
						</ul>
					</li>
				<? else: ?>
					<li><a href="/auth/login" title="Войти"><i class="material-icons left">person_outline</i>Войти</a></li>
				<? endif; ?>

			</ul>
			
			<a href="#" data-activates="nav-mobile" class="button-collapse"><i class="material-icons">menu</i></a>
		</div>
	</nav>
</div>
<ul id="nav-mobile" class="side-nav hide-on-large-only show-on-medium-and-down">
	<? if ($_SESSION['user']['auth']): ?>
		<li><a href="/posts/create" title="Новый пост"><i class="material-icons left">add</i>Новый пост</a></li>
		<li><a href="/posts/random" title="Случайный пост"><i class="material-icons left">shuffle</i>Рандом</a></li>
		<li><a href="/users/profile" title="Мои посты"><i class="material-icons left">person</i><?= $_SESSION['user']['login'] ?></a></li>
		<li><a href="/users/favorites" title="Избранное"><i class="material-icons left">star</i>Избранное</a></li>
		<li><a href="/users/settings" title="Настройки"><i class="material-icons left">settings</i>Настройки</a></li>
		<li><a href="/auth/logout" title="Выход" onclick="return confirm('Вы действительно хотите выйти?')"><i class="material-icons left">exit_to_app</i>Выход</a></li>
	<? else: ?>
		<li><a href="/auth/login" title="Войти"><i class="material-icons left">person_outline</i>Войти</a></li>
		<li><a href="/posts/random" title="Случайный пост"><i class="material-icons left">shuffle</i>Рандом</a></li>
	<? endif; ?>
</ul>

<form method="get" action="/search/results" class="search-panel card z-depth-1">
	<div class="input-field nomargin">
		<input type="hidden" name="v" value="1">
		<input type="text" name="target" placeholder="Поиск">
	</div>
</form>

<!-- CONTENT  -->
<div class="content-wrap">
	<?php include_once Config::get('app').'views/' . $this->template . '/' . $content_view; ?>
</div>

<a id="page-up" class="down"><i class="material-icons">arrow_upward</i></a>

<!-- Modal Structure -->
<div id="modal-bug" class="modal">
	<div class="modal-content">
		<h4>Сообщение об ошибке</h4>
		<div class="input-field col s12">
			<textarea id="textarea1" name="bug_report" class="materialize-textarea" pattern="^.{5,6}$" placeholder="Опишите обнаруженную проблему..."></textarea>
		</div>
	</div>
	<div class="modal-footer">
		<a class="modal-action modal-close waves-effect waves-dark btn-flat ">Закрыть</a>
		<a class="send-report modal-action waves-effect waves-dark btn-flat ">Отправить</a>
	</div>
	<input type="hidden" name="_token" class="_token" value="<?= $_SESSION['_token'] ?>">
</div>

<!-- FOOTER  -->
<footer class="page-footer">
	<div class="container">
		<div class="nomargin">
			<div class="copyright">
				© 2016 <a class="underline nomargin" href="http://vk.com/didimoner" target="_blank"
				          rel="noopener noreferrer">Дмитрий Ибрагимов</a>
			</div>
			
			<div class="links">
				<a class="underline" href="/info/contacts">Контакты</a>
				<a class="underline" href="/info/help">Помощь</a>
				<a class="underline" href="/info">О проекте</a>
			</div>
		</div>
	</div>
</footer>

<!-- Google Analytics -->
<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

	ga('create', 'UA-80669509-1', 'auto');
	ga('send', 'pageview');

</script>
<!-- /Google Analytics -->

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
	(function (d, w, c) {
		(w[c] = w[c] || []).push(function() {
			try {
				w.yaCounter38444760 = new Ya.Metrika({
					id:38444760,
					clickmap:true,
					trackLinks:true,
					accurateTrackBounce:true
				});
			} catch(e) { }
		});

		var n = d.getElementsByTagName("script")[0],
			s = d.createElement("script"),
			f = function () { n.parentNode.insertBefore(s, n); };
		s.type = "text/javascript";
		s.async = true;
		s.src = "https://mc.yandex.ru/metrika/watch.js";

		if (w.opera == "[object Opera]") {
			d.addEventListener("DOMContentLoaded", f, false);
		} else { f(); }
	})(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/38444760" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

<!--  Scripts-->
<script src="/js/jquery-2.2.3.min.js"></script>
<script src="/js/materialize.min.js"></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/clipboard.min.js"></script>
<script src="/js/jquery.transit.min.js"></script>
<?= (isset($data['js']) ? $data['js'] : '') ?>
<script src="/js/common.js"></script>
</body>
</html>