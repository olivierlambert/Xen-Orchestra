<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';

$name		= isset ($_GET['name']) ? $_GET['name'] : false; 			// vm name
$state		= isset ($_GET['state']) ? $_GET['state'] : false; 		// state of vm
$action		= isset ($_GET['action']) ? $_GET['action'] : false; 		// action to do (see switch)
$target		= isset ($_GET['target']) ? $_GET['target'] : false; 		// target for migration
$id 		= isset ($_GET['id']) ? $_GET['id'] : false; 		// dom0 number

$result = array();

$domU = Model::get_dom0s($name,$state,$id);

//$dom0->table_dom0();

//echo json_encode($result);

