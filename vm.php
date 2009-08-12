<?php
include "inc/header.php";
include "inc/config.php";

//$dbname = "database/vm";


$vm			= $_GET['vm']; 			// vm number
$action		= $_GET['action']; 		// action to do (see switch)
$target		= $_GET['target']; 		// target for migration
$domN 		= $_GET['dom0']; 		// dom0 number
$dom0name 	= $domain[$domN]; 		// dom0 name


$other_domains = array_diff($domain,array($domN => $dom0name));


$dom0 = unserialize($_SESSION["'.$domN.'"]); // get dom0 object

//print_r(($_SESSION['dom0'][0]));
//print_r($domain);
//$other_domain = 
switch ($action) {
	case 1:
	$dom0->pause_vm($vm);
	echo '<meta http-equiv="refresh" content="1;url=index.php" />';
	break;
	case 2:
	$dom0->unpause_vm($vm);
	echo '<meta http-equiv="refresh" content="1;url=index.php" />';
	break;
	case 3:
	$dom0->migrate_vm($vm,$target,"true");
	echo '<meta http-equiv="refresh" content="1;url=index.php" />';
	break;
}


$dom0->display_page_vm($vm,$other_domains);
//print_r($dom0->get_record($vm));

include "inc/footer.php";
