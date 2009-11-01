<?php

class DomU
{
	public function __construct($xid, Dom0 $dom0)
	{
		$this->xid = $xid;
		$this->dom0 = $dom0;
	}

	public function __call($name, $arguments)
	{
		switch ($name)
		{
			case 'delete':
			case 'pause':
				$this->dom0->rpc_query ('VM.' . $name, $this->xid);
				break;
			case 'play':
				if ($this->power_state === 'Paused')
				{
					$this->dom0->rpc_query ('VM.unpause', $this->xid);
				}
				else
				{
					$this->dom0->rpc_query('VM.start', array(
						$this->xid,
						false // Do not start paused.
					));
				}
				break;
			case 'stop':
				//* TODO: decide wether we use hard or clean shutdown.
				$this->dom0->rpc_query('VM.hard_shutdown', $this->xid);
				/*/
				$this->dom0->rpc_query('VM.clean_shutdown',$this->id);
				//*/
				break;
			default:
				throw new Exception('No such method: ' . __CLASS__ . '::' . $name);
		}
	}

	public function __get($name)
	{
		if ($name === 'dom0')
		{
			return $this->dom0;
		}

		if (isset(self::$aliases[$name]))
		{
			$name = self::$aliases[$name];
		}

		if ($this->vm_record === null)
		{
			$this->vm_record = $this->dom0->rpc_query(
				'VM.get_record',
				$this->xid
			);
		}
		if (isset($this->vm_record[$name]))
		{
			return $this->vm_record[$name];
		}

		if ($this->vm_metrics_record === null)
		{
			$this->vm_metrics_record = $this->dom0->rpc_query(
				'VM_metrics.get_record',
				$this->vm_record['metrics']
			);
		}
		if (isset($this->vm_metrics_record[$name]))
		{
			return $this->vm_metrics_record[$name];
		}

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

	public function migrate($dest, $live)
	{
		$port = array('port' => 8002);
		$params = array($this->xid, $dest, true, $port);
		$this->dom0->rpc_query('VM.migrate', $params);
	}

	public function lol()
	{
		return ($this->dom0->rpc_query('VM.get_record', $this->xid));
	}

	public function refresh()
	{
		$this->vm_record = null;
		$this->vm_metrics_record = null;
	}

	static private $aliases = array(
		'id' => 'uuid',
		'name' => 'name_label',
	);

	private $xid;

	/**
	 * The dom0 this domU belongs to.
	 *
	 * @param Dom0
	 */
	private $dom0;

	private $vm_record = null;

	private $vm_metrics_record = null;
}

