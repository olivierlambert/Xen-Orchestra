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
