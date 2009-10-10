<?php
// The session is used for users management.
session_name('XenOrchestra');
session_start();

define ('ROOT_DIR', dirname (dirname (__FILE__)));

set_include_path (ROOT_DIR . PATH_SEPARATOR . get_include_path ());

// autoload classes
function __autoload($class_name)
{
	require_once 'libs/' . $class_name . '.php';
}
