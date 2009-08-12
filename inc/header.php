<?
session_start();
echo $header='
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>Xen Orchestra</title>
	<meta http-equiv="content-type" content="text/html;charset=iso-8859-1" />
	<link rel="stylesheet" media="screen" type="text/css" href="style.css" />
</head>

<body>
<h1><a href=".">XenOrchestra</a></h1>
';

include "inc/menu.php";

echo '
<div id="main">';

// autoload classes in the classes directory
function __autoload($class_name) {
	require_once 'classes/'.$class_name . '.php';
}
?>
