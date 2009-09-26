<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';
$cfg = Config::get_instance();

$vm			= isset ($_GET['vm']) ? $_GET['vm'] : false; 			// vm number
$action		= isset ($_GET['action']) ? $_GET['action'] : false; 		// action to do (see switch)
$target		= isset ($_GET['target']) ? $_GET['target'] : false; 		// target for migration
$domN 		= isset ($_GET['dom0']) ? $_GET['dom0'] : false; 		// dom0 number

$db = Db::get_instance();

foreach ($cfg->domains as $id => $domain)
{
	list ($address, $port) = explode (':', $id);
	$user = isset($domain['user']) ? $domain['user'] : 'none';
	$password = isset($domain['pass']) ? $domain['pass'] : 'none';

	try
	{
		// Create objects and put them into Database
		$dom0 = new Dom0($address, $port, $user, $password);
		Model::set_dom0($dom0);
	}
	catch (Exception $e)
	{
		echo '<h3>Connection Error: ',  $e->getMessage(), '</h3>';
	}

	if (($vm !== false) && ($domN == $id))
	{
		switch ($action)
		{
			case 'migrate_vm':
				$dom0->migrate_vm($vm, $target, true);
				$vm_name = $dom0->get_vm_name($vm);
				break;
			case 'destroy_vm':
			case 'shutdown_vm':
			case 'start_vm':
			case 'pause_vm':
			case 'unpause_vm':
				$dom0->$action($vm);
		}
	}
}

foreach (Model::get_dom0s() as $dom0)
{
	$dom0->display_table_all_vm();
}

