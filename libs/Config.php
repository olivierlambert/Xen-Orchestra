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

abstract class Config implements IteratorAggregate
{
	public static function get($name, $default = null)
	{
		$instance = self::get_instance;
		if (isset($instance->$name))
		{
			return $instance->$name;
		}
		return $default;
	}

	public static function get_instance()
	{
		if (self::$instance == null)
		{
			self::$instance = new ConfigIniFile(ROOT_DIR . '/xen-orchestra.conf');
		}
		return self::$instance;
	}

	public static function set($name, $value)
	{
		self::get_instance()->$name = $value;
	}

	public function __destruct()
	{
		if ($this->changed) {
			$this->write();
		}
	}

	public function __get($name)
	{
		if ($this->entries == null)
		{
			$this->read();
		}
		return $this->entries[$name];
	}

	public function __isset($name)
	{
		if ($this->entries == null)
		{
			$this->read();
		}
		return isset($this->entries[$name]);
	}

	public function __set($name, $value)
	{
		if ($this->entries == null)
		{
			$this->read();
		}
		if (isset($this->entries[$name]))
		{
			$this->entries[$name] = $value;
			$this->changed = true;
		}
	}

	public function getIterator()
	{
		if ($this->entries == null)
		{
			$this->read();
		}
		return new ArrayIterator($this->entries);
	}

	abstract protected function read();

	abstract protected function write();

	protected $entries = null;

	private static $instance = null;

	private $changed = false;
}
