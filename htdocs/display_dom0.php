<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';

$id = isset($_POST['id']) ? $_POST['id'] : false;
$id = 'dls.homelinux.net:9364';

list($address, $port) = explode(':', $id, 2);

$dom0 = Dom0::get($id);


$nb = $dom0->vm_attached_number();
$content = $dom0->display_frame_all_vm();


$title = $address.' ('.$nb.' vm)';

echo json_encode(array('title' => $title, 'content' => $content));
/*

$json = array (
	'title' => ,
	'domUs' => array ()
);

foreach ($dom0 as $domU) {
	$json['domUs'] = array (
		'name' =>
		'status' =>
	);
}

echo json_encode ($json);
*/
