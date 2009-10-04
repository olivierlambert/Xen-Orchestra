<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';
require 'includes/header.php';
?>
<div id="main"
<h3>Available API method on Dom0's (debug purpose)</h3>
<?php
foreach (Model::get_dom0s() as $dom0)
{
	$dom0_array = $dom0->host_record();
	echo '<h4>'.$dom0->id.'</h4>';
	echo '<p>';
	foreach ($dom0_array as $dom0) {
		echo $dom0.'  &nbsp|&nbsp  ';
	}
	echo '</p>';
}
?>
</div>
