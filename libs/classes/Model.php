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
		if ($force_refresh || !isset (self::$dom0s[$id]))
		{
			$config = Config::get_instance();
			if (isset($config->dom0s[$id]))
			{
				list ($address, $port) = explode (':', $id);
				$user = isset($domain['user']) ? $domain['user'] : 'none';
				$password = isset($domain['pass']) ? $domain['pass'] : 'none';

				self::$dom0s[$id] = new Dom0($address, $port, $user, $password);
			}
		}
		return self::$dom0s[$id]; // We are sure, it is correctly defined.
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
		if ($force_refresh || (self::$dom0s === null))
		{
			$config = Config::get_instance();
			$dom0s = array();
			if (isset($config->dom0s))
			{
				foreach ($config->dom0s as $id => $dom0)
				{
					list ($address, $port) = explode (':', $id);
					$user = isset($domain['user']) ? $domain['user'] : 'none';
					$password = isset($domain['pass']) ? $domain['pass'] : 'none';

					self::$dom0s[$id] = new Dom0($address, $port, $user, $password);
				}
			}
		}
		return self::$dom0s;
	}

	public static function get_domU($name, $state, $id)
	{
		//static $dom0s = array ();
		$result = Db::get_instance()->query('SELECT name FROM domU WHERE '
		. 'id = "' . sqlite_escape_string($id) . '" AND '
		. 'state = "' . $state . '" AND '
		. 'name = "' . $name . '"');

		$compare = $result->fetchSingle();

		if ($compare === false)
		{
			return null;
		}

		return $name;
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

	private static $dom0s = null;

	/**
	 * This array contains all the domUs: dom0_ids => name => domU.
	 *
	 * @var array
	 */
	private static $domUs_by_dom0s = array();

	/**
	 * This array contains all the domUs:  names => dom0_id => domU.
	 *
	 * TODO: optimize get_domUs in inserting in this array only halted domUs.
	 *
	 * @var array
	 */
	private static $domUs_by_names = array();

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

	private function __construct()
	{}
}
