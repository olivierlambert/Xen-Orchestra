<?php
require_once dirname (__FILE__) . '/includes/prepend.php';

$cfg = Config::get_instance();
$db = Db::get_instance();



echo '<h1>Database Creation :</h1>';


try {
	$db->query('DROP TABLE dom0');
	$db->query('DROP TABLE domU');
	$db->query('CREATE TABLE dom0 (id varchar(128), object text)');
	$db->query('CREATE TABLE domU (vm_name varchar(128), state varchar(128), domN int)');
}
catch (Exception $e) {
	echo '<p>Error : ',$e->getMessage().'</p>';
	die();
}

echo '<p>Database successfully generated.</p>';

