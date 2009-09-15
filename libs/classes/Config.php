<?php

abstract class Config implements IteratorAggregate {
	public static function get($name) {
		return self::get_instance()->$name;
	}

	public static function get_instance() {
		if (self::$instance == null)
		{
			self::$instance = new ConfigIniFile(ROOT_DIR . '/xen-orchestra.conf');
		}
		return self::$instance;
	}

	public static function set($name, $value) {
		self::get_instance()->$name = $value;
	}

	public function __destruct() {
		if ($this->changed) {
			$this->write();
		}
	}

	public function __get($name) {
		if ($this->entries == null)
		{
			$this->read();
		}
		return $this->entries[$name];
	}

	public function __isset($name) {
		if ($this->entries == null)
		{
			$this->read();
		}
		return isset($this->entries[$name]);
	}

	public function __set($name, $value) {
		if ($this->entries == null)
		{
			$this->read();
		}
		if (isset($this->entries[$name]))
		{
			$this->entries[$name] = $value;
			$changed = true;
		}
	}

	public function getIterator() {
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
