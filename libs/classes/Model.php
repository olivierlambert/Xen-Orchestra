<?php

final class Model
{
	public static function create_user($name, $password, $email, $permission,
		$pw_hashed = false)
	{
		if (!Database::is_enabled())
		{
			return false;
		}

		if (!$pw_hashed)
		{
			$password = md5($password);
		}

		$id = Database::get_instance()->insert_user($name, $password, $email,
			ACL::to_string($permission));
		if ($id === false)
		{
			return false;
		}
		return self::$users[$name] = new User($id, $name, $password, $email,
			$permission);
	}

	public static function delete_user($name)
	{
		if (Database::is_enabled() && Database::get_instance()->delete_user($name))
		{
			unset(self::$users[$name]); // He may have been in the cache.
			return true;
		}
		return false;
	}

	/**
	 * Returns the current user.
	 * If the user is not registered or if the database is disabled, the
	 * returned user is "guest".
	 *
	 * @return The current user.
	 */
	public static function get_current_user()
	{
		if (self::$current_user !== null)
		{
			return self::$current_user;
		}

		if (isset($_SESSION['user']))
		{
			self::$current_user = self::get_user($_SESSION['user']);

			if (self::$current_user !== false)
			{
				// The user has been successfully retrieved.
				return self::$current_user;
			}

			// An error occured, unregisters the user and falls back to "guest".
			self::unregister_current_user();
		}
		return self::$current_user = self::get_user('guest');
	}

	/**
	 * Returns the dom0 which has the id $id in the database or null.
	 *
	 * @param string $id             The identifier of the dom0.
	 * @param boolean $force_refresh The results are cached, pass true to ignore
	 *                               it.
	 *
	 * @return The dom0 if present, otherwise false.
	 */
	public static function get_dom0($id, $force_refresh = false)
	{
		if ($force_refresh)
		{
			$config = Config::get_instance();
			if (isset($config->$id))
			{
				$entries = $config->$id;

				list($address, $port) = explode (':', $id, 2);
				return self::$dom0s[$id] = new Dom0(
					$address,
					$port,
					isset($entries['username']) ? $entries['username'] : 'none',
					isset($entries['password']) ? $entries['password'] : 'none'
				);
			}
			// There is no such dom0.
			return self::$dom0s[$id] = false; // It may have existed.
		}

		if (isset(self::$dom0s, $id))
		{
			return (self::$dom0s[$id]);
		}
		if (self::$all_dom0s_retrieved)
		{
			return false;
		}

		// Not found but may exist, recall this method with $force_refresh set
		// to true.
		return self::get_dom0($id, true);
	}

	/**
	 * Returns all the dom0s present in the database.
	 *
	 * @param boolean $force_refresh The results are cached, pass true to ignore
	 *                               it.
	 *
	 * @return An array containing all the dom0 (can be empty).
	 */
	public static function get_dom0s($force_refresh = false)
	{
		if ($force_refresh || !self::$all_dom0s_retrieved)
		{
			$config = Config::get_instance();
			$dom0s = array(); // Necessary for the force refresh.
			self::$all_dom0s_retrieved = true;
			foreach ($config as $entry => $entries)
			{
				// Checks if this entry is a dom0.
				if (is_array($entries) && (strpos($entry, ':') !== false))
				{
					list($address, $port) = explode (':', $entry, 2);

					self::$dom0s[$entry] = new Dom0(
						$address,
						$port,
						isset($entries['username']) ? $entries['username'] : 'none',
						isset($entries['password']) ? $entries['password'] : 'none'
					);
				}
			}
		}
		return self::$dom0s;
	}

