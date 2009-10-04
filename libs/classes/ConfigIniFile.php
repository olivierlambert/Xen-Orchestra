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

		if (!isset($entries['global']))
		{
			return;
		}

		$this->entries = array(
			'global' => $entries['global']
		);
		unset ($entries['global']);
		$this->entries['dom0s'] = $entries;
	}

	protected function write()
	{
		// Update config.
	}

	private $file;
}
