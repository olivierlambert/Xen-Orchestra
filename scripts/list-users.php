#!/usr/bin/php
<?php
require_once dirname (__FILE__) . '/../libs/prepend.php';

if (!Database::is_enabled())
{
	echo 'The database is disabled.', PHP_EOL;
}

$users = Model::get_users();
echo count($users), ' user(s).', PHP_EOL;
foreach ($users as $user)
{
	echo $user->id, "\t", $user->name, "\t", $user->email, "\t",
		ACL::to_string($user->permission), PHP_EOL;
}
