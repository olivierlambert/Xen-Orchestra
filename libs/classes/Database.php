<?php

final class Database
{
	public static function get_instance()
	{
		if (self::$instance === null)
		{
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
			self::$instance = new PDO(
				$config['dsn'],
				isset($config['username']) ? $config['username'] : null,
				isset($config['password']) ? $config['password'] : null
			);
			self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return self::$instance;
	}

	private static $instance = null;

	private function __construct()
	{}

	private function __clone()
	{}
}
