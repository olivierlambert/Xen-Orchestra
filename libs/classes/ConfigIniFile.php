<?php

class ConfigIniFile extends Config
{
	public function __construct($file)
	{
		$this->file = $file;
	}


	protected function read()
	{
		$entries = parse_ini_file($this->file, true);

		if ($entries === false)
		{
			throw new Exception('Cannot read the configuration file ('
				. $this->file . ')');
		}

		$this->entries = $entries;
	}

	protected function write()
	{
		// Update config.
	}

	private $file;
}
