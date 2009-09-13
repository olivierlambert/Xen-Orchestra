<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Xen Orchestra</title>
	
	<script type="text/javascript" src="lib/prototype.js"></script>
	<script type="text/javascript" src="lib/functions.js"></script>
	<script type="text/javascript" src="lib/windows_js_1.3/javascripts/window.js"></script>
	<link rel="stylesheet" media="screen" type="text/css" href="styles/vm.css" />
	<meta http-equiv="content-type" content="text/html;charset=iso-8859-1" />
</head>
<body>

<div id="migrate">



<?php

require_once "inc/config.php";

$dom0 = unserialize($object);
$vm			= $_GET['vm']; 			// vm number
$action		= $_GET['action']; 		// action to do (see switch)
$target		= $_GET['target']; 		// target for migration
$domN 		= $_GET['dom0']; 		// dom0 number
$dom0name 	= $domain[$domN]; 		// dom0 name

$db = sqlite_factory("database/test");

/*
$tmp = $db->query("SELECT count(*) FROM dom0 WHERE id = $domN");
$test = $tmp->fetchSingle();
print_r($test);
*/
$result = $db->query("SELECT object FROM dom0 WHERE id = $domN");
$object = $result->fetchSingle();
$dom0 = unserialize($object);

$other_domains = array_diff($domain,array($domN => $dom0name));

$dom0->display_page_migrate($i,$other_domains);

echo '</div>';

include "inc/footer.php";