	/**
	 * Returns a reference to an array containing all the domUs of a dom0.
	 */
	public static function &get_domUs(Dom0 $dom0)
	{
		self::$domUs_by_dom0s[$dom0->id] = array();
		$xids = $dom0->rpc_query('VM.get_all');
		foreach ($xids as $xid)
		{
			// The domU Domain-0 is special, do not insert
			// it in the domUs array.
			if ($xid === '00000000-0000-0000-0000-000000000000')
			{
				continue;
			}

			$domU = new DomU($xid, $dom0);
			if (($domU->state === 'Halted')
				&& self::is_running_domU_named($domU->name))
			{
				continue;
			}

			if (($domU->state === 'Running') || ($domU->state === 'Paused'))
			{
				if (isset(self::$domUs_by_names[$domU->name]))
				{
					foreach (self::$domUs_by_names[$domU->name] as $dom0_id => $domU_)
					{
						if ($domU_->state === 'Halted')
						{
							unset (self::$domUs_by_dom0s[$dom0_id][$domU->name]);
							unset (self::$domUs_by_names[$domU->name][$dom0_id]);
						}
					}
				}
			}

			self::$domUs_by_dom0s[$dom0->id][$domU->name] = $domU;
			if (!isset(self::$domUs_by_names[$domU->name]))
			{
				self::$domUs_by_names[$domU->name] = array($dom0->id => $domU);
			}
			else
			{
				self::$domUs_by_names[$domU->name][$dom0->id] = $domU;
			}
		}

		return self::$domUs_by_dom0s[$dom0->id];
	}

	/**
	 * Returns the user who has the name $name if he exists, otherwise returns
	 * false.
	 *
	 * If $password is not null, the user's password will also be checked, if
	 * not correct, the function will return false.
	 *
	 * @param string $name          The user's name.
	 * @param string|null $password The user's password.
	 *
	 * @return The user or false.
	 */
	public static function get_user($name, $password = null, $pw_hashed = false)
	{
		if (!isset(self::$users[$name]))
		{
			if (self::$all_users_retrieved)
			{
				// The user is not in the cache and we know we have all the
				// users in it, so we know he does not exist.
				return false;
			}
			if (Database::is_enabled())
			{
				$user = Database::get_instance()->get_user('name', $name);

				if ($user === false)
				{
					if ($name !== 'guest')
					{
						return self::$users[$name] = false;
					}

					// There must be a "guest" user in the database.
					self::create_user('guest', '', '', ACL::NONE);
				}
				else
				{
					self::$users[$name] = new User($user['id'], $user['name'],
						$user['password'], $user['email'],
						ACL::from_string($user['permission']));
				}
			}
			else
			{
				if ($name !== 'guest')
				{
					// The database is disabled, only "guest" is available.
					return false;
				}
				self::$users['guest'] = self::get_default_guest();
			}
		}
		if (self::$users[$name] === false) // Already checked, not here.
		{
			return false;
		}
		if ($password !== null)
		{
			if (!$pw_hashed)
			{
				$password = md5($password);
			}
			if ($password !== self::$users[$name]->password)
			{
				return false;
			}
		}
		return self::$users[$name];
	}

	public static function get_users()
	{
		if (self::$all_users_retrieved)
		{
			return self::$users;
		}

		if (!Database::is_enabled())
		{
			self::$all_users_retrieved = true;
			return self::$users = array(
				'guest' => self::get_default_guest()
			);
		}

		self::$users = array();
		$stmt = Database::get_instance()->query('SELECT "id", "name", '
			. '"password", "email", "permission" FROM "users"');

		// What should we do if $stmt equals false?


		while (($r = $stmt->fetch(PDO::FETCH_NUM)) !== false)
		{
			$r = array_map('rtrim', $r);
			self::$users[$r[1]] = new User($r[0], $r[1], $r[2], $r[3],
				ACL::from_string($r[4]));
		}

		if (!isset(self::$users['guest'])) // "guest" must always exist.
		{
			self::$all_users_retrieved = false;
			self::get_user('guest'); // Ensures "guest" exi
		}

		self::$all_users_retrieved = true;

		return self::$users;
	}

