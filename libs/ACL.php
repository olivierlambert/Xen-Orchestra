<?php
/**
 * This file is a part of Xen Orchesrta.
 *
 * Xen Orchestra is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Xen Orchestra is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Xen Orchestra. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Xen Orchestra
 * @license http://www.gnu.org/licenses/gpl-3.0-standalone.html GPLv3
 **/
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
