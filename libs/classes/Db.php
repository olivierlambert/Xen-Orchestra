<?php

// DB CLASS
// create an SQLite Database
class Db {

	public static function get_instance() {
		if (self::$instance == null) {
			self::$instance = sqlite_factory(ROOT_DIR . '/database/database.sqlite');
		}
		return self::$instance;
	}

	private static $instance = null;
}
