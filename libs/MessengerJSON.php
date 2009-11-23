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

class MessengerJSON
{
	const ERR_NONE = 0;

	const ERR_UNKNOWN = 1;

	public function __construct($auto_send = false)
	{
		$this->auto_send = $auto_send;
	}

	public function __destruct()
	{
		if ($this->auto_send)
		{
			$this->send();
		}
	}

	public function &__get($name)
	{
		return $this->data[$name];
	}

	public function __isset($name)
	{
		isset($this->data[$name]);
	}

	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	public function __unset($name)
	{
		unset($this->data[$name]);
	}

	public function error($message, $code = self::ERR_UNKNOWN)
	{
		$this->error_code = $code;
		$this->error_msg = $message;
	}

	public function get()
	{
		if ($this->error_code !== 0)
		{
			$data = $this->data + array(
				'error_code' => $this->error_code,
				'error_message' => $this->error_msg
			);
		}
		else
		{
			$data = $this->data + array(
				'error_code' => 0
			);
		}
		$json = json_encode($data);
		if ($json === '[]') // The root element must be an object.
		{
			return '{}';
		}
		return $json;
	}

	public function send()
	{
		$this->auto_send = false;
		echo $this->get();
	}

	private $auto_send;

	private $data = array();

	private $error_msg = 'No error';

	private $error_code = self::ERR_NONE;
}

