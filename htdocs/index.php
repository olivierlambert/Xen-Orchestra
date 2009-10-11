<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';
require 'includes/header.php';
?>
<script type="text/javascript">
refresh_time = <?php echo (Config::get_instance()->global['refresh'] * 1000) ?>;
data = "<?php
ob_start();
require 'display_dom0.php';
echo addcslashes(ob_get_clean(), '"');
?>".evalJSON();
</script>
<div id="main">
	<div id="widget_col_0"></div>
	<div id="widget_col_1"></div>
</div>
