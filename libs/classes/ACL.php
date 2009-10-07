<?php

class ACL
{
	const NONE = 0;

	const READ = 1;

	const WRITE = 2;

	const ADMIN = 3;

	public static function from_string($acl)
	{
		switch ($acl)
		{
			case 'READ':
				return self::READ;
			case 'WRITE':
				return self::WRITE;
			case 'ADMIN':
				return self::ADMIN;
		}
		return self::NONE;
	}

	public static function to_string($acl)
	{
		switch ($acl)
		{
			case self::READ:
				return 'READ';
			case self::WRITE:
				return 'WRITE';
			case self::ADMIN:
				return 'ADMIN';
		}
		return 'NONE';
	}

	private function __construct()
	{}
}
