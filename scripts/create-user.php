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

echo 'Password: ';
$password = trim(fgets($stdin));

echo 'Email: ';
$email = trim(fgets($stdin));

echo 'Permission (NONE, read, write, admin): ';
$permission = ACL::from_string(strtoupper(trim(fgets($stdin))));

$u = Model::create_user($username, $password, $email, $permission);
if ($u !== false)
{
	echo 'User created with identifier ', $u->id, '.', PHP_EOL;
}
else
{
	echo 'User creation failed.', PHP_EOL;
}
