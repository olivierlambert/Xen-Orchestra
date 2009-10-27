<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';

function error($message)
{
	echo json_encode(array('error' => $message));
	exit;
}

$action = isset ($_GET['action']) ? $_GET['action'] : false;

$dom0_id = isset ($_GET['dom0']) ? $_GET['dom0'] : false;
$domU_id = isset ($_GET['domU']) ? $_GET['domU'] : false;

$u = Model::get_current_user();
if (!$u->can(ACL::READ, $dom0_id, $domU_id))
{
	error('Access denied');
}

$dom0 = Model::get_dom0($dom0_id);
if ($dom0 === false)
{
	error('No such dom0.');
}

$domU = $dom0->getDomU($domU_id);
if ($domU === false)
{
	error('No such domU.');
}

if ($action)
{
	if (!$u->can(ACL::WRITE, $dom0_id, $domU_id))
	{
		error('Access denied');
	}
	switch ($action)
	{
		case 'delete':
		case 'pause':
		case 'play':
		case 'stop':
			$domU->$action();
	}
	
	$domU->refresh();
}

// In the future, the user may request info about more than one domU.
$data = array(
	'dom0s' => array(
		$dom0_id => array(
			'domUs' => array(
				$domU_id => $domU->get_all_infos()
			)
		)
	),
	
	// Tells JavaScript that this list is exhaustive and it has to
	// keep all dom0s/domUs not listed.
	'exhaustive' => false
);

echo json_encode($data);
