<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Xen Orchestra</title>

	<script type="text/javascript" src="scripts/prototype.js"></script>
	<script type="text/javascript" src="scripts/windows_js_1.3/javascripts/window.js"></script>
	<script type="text/javascript" src="scripts/windows_js_1.3/javascripts/tooltip.js"></script>
	<script type="text/javascript" src="scripts/scriptaculous.js"></script>
	<script type="text/javascript" src="scripts/portal.js"></script>
	<script type="text/javascript" src="scripts/livepipe.js"></script>
	<script type="text/javascript" src="scripts/tabs.js"></script>

	<script type="text/javascript" src="scripts/md5.js"></script>

	<script type="text/javascript" src="scripts/xo.js"></script>
	<script type="text/javascript" src="scripts/xorender.js"></script>
	<script type="text/javascript">
	//<![CDATA[
		document.observe('dom:loaded', function ()
		{
			init();
			register_info('<?php echo addcslashes($json, '\'') ?>'.evalJSON());
		});
	//]]>
	</script>

	<link rel="stylesheet" media="screen" type="text/css" href="styles/stylewhite.css" />
	<link rel="stylesheet" media="screen" type="text/css" href="styles/alphacube.css" />
	<link rel="stylesheet" media="screen" type="text/css" href="styles/spread.css" />
	<link rel="stylesheet" media="screen" type="text/css" href="styles/default.css" />
</head>

<body>
	<h1>
		<a href=".">XenOrchestra</a>
	</h1>

	<ul class="menu1">
		<li><a href="index.php"><b><img src="img/house.png" alt=""/>Home</b></a></li>
		<li><a href="index.php"><b><img src="img/server.png" alt=""/>servers</b></a></li>
		<li><a href="configuration.php"><b><img src="img/conf.png" alt=""/>Configuration</b></a></li>
		<li><a href="index.php"><b><img src="img/vm.png" alt=""/>VM management</b></a></li>
		<li><a href="index.php"><b><img src="img/user.png" alt=""/>User management</b></a></li>
	<div id="login"></div></ul>
	<div id="main">
		<div id="widget_col_0"></div>
		<div id="widget_col_1"></div>
	</div>
</body>
</html>

