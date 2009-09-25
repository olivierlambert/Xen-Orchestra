<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';
$dom0	= isset ($_GET['dom0']) ? $_GET['dom0'] : false;
echo $dom0;
//echo json_encode(array('title' => 'Dom0', 'content' => 'Lorem Ipsum Dolor'));
