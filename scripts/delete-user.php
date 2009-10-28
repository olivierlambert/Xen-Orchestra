#!/usr/bin/php
<?php
require_once dirname (__FILE__) . '/../libs/prepend.php';

if (!Database::is_enabled())
{
	echo 'The database is disabled.', PHP_EOL;
	exit;
}

$stdin = fopen('php://stdin', 'r');

echo 'Username: ';
$username = trim(fgets($stdin));

if (Model::delete_user($username) !== false)
{
	echo 'User deleted.', PHP_EOL;
}
else
{
	echo 'User deletion failed.', PHP_EOL;
}
