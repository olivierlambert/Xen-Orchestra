<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';
require 'includes/header.php';
 ?>
<div id="page">
<div id="widget_col_0"></div>
<div id="widget_col_1"></div>

<?php
$db = Db::get_instance();
// now that the database if filled, we display
$query = $db->query('SELECT object FROM dom0');
$result = $query->fetchAll();

$row = 0;
$i = 0;
foreach ($result as $dom0) {
	$dom0 = unserialize($dom0[0]);
	$dom0->detect_migrated();
	//$dom0->display_table_all_vm();
	$row = $i % 2;
	$i++;
	
}
	echo '<script>display_dom0($dom0);</script>';
?>
</div>


