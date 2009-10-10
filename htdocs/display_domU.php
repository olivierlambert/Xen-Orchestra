<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';

function error($message)
{
	echo json_encode (array('error' => $message));
	exit;
}

$id 		= isset ($_GET['id']) ? $_GET['id'] : false; 		// dom0 number
$name		= isset ($_GET['name']) ? $_GET['name'] : false; 			// vm name

$action		= isset ($_GET['action']) ? $_GET['action'] : false; 		// action to do (see switch)
$target		= isset ($_GET['target']) ? $_GET['target'] : false; 		// target for migration

$dom0 = Model::get_dom0($id);
if ($dom0 === false)
{
	error('No such dom0.');
}
$domU = $dom0->getDomU($name);
if ($domU === false)
{
	error('No such domU.');
}

echo json_encode($domU->get_all_infos());
