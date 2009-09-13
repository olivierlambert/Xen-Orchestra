<?php
require dirname (__FILE__) . '/../includes/prepend.php';

// Return the refresh time for main
echo Config::get_instance()->global['refresh'];

