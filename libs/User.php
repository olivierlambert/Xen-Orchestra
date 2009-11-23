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
class User
{
	public function __construct($id, $name, $password, $email, $permission)
	{
		$this->id = $id;
		$this->name = $name;
		$this->password = $password;
		$this->email = $email;
		$this->permission = $permission;
	}

	public function __get($name)
	{
		switch ($name)
		{
			case 'id':
			case 'name':
			case 'password':
			case 'email':
			case 'permission':
				return $this->$name;
			case 'acls':
				if ($this->_acls === null)
				{
					$this->_acls = Model::get_user_acls($this);
				}
				return $this->_acls;
		}
		if (isset ($this->$name))
		{
			throw new Exception('Property ' . __CLASS__ . '::' . $name . ' is not readable');
		}
		else
		{
			throw new Exception('No such property: ' . __CLASS__ . '::' . $name);
		}
	}

	public function __set($name, $value)
	{
		switch ($name)
		{
		}
		if (isset ($this->$name))
		{
			throw new Exception('Property ' . __CLASS__ . '::' . $name . ' is not writable');
		}
		else
		{
			throw new Exception('No such property: ' . __CLASS__ . '::' . $name);
		}
	}

	public function can($permission, $dom0_id = null, $domU_name = null)
	{
		return ($permission <= $this->get_permissions_for($dom0_id, $domU_name));
	}

	public function get_permissions_for($dom0_id = null, $domU_name = null)
	{
		$acls = $this->acls;
		if (($domU_name !== null) && isset($acls[$dom0_id][$domU_name]))
		{
			return $acls[$dom0_id][$domU_name];
		}
		if (($dom0_id !== null) && isset($acls[$dom0_id]['Domain-0']))
		{
			return $acls[$dom0_id]['Domain-0'];
		}
		return $this->permission;
	}

	private $id;

	private $name;

	/**
	 * The user's password, hashed with md5.
	 */
	private $password;

	private $email;

	private $permission;

	private $_acls = null; // aka extended permission.
}
