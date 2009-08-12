<?php

// CONFIG PARSER 

$conf_file = parse_ini_file("etc/xen-orchestra.conf");

$domain = explode(",",$conf_file['domain']);
$user = explode(",",$conf_file['user']);
$password = explode(",",$conf_file['password']);
$port = explode(",",$conf_file['port']);
$dbname = "database/vm";
