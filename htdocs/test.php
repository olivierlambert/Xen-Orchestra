<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';
require 'includes/header.php';
 ?>
<div id="page">
<div id="widget_col_0"></div>
<div id="widget_col_1"></div>
<script type="text/javascript">var portal = new Xilinus.Portal("#page div");</script>

<?php

$row = 0;
$i = 0;
foreach (Dom0::get_all() as $dom0) {
	$row = $i % 2;
	echo '<script type="text/javascript">display_dom0(portal,'.$row.',"'.$dom0.'");</script>';
	$i++;
}

?>
</div>


