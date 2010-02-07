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
class Dom0
{
	/**
	 * Create a new Dom0 object.
	 *
	 * @param address  ...
	 * @param port     ...
	 * @param username ...
	 * @param password ...
	 *
	 * @TODO Write proper doc, check arguments.
	 */
	public function __construct($address, $port, $username, $password)
	{
		$this->id = $address . ':' . $port;
		$this->address = $address;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;

		$this->connect();

		$this->xid = $this->rpc_query('host.get_all');
		$this->id_metrics = $this->rpc_query('host_metrics.get_all');
		$this->id_metrics = $this->id_metrics[0];
		$this->get_cpus_infos();

		$this->domUs = &Model::get_domUs($this);
	}

	public function __destruct()
	{
		$this->rpc_query('session.logout');
	}

	public function __get ($name)
	{
		switch ($name)
		{
			case 'address':
			case 'id':
			case 'port':
			case 'username':
				return $this->$name;
		}

		if ($this->record === null)
		{
			$this->record = $this->rpc_query(
											'host.get_record',
											$this->xid
											);
		}
		if (isset($this->record[$name]))
		{
			return $this->record[$name];
		}

		if ($this->metrics_record === null)
		{
			$this->metrics_record = $this->rpc_query(
													'host_metrics.get_record',
													$this->id_metrics
			);
		}
		if (isset($this->metrics_record[$name]))
		{
			return $this->metrics_record[$name];
		}
/*
		if ($this->cpus_record === null)
		{
			foreach ($this->host_CPUs as $idcpu)
			{
				$this->cpus_record[$idcpu] = $this->rpc_query(
													'host_cpu.get_record',
													$idcpu
			);
			}
		}
		if (isset($this->cpus_record[$name]))
		{
			return $this->cpus_record[$name];
		}
*/
		if (isset ($this->$name))
		{
			throw new Exception('Property ' . __CLASS__ . '::' . $name . ' is not readable');
		}
		else
		{
			throw new Exception('No such property: ' . __CLASS__ . '::' . $name);
		}
	}

	public function __set ($name, $value)
	{
		switch ($name)
		{
		}
		if (isset ($this->$name))
		{
			throw new Exception('Property ' . __CLASS__ . '::' . $name . ' is not writable');
		}
		else
		{
			throw new Exception('No such property: ' . __CLASS__ . '::' . $name);
		}
	}

	public function getDomU($id)
	{
		if (isset ($this->domUs[$id]))
		{
			return $this->domUs[$id];
		}
		return false;
	}

	public function getDomUs()
	{
		return $this->domUs; // Maybe we should protect it.
	}

	public function get_vif_info($id)
	{
		return $this->connection->send('VIF.get_record', $id);
	}

	public function rpc_query($method, $params = null)
	{
		return $this->connection->send($method, $params);
	}

	public function get_cpus_infos()
	{
		foreach ($this->host_CPUs as $idcpu)
		{
			$this->cpus_record[$idcpu] = $this->rpc_query(
												'host_cpu.get_record',
												$idcpu
		);
		}
		return $this->cpus_record;
	}


	/**
	 * Server address: IP or name.
	 *
	 * @var string
	 */
	private $address;

	/**
	 * Unique identifier for the dom0 : adress + ":" + port.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Password used for the connection to the Xen daemon.
	 * @var string
	 */
	private $password;

	/**
	 * Xen daemon's port.
	 * @var int
	 */
	private $port;

	/**
	 * User used for the connection to the Xen daemon.
	 *
	 * @var string
	 */
	private $username;

	/**
	 * Handle of the connection to the Xen daemon.
	 *
	 * @var resource
	 */
	private $connection;

	/**
	 * DomUs of this dom0.
	 *
	 * @var array
	 */
	private $domUs;

	private $record = null;

	private $metrics_record = null;

	public $cpus_record = null;

	private $xid;

	private $id_metrics;

	private function connect()
	{
		$method = 'session.login_with_password';
		$params = array ($this->username,$this->password);
		$request = xmlrpc_encode_request($method,$params);
		$context = stream_context_create(array('http' => array(
			'method' => 'POST',
			'header' => 'Content-Type: text/xml',
			'content' => $request
		)));

		$data = @file_get_contents(
			'http://' . $this->address . ':' . $this->port,
			false,
			$context
		);
		if (!$data)
		{
			throw new Exception('Can\'t connect to ' . $this->address);
		}
		$response = xmlrpc_decode($data);
		if (xmlrpc_is_fault($response))
		{
			new Exception('XMLRPC error: ' . $response['faultString'] .' ('
				. $response['faultCode'] . ')');
		}

		$id = $response['Value'];
		$this->connection = new Rpc($this->address, $this->port, $id);
	}
}
