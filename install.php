<?php
require_once dirname (__FILE__) . '/includes/prepend.php';
$cfg = Config::get_instance();

$db = Db::get_instance();


$db->query('DROP TABLE dom0');
$db->query('DROP TABLE domU');
//$db->query('DROP TABLE migrated');
$db->query('CREATE TABLE dom0 (id varchar(128), object text)');
$db->query('CREATE TABLE domU (vm_name varchar(128), state varchar(128))');


echo '<p>Database Creation :</p>';


try {
	//$db->query('DROP TABLE dom0');
	//$db->query('DROP TABLE domU');
	$db->query('CREATE TABLE dom0 (id varchar(128), object text)');
	$db->query('CREATE TABLE domU (vm_name varchar(128), state varchar(128))');
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

include 'includes/footer.php';
