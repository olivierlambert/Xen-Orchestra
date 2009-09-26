<?php
require_once dirname (__FILE__) . '/../includes/prepend.php';

$dom0 = new Dom0 ('dls.homelinux.net', '9363', 'toto', 'otot');

var_dump ($dom0->password);
