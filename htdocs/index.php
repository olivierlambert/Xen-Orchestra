<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';
require 'includes/header.php';
echo '<div id="main">';
?>

<div id="widget_col_0"></div>
<div id="widget_col_1"></div>


<?php
$cfg = Config::get_instance();
$db = Db::get_instance();

foreach ($cfg->domains as $id => $domain)
{
	list ($address, $port) = explode (':', $id);
	$user = isset($domain['user']) ? $domain['user'] : 'none';
	$password = isset($domain['pass']) ? $domain['pass'] : 'none';

	try
	{
		// Create objects and put them into Database
		$dom0 = new Dom0($address, $port, $user, $password);
		Model::set_dom0($dom0);
	}
	catch (Exception $e)
	{
		echo '<h3>Connection Error: ',  $e->getMessage(), '</h3>';
	}
}
?>
</div>
