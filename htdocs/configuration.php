<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';
$cfg = Config::get_instance();
$db = Db::get_instance();
require 'includes/header.php';
echo '<div id="main">
<h3>Avalaible API method on Dom0\'s (debug purpose)</h3>';

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
	

	$dom0_array = $dom0->host_record();
	echo '<p>';
	foreach ($dom0_array as $dom0) {
		echo $dom0.' <br/>';
	}
	echo '</p>';

}
	catch (Exception $e) {
	echo '<h3>Connection Error: ',  $e->getMessage(), '</h3>';
	//echo '</div>';
	//include 'includes/footer.php';
	//exit;
	}
}
?>
</div>
