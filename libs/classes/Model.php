<?php

class Model
{
	/**
	 * Returns the dom0 which has the id $id in the database or null.
	 *
	 * @param string $id             The identifier of the dom0.
	 * @param boolean $force_refresh The results are cached, pass true to ignore
	 *                               it.
	 *
	 * @return The dom0 if present, otherwise null.
	 */
	public static function get_dom0($id, $force_refresh = false)
	{
		static $dom0s = array ();
		if ($force_refresh || !isset ($dom0s[$id]))
		{
			$result = Db::get_instance()->query('SELECT object FROM dom0 WHERE '
				. 'id = "' . sqlite_escape_string($id) . '"');
			if (sqlite_num_rows ($result) === 0)
			{
				return null;
			}
			$dom0s[$id] = unserialize($result->fetchSingle());
		}
		return $dom0s[$id]; // We are sure, it is correctly defined.
	}

	/**
	 * Returns all the dom0 presents in the database.
	 *
	 * @param boolean $force_refresh The results are cached, pass true to ignore
	 *                               it.
	 *
	 * @return An array containing all the dom0 (can be empty).
	 */
	public static function get_dom0s($force_refresh = false)
	{
		static $dom0s = null;
		if ($force_refresh || ($dom0s === null))
		{
			$result = Db::get_instance()->query('SELECT object FROM dom0');
			if ($result === false)
			{
				return array();
			}
			$dom0s = array();
			foreach ($result->fetchAll() as $dom0)
			{
				$dom0 = unserialize($dom0[0]);
				$dom0->detect_migrated();
				$dom0s[$dom0->id] = $dom0;
			}
		}
		return $dom0s;
	}

	/**
	 * TODO: write doc.
	 */
	public static function set_dom0(Dom0 $dom0)
	{
		Db::get_instance()->query('INSERT OR REPLACE INTO dom0 (id, object) '
			. 'VALUES ("' . sqlite_escape_string ($dom0->id) . '","'
			. sqlite_escape_string (serialize ($dom0)) . '")');
	}
	
	public static function get_domU($name, $state, $id)
	{
		//static $dom0s = array ();

		$result = Db::get_instance()->query('SELECT name FROM domU WHERE '
		. 'id = "' . sqlite_escape_string($id) . '" AND '
		. 'state = "' . $state . '" AND '
		. 'name = "' . $name . '"');
		
		$compare = $result->fetchSingle();
		//var_dump($compare);
		
	if ($compare === false)
	{
		return null;
	}
		
		return $name; 
	}
	private function __construct()
	{}
}
