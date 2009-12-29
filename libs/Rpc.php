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
class Rpc {

	public $id;
	public $address;
	public $port;
	public $params;
	public $method;
	public $response;

	public function __construct($address, $port ,$id)
	{
		$this->address = $address;
		$this->port = $port;
		$this->id = $id;
	}

	public function send($method, $params = null)
	{
		$this->method = $method;
		if ($params === null)
		{
			$this->params = $this->id;
		}
		else
		{
			$this->params = array_merge((array)$this->id, (array)$params);
		}

		$request = xmlrpc_encode_request($method, $this->params);
		$context = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'header' => 'Content-Type: text/xml',
				'content' => $request
			)
		));
		$data = file_get_contents('http://'.$this->address.':'.$this->port, false, $context);
		$response = xmlrpc_decode($data);

		if ($response['Status'] === 'Success')
		{
			return $this->response = $response['Value'];
		}
		elseif ($response['Status'] === 'Failure')
		{
			return $this->response = $response['ErrorDescription'];
		}
	}

	public function __toString()
	{
		return $this->response;
	}
}
