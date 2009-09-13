<?php

require_once "inc/header.php";



// main is display for AJAX call
include "main.php";

// TODO : remove or do something else with these ugly buttons

echo '
<div class="buttons">
<button id="btnReload" type="submit" class="positive">
<img src="img/arrow_refresh.png" alt=""/>
</button>
</div>';
include "inc/footer.php";

?>
