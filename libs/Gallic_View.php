<?php
/**
 * This file is a part of Gallic.
 *
 * Gallic is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Gallic is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Gallic. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Gallic
 * @license http://www.gnu.org/licenses/gpl-3.0-standalone.html GPLv3
 **/


/**
 * TODO: write help.
 **/
class Gallic_View implements Countable, IteratorAggregate
{
	/**
	 * TODO: write help.
	 **/
	public function __construct ($_template)
	{
		$this->setTemplate ($_template);
	}

	/**
	 * TODO: write help.
	 **/
	public function __get ($name)
	{
		return $this->_entries[$name];
	}

	/**
	 * TODO: write help.
	 **/
	public function __isset ($name)
	{
		return array_key_exists ($name, $this->_entries);
	}

	/**
	 * TODO: write help.
	 **/
	public function __set ($name, $value)
	{
		if (empty ($name) || $name[0] === '_')
		{
			throw new Gallic_Exception ();
		}
		$this->_entries[$name] = $value;
	}

	/**
	 * TODO: write help.
	 **/
	public function __unset ($name)
	{
		unset ($this->_entries[$name]);
	}

	/**
	 * TODO: write help.
	 **/
	public function clear ()
	{
		$this->_entries = array ();
	}

	/**
	 * TODO: write help.
	 **/
	public function count ()
	{
		return count ($this->_entries);
	}

	/**
	 * TODO: write help.
	 **/
	public function getIterator ()
	{
		return new ArrayIterator ($this->_entries);
	}

	public function getTemplate ()
	{
		return $this->_template;
	}

	public function render ()
	{
		extract ($this->_entries);
		include $this->_template;
	}

	public function setTemplate ($template)
	{
		$this->_template = $template;
	}

	private $_entries = array ();

	private $_template;
}


