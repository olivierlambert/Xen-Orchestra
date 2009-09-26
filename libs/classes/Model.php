<?php

class Model {
	public static function get_dom0($id, $force_refresh = false) {
		static $dom0s = array ();
		if ($force_refresh || !isset ($dom0s[$id]))
		{
			// Tries to get it.
			$db = Db::get_instance();
			$query = $db->query('SELECT object FROM dom0 WHERE id = "'.sqlite_escape_string($id).'"');
			$result = $query->fetchSingle();
			$dom0s[$id] = unserialize($result);
			// TODO VERIF CAS ERREUR
			if (!isset ($dom0s[$id]))
			{
				return null;
			}
		}
		return $dom0s[$id]; // We are sure, it is correctly defined.
	}

	public static function get_dom0s($force_refresh = false) {
		static $dom0s = null;
		if ($force_refresh || ($dom0s === null))
		{
			$dom0s = array();
			$db = Db::get_instance();
			// now that the database if filled, we display
			$query = $db->query('SELECT object FROM dom0');
			$result = $query->fetchAll();

			foreach ($result as $dom0) {
				$dom0 = unserialize($dom0[0]);
				$dom0->detect_migrated();
				$dom0s[] = $dom0;
			}
		}
		return $dom0;
	}

	public static function insert_dom0(Dom0 $dom0) {
		$db = Db::getInstance();
		$db->query('INSERT INTO dom0 (id, object) VALUES ("'
			. sqlite_escape_string ($id) . '","'
			. sqlite_escape_string (serialize ($dom0))
			. '")');
	}

	public function update_dom0(Dom0 $dom0) {
		$db = Db::getInstance();
		$db->query('UPDATE dom0 SET object="'
			. sqlite_escape_string (serialize ($dom0))
			. '" WHERE id = "'
			. sqlite_escape_string ($dom0->id) . '"');
	}



	private function __construct()
	{}
}
