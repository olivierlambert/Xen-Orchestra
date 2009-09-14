<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';
$cfg = Config::get_instance();

$vm			= isset ($_GET['vm']) ? $_GET['vm'] : false; 			// vm number
$action		= isset ($_GET['action']) ? $_GET['action'] : false; 		// action to do (see switch)
$target		= isset ($_GET['target']) ? $_GET['target'] : false; 		// target for migration
$domN 		= isset ($_GET['dom0']) ? $_GET['dom0'] : false; 		// dom0 number

// the 'main' div, which is dynamic
// create object in order to play with SQLite database
$db = Db::get_instance();

// TO DO : UPDATE OR INSERT, COMPARE OLD AND NEW DATA
/*
$db->query('DROP TABLE dom0');
$db->query('DROP TABLE domU');
//$db->query('DROP TABLE migrated');
$db->query('CREATE TABLE dom0 (id varchar(128), object text)');
$db->query('CREATE TABLE domU (vm_name varchar(128), state varchar(128))');
*/

$dbresult = $db->query("SELECT count(*) FROM dom0");

$db->query("DELETE FROM domU");

foreach ($cfg->domains as $id => $domain) {
	list($address, $port) = explode(':', $id, 2);
	$user = isset($domain['user']) ? $domain['user'] : 'none';
	$password = isset($domain['password']) ? $domain['password'] : 'none';

	try {
		// Create objects and put them into Database
		$dom0 = new Dom0($id, $address, $port, $user, $password);
		
		// Compare and/or Update/Add/Remove if necessary
		$query = $db->query('SELECT object FROM dom0 WHERE id = "'.sqlite_escape_string($id).'"');
		$number = $query->numRows();
		
		$domobject = sqlite_escape_string(serialize($dom0));
		
		if ($number==1) {
			$db->query('UPDATE dom0 SET object="'.$domobject
				.'" WHERE id = "'.sqlite_escape_string($id).'"');
		}
		else {
			$db->query('INSERT INTO dom0 (id, object) VALUES ("'
				.$id.'","'.$domobject.'")');
		}
		
		// Save object in a array to re-use it after
		$dom0_table[$id] = $dom0;
	}
	catch (Exception $e) {
		echo '<h3>Connection Error: ',  $e->getMessage(), "</h3>";
		//echo '</div>';
		//include 'includes/footer.php';
		//exit;
	}
	// if we have GET value action "migrate" on corresponding Dom0
	if ($vm !== false && $action=="migrate_vm" && $domN==$id) {
		$dom0->migrate_vm($vm, $target, true);
		$vm_name = $dom0->get_vm_name($vm);
	}
	// else if we have a GET other value action on corresponding Dom0
	elseif ($vm !== false && $domN==$id) {
		$dom0->$action($vm);
	}
}

// Now display our page
foreach ($dom0_table as $dom0) {
	$dom0->display_table_all_vm();
}
