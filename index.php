<?php

include "inc/header.php";
include "inc/config.php";

/*
$user = "toto";
$pass = "toto";
$dom01 = "xena1";
$dom02 = "xenb1";
$port = "9363";
$dbname = "database/vm";
*/


for ($i=0;$i<count($domain);$i++)
{
	$dom0 = new Dom0($i,$domain[$i],$port[$i],$user[$i],$password[$i]);
	$dom0->display_table_all_vm();
	$_SESSION["'.$i.'"] = serialize($dom0);
}

include "inc/footer.php";
/*
$xena1 = new Dom0($dom01,$port,$user,$pass);

$_SESSION['dom0'] = serialize($xena1);
include "inc/footer.php";*/
?>
