<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';
$cfg = Config::get_instance();
$db = Db::get_instance();
require 'includes/header.php';
echo '<div id="main">
<h3>Available API method on Dom0\'s (debug purpose)</h3>';

foreach (Model::get_dom0s() as $dom0)
{
	$dom0_array = $dom0->host_record();
	echo '<h4>'.$dom0->id.'</h4>';
	/*$var = Model::get_dom0("xena1:9363");
	var_dump ($var);*/
	echo '<p>';
	foreach ($dom0_array as $dom0) {
		echo $dom0.'  &nbsp|&nbsp  ';
	}
	echo '</p>';
}
?>
</div>
