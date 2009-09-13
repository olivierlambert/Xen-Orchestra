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
<div id="vm">

<?php


require_once "inc/config.php";

$vm			= isset ($_GET['vm']) ? $_GET['vm'] : false; 			// vm number
$action		= isset ($_GET['action']) ? $_GET['action'] : false; 		// action to do (see switch)
$target		= isset ($_GET['target']) ? $_GET['target'] : false; 		// target for migration
$domN 		= isset ($_GET['dom0']) ? $_GET['dom0'] : false; 		// dom0 number


$db = sqlite_factory("database/test");

/*
$tmp = $db->query("SELECT count(*) FROM dom0 WHERE id = $domN");
$test = $tmp->fetchSingle();
print_r($test);
*/
$result = $db->query("SELECT object FROM dom0 WHERE id = $domN");
$object = $result->fetchSingle();

$dom0 = unserialize($object);


$other_domains = $domain;
unset ($other_domains[$domN]);

$dom0->display_page_vm($vm,$other_domains);



if (!$action=="") {
	
	if (!$target=="") {
		$dom0->$action($vm,$target,true);
		$vm_name = $dom0->get_vm_name($vm);
		echo '<script type="text/javascript">close_reload();</script>';
	}
	else {
	$dom0->$action($vm);
		echo '<script type="text/javascript">close_reload();</script>';
	}
}


echo '</div>';
include "inc/footer.php";
