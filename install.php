<?php

include "inc/header.php";


echo '<p>Database Creation :</p>';


try {
	$db = sqlite_factory("database/test");
	$db->query('DROP TABLE dom0');
	$db->query('CREATE TABLE dom0 (id int, object text)');
	$db->query('CREATE TABLE network (id int, object text)');
	$db->query('CREATE TABLE vif (id int, object text)');
	$db->query('CREATE TABLE vm (id int, object text)');
	$db->query('CREATE TABLE metrics (id int, object text)');
}
catch (Exception $e) {
	echo "Error : ",$e->getMessage(), "<br/>";
	die();
}

echo "Database successfully generated <br/>";
include "inc/footer.php";
