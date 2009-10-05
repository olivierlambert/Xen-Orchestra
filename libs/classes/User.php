<?php

class User
{
	public const NONE = 0;

	public const READ = 1;

	public const WRITE = 2;

	public const ADMIN = 3;

	public function __construct($id, $name, $mail, $permissions)
	{
		$this->id = $id;
		$this->name = $name;
		$this->mail = $mail;
		$this->permissions = $permissions;
	}

	public function __get ($name)
	{
		switch ($name)
		{
			case 'id':
			case 'name':
			case 'mail':
			case 'permissions':
				return $this->$name;
			case 'acls':
				if ($this->_acls === null)
				{
					$this->_acls = &Model::get_user_acls($this)
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

	public function __set ($name, $value)
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
		return $this->permissions;
	}

	private $id;

	private $name;

	private $mail;

	private $permissions;

	private $_acls = null; // aka extended permissions.
}
