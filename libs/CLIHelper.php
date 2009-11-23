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

/**
 * TODO: write doc
 */
class CLIHelper
{
	public function prompt($prompt)
	{
		$this->write($prompt);
		return $this->read();
	}

	public function read($file_handle = STDIN)
	{
		return rtrim(fgets($file_handle), PHP_EOL);
	}

	public function write($message, $file_handle = STDOUT)
	{
		fwrite($file_handle, $message);
	}

	public function writeln($message = '', $file_handle = STDOUT)
	{
		$this->write($message . PHP_EOL);
	}
}

