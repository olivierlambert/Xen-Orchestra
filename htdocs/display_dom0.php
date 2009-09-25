<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';
$db = Db::get_instance();

$id = ($_POST['id']) ? $_POST['id'] : false;
//$id = "xena1:9363";
list($address, $port) = explode(':', $id, 2);

$query = $db->query('SELECT object FROM dom0 WHERE id = "'.sqlite_escape_string($id).'"');

$result = $query->fetchSingle();
$dom0 = unserialize($result);

$nb = $dom0->vm_attached_number();
$content = $dom0->display_frame_all_vm();


$title = $address.' ('.$nb.' vm)';

echo json_encode(array(title => $title, content => $content));