	public static function get_user_acls(User $user)
	{
		if (!Database::is_enabled())
		{
			return array();
		}

		$db = Database::get_instance();
		$stmt = $db->prepare('SELECT "dom0_id", "domU_name", "permission" '
			. 'FROM "acls" WHERE "user_id" = :user_id');

		if (!$stmt->execute(array(':user_id' => $user->id)))
		{
			return array(); // The request failed.
		}

		$acls = array();
		while (($r = $stmt->fetch(PDO::FETCH_NUM)) !== false)
		{
			$r[0] = rtrim($r[0]);
			if (!isset($acls[$r[0]]))
			{
				$acls[$r[0]] = array();
			}

			if ($r[1] === null) // For the whole dom0.
			{
				$acls[$r[0]]['Domain-0'] = $r[2];
			}
			else
			{
				$acls[$r[0]][rtrim($r[1])] = ACL::from_string($r[2]);
			}
		}
		return $acls;
	}

	/**
	 * Registers the current user.
	 * If an user named $name with the password $password exists, this user is
	 * registered as the current user, which means that each action will be done
	 * with his permissions.
	 *
	 * @param string      $name
	 * @param string|null $password
	 * @param bool        $pw_hashed
	 *
	 * @return The user if the registration was a success, otherwise false.
	 */
	public static function register_current_user($name, $password = null,
		$pw_hashed = false)
	{
		$user = self::get_user($name, $password, $pw_hashed);
		if ($user === false)
		{
			return false;
		}
		$_SESSION['user'] = $name;
		return self::$current_user = $user;
	}

	/**
	 * Unregisters the current user.
	 */
	public static function unregister_current_user()
	{
		self::$current_user = null;
		unset($_SESSION['user']);
	}

	/**
	 * Updates the database to match the given user, registers him if necessary.
	 *
	 * @param User $u The user to update.
	 *
	 * @return Whether the update was successful.
	 */
	public static function update_user(User $u)
	{
		return (Database::is_enabled()
			&& Database::get_instance()->update_user($u->id, $u->name,
				$u->password, $u->email, ACL::to_string($u->permission)));
	}

	/**
	 * The current user.
	 *
	 * @var User
	 */
	private static $current_user = null;

	/**
	 * To avoid unecessary checking and object creation, all dom0s are stored in
	 * this array.
	 *
	 * @var array
	 */
	private static $dom0s = array();

	/**
	 * This flag equals true if all the dom0s are already retrieved, otherwise
	 * it equals false.
	 *
	 * @var boolean
	 */
	private static $all_dom0s_retrieved = false;

	/**
	 * This array contains all the domUs: dom0_ids => name => domU.
	 *
	 * @var array
	 */
	private static $domUs_by_dom0s = array();

	/**
	 * This array contains all the domUs:  names => dom0_id => domU.
	 *
	 * @var array
	 */
	private static $domUs_by_names = array();

	/**
	 * This array contains all the users already fetched from the database.
	 *
	 * @var array
	 */
	private static $users = array();


	/**
	 * This flag equals true if all the users are already retrieved, otherwise
	 * it equals false.
	 *
	 * @var boolean
	 */
	private static $all_users_retrieved = false;

	/**
	 * Returns the default "guest" user.
	 * The default "guest" user is the user "guest" when the database is
	 * disabled.
	 *
	 * @param $permission
	 *
	 * @return The default "guest" user.
	 */
	private static function get_default_guest($permission = null)
	{
		if ($permission === null)
		{
			if (isset($cfg->global['default_guest_permission']))
			{
				$permission = ACL::from_string($cfg->global['default_guest_permission']);
			}
			else
			{
				$permission = ACL::NONE;
			}
		}
		return new User(-1, 'guest', '', '', $permission);
	}

	/**
	 * Checks if there is a running domU with the name $name among the known
	 * domUs.
	 *
	 * @param string name
	 *
	 * @return True if there is at least one, otherwise false.
	 */
	private static function is_running_domU_named($name)
	{
		if (isset(self::$domUs_by_names[$name]))
		{
			foreach (self::$domUs_by_names[$name] as $domU)
			{
				if ($domU->state === 'Running')
				{
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * This class cannot be instanciated.
	 */
	private function __construct()
	{}
}
