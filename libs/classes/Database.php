<?php

final class Database extends PDO
{
	const ID = 0;

	const NAME = 1;

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
		$stmt = $this->prepare('DELETE FROM "users" WHERE "name" = :name');

		$r = $stmt->execute(array(
			':name' => $name
		));
		return ($r && ($stmt->rowCount() === 1));
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

		$stmt = $this->prepare('SELECT "id", "name", "password", "email", '
			. '"permission" FROM "users" WHERE "' . $by . '" = :value');

		if (!$stmt->execute(array(':value' => $value))
			|| (($r = $stmt->fetch(PDO::FETCH_ASSOC)) === false))
		{
			return false;
		}
		return array_map('rtrim', $r);
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
		$stmt = $this->prepare('INSERT INTO "users" '
			. '("name", "password", "email", "permission") VALUES '
			. '(:name, :password, :email, :permission)');

		$r = $stmt->execute(array(
			':name' => $name,
			':password' => $password,
			':email' => $email,
			':permission' => $permission,
		));

		if (!$r || ($stmt->rowCount() == 0)) // The query failed.
		{
			return false;
		}

		return $this->lastInsertId('users_id_seq'); // Will only work with PostgreSQL.
	}

	private static $enabled = null;

	private static $instance = null;
}
