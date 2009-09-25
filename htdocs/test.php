<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';
require 'includes/header.php';
 ?>
<div id="page">
<div id="widget_col_0"></div>
<div id="widget_col_1"></div>
<script>var portal = new Xilinus.Portal("#page div");</script>

<?php
$db = Db::get_instance();
// now that the database if filled, we display
$query = $db->query('SELECT object FROM dom0');
$result = $query->fetchAll();
foreach ($result as $obj) {
	$dom0 = unserialize($obj[0]);
	$dom0->detect_migrated();
	//$dom0->display_table_all_vm();
	$row = $i % 2;
	echo '<script>display_dom0(portal,'.$row.',"'.$dom0.'");</script>';
	$i++;
	
}
	
?>
</div>


