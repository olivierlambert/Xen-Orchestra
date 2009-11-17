#!/usr/bin/php
<?php
require_once dirname (__FILE__) . '/../libs/prepend.php';

$c = new CLIHelper();

if (!Database::is_enabled())
{
	$c->writeln('The database is disabled.');
	exit;
}

$username = trim($c->prompt('Username: '));

$password = trim($c->prompt('Password: '));

$email = trim($c->prompt('Email: '));

$permission = trim($c->prompt('Permission (NONE, read, write, admin): '));
$permission = ACL::from_string(strtoupper($permission));

$u = Model::create_user($username, $password, $email, $permission);
if ($u !== false)
{
	$c->writeln('User created with identifier ' . $u->id . '.');
}
else
{
	$c->writeln('User creation failed.', STDERR);
}

