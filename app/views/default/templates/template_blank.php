<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="author" content="Dmitry Ibragimov">
	<title><?php echo(isset($data['page_title']) ? $data['page_title'] . ' - piqtune' : 'piqtune') ?></title>
	
	<!-- CSS  -->
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link href="/css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
	
	<?php echo(isset($data['css']) ? $data['css'] : '') ?>
</head>
<body>
<?php include_once Config::get('app').'views/' . $this->template . '/' . $content_view; ?>

<!--  Scripts-->
<script src="/js/jquery-2.2.3.min.js"></script>
<?php echo(isset($data['js']) ? $data['js'] : '') ?>
</body>
</html>