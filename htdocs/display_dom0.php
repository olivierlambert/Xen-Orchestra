<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';

$result = array();

foreach (Model::get_dom0s() as $dom0)
{
	$result[] = $dom0->table_dom0();
}
echo json_encode($result);
