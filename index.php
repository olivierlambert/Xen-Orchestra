<?php

include "inc/header.php";
include "inc/config.php";


for ($i=0;$i<count($domain);$i++)
{
	$dom0 = new Dom0($i,$domain[$i],$port[$i],$user[$i],$password[$i]);
	$dom0->display_table_all_vm();
	$_SESSION["'.$i.'"] = serialize($dom0);
}


include "inc/footer.php";
?>
