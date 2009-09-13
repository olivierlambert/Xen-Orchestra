<?php
// the main config file needed to load settings in etc/xen-orchestra.conf
require_once "inc/config.php";

$vm			= isset ($_GET['vm']) ? $_GET['vm'] : false; 			// vm number
$action		= isset ($_GET['action']) ? $_GET['action'] : false; 		// action to do (see switch)
$target		= isset ($_GET['target']) ? $_GET['target'] : false; 		// target for migration
$domN 		= isset ($_GET['dom0']) ? $_GET['dom0'] : false; 		// dom0 number
$dom0name 	= $domain[$domN]; 		// dom0 name

// the 'main' div, which is dynamic
echo '<div id="main">';
// create object in order to play with SQLite database
$db = sqlite_factory("database/test");

// TO DO : UPDATE OR INSERT, COMPARE OLD AND NEW DATA
$db->query('DROP TABLE dom0');
$db->query('DROP TABLE domU');
//$db->query('DROP TABLE migrated');
$db->query('CREATE TABLE dom0 (id int, object text)');
$db->query('CREATE TABLE domU (vm_name varchar(128), state varchar(128))');

//$db->query('CREATE TABLE migrated (name text)');
/*
foreach ($migrated_array as $name) {
	echo($name['name']);echo '<br/>';
}*/
// DB record inspection and update
// This step allows to compare previous record (Database) and "real" record (RPC to Xend).
// It's necessary because Xen's (xm new) behavior is not as we expected, e.g for Live Migration,
// it always displays migrated VM on the source in a "Halted" state. That's not acceptable, that's
// why we need persitent storage in order to hide migrated VM.

// This limitation in xm new introduces more complexity in this program. Maybe a message on Xen devel
// list is necessary ?

$dbresult = $db->query("SELECT count(*) FROM dom0");
//echo $dom0number = $dbresult->fetchSingle();
/*
// check in Database, if 0 Dom0 recorded, we need to insert all infos from RPC
if ($dom0number = 0) {
	
}
// if there is previous record, we need to compare 
else {
	$diff = count($domain)-$dom0number;
	// there is more recorded Dom0 than RPC said
	// so we need to destroy them
	if ($diff<0) {
		
	}
	// there is more Dom0 than we "expect"
	// so we need to create them
	else if ($diff>0) {
		
	}
	else {
		
	}
	
	// if VM is halted and live migrated, we do NOT update
	
	$dbresult = $db->query("SELECT object FROM dom0");
	$dom0_table = $dbresult->fetchAll();
	foreach ($dom0_table as $val) {
		$dom0 = unserialize($val[0]);
		//$dom0->display_table_all_vm();
	}
}
	
	//$dom0->display_table_all_vm();
	for ($i=0;$i<$dom0->vm_attached_number();$i++) {
		if ($dom0->is_migrated($i) and $dom0->get_state($i)=="Halted") {
			// VM has migrated
			$dom0b = new Dom0($i,$domain[$i],$port[$i],$user[$i],$password[$i]);
			$dom0b->set_migrated($i,true);
			$domobject = sqlite_escape_string(serialize($dom0b));
			$db->query("INSERT INTO dom0 (id,object) VALUES ('$i','$domobject')");
		}
		else {
			
		}
	}
	//echo $dom0->is_migrated(2);* 
* 
* */
$db->query("DELETE FROM domU");
// INSERT OR MAJ RPC
for ($i=0;$i<count($domain);$i++) {
	try {
		// Create objects and put them into Database
		$dom0 = new Dom0($i,$domain[$i],$port[$i],$user[$i],$password[$i]);
		
		//echo $dom0->address;
		// Compare and/or Update/Add/Remove if necessary
		$query = $db->query("SELECT object FROM dom0 WHERE id = $i");
		$number = $query->numRows();
		
		$domobject = sqlite_escape_string(serialize($dom0));
		
		if ($number==1) {
			$db->query("UPDATE dom0 SET object='$domobject' WHERE id='$i'");
		}
		else {
			$db->query("INSERT INTO dom0 (id,object) VALUES ('$i','$domobject')");
		}
		//$dom0db = unserialize($result);
		//if ($dom0->address == $dom0db->address) echo "TOTO!!!!";
		
		//$domobject = sqlite_escape_string(serialize($dom0));
		//$db->query("UPDATE dom0 SET object='$domobject' WHERE id='$i'");
		
		// Save object in a array to re-use it after
		$dom0_table[$i] = serialize($dom0);
	}
	catch (Exception $e) {
		echo '<h3>Connection Error: ',  $e->getMessage(), "</h3><br/>";
	}
	// if we have GET value action "migrate" on corresponding Dom0
	if ($vm !== false && $action=="migrate_vm" and $domN==$i) {
		$dom0->$action($vm,$target,true);
		$vm_name = $dom0->get_vm_name($vm);
	}
	// else if we have a GET other value action on corresponding Dom0
	elseif ($vm !== false && $domN==$i) {
		$dom0->$action($vm);
	}
}


// Now display our page
foreach ($dom0_table as $obj) {
	$dom0 = unserialize($obj);
	$dom0->display_table_all_vm();
}

echo '</div>';
