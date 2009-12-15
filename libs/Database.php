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
final class Database extends PDO
{
	public static function get_instance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function is_enabled()
	{
		if (self::$enabled === null)
		{
			$cfg = Config::get_instance();
			self::$enabled = !(isset($cfg->global['disable_database'])
				&& $cfg->global['disable_database']);
		}
		return self::$enabled;
	}

	public function __construct()
	{
		if (!self::is_enabled())
		{
			throw new Exception('The database is disabled.');
		}

		$config = Config::get_instance();

		if (!isset($config->database))
		{
			throw new Exception('No database entry in the configuration.');
		}
		$config = $config->database;

		if (!isset($config['dsn']))
		{
			throw new Exception('No database.dsn entry in the configuration.');
		}
		parent::__construct(
			$config['dsn'],
			isset($config['username']) ? $config['username'] : null,
			isset($config['password']) ? $config['password'] : null
		);
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Deletes an user from the database.
	 *
	 * @param string $name
	 */
	public function delete_user($name)
	{
		$stmt = $this->prepare('DELETE FROM users WHERE name = ?');

		return ($stmt->execute(array($name)) && ($stmt->rowCount() === 1));
	}

	/**
	 * Gets an user from the database.
	 *
	 * @param string $by    Can be either "id" or "name".
	 * @param mixed  $value
	 *
	 * @return An associative array or false.
	 */
	public function get_user($by, $value)
	{
		if (($by !== 'id') && ($by !== 'name'))
		{
			return false; // Incorrect query.
		}

		$stmt = $this->prepare('SELECT id, name, password, email, '
			. 'permission FROM users WHERE ' . $by . ' = ?');

		if (!$stmt->execute(array($value))
			|| (($r = $stmt->fetch(PDO::FETCH_NUM)) === false))
		{
			return false;
		}
		return new User($r[0], rtrim($r[1]), $r[2], rtrim($r[3]),
			ACL::from_string($r[4]));
	}

	public function get_users()
	{
		$stmt = $this->query('SELECT id, name, '
			. 'password, email, permission FROM users');

		if ($stmt === false)
		{
			return false;
		}

		$users = array();
		while (($r = $stmt->fetch(PDO::FETCH_NUM)) !== false)
		{
			$r[1] = rtrim($r[1]);
			$users[$r[1]] = new User($r[0], $r[1], $r[2], rtrim($r[3]),
				ACL::from_string($r[4]));
		}

		return $users;
	}

	/**
	 * Inserts a new user into the database.
	 *
	 * @param string $name
	 * @param string $password
	 * @param string $email
	 * @param string $permission
	 *
	 * @return The identifier (integer) of the new user if the insertion was
	 *         successful, otherwise false.
	 */
	public function insert_user($name, $password, $email, $permission)
	{
		$stmt = $this->prepare('INSERT INTO users '
			. '(name, password, email, permission) VALUES '
			. '(?, ?, ?, ?)');

		$r = $stmt->execute(array($name, $password, $email, $permission));

		if (!$r || ($stmt->rowCount() == 0)) // The query failed.
		{
			return false;
		}

		return $this->lastInsertId('users_id_seq');
	}

	public function update_user(User $u)
	{
		$stmt = $this->prepare('UPDATE users SET name = ?, password = ?, '
			.'email = ?, permission = ? WHERE id = ?');

		$r = $stmt->execute(array($u->name, $u->password, $u->email,
			ACL::to_string($u->permission), $u->id));

		return ($r && ($stmt->rowCount() === 1));
	}

	private static $enabled = null;

	private static $instance = null;
}

