<?php

include "inc/header.php";


echo '<p>Database Creation :</p>';


try {
	$db = new Db("vm");
}
catch (Exception $e) {
	echo "Error : ",$e->getMessage(), "<br/>";
	die();
}

echo "Database successfully generated <br/>";
include "inc/footer.php";
