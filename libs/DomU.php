<?php

class DomU
{
	public $vcpu_number, $vcpu_use;
	private $xid, $state, $record, $sid, $metrics, $date, $domid;
	private $lastupdate;

	public function __construct($xid, Dom0 $dom0)
	{
		$this->xid = $xid;
		$this->dom0 = $dom0;
		
		// Get info.
		$this->refresh();
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
				if ($this->state === 'Paused')
				{
					$this->dom0->rpc_query ('VM.unpause', $this->xid);
				}
				else
				{
					$this->start();
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
		switch ($name)
		{
			case 'dom0':
				return $this->dom0;
			case 'id':
			case 'name':
				return $this->name;
			case 'state':
				return $this->record['power_state'];
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

	public function get_preview()
	{
		return array(
			'name' => $this->name,
			'state' => $this->state
		);
	}

	public function get_all_infos()
	{
		$cpu_counter = array();

		return array(
			'xid' => $this->record['domid'],
			'name' => $this->record['name_description'],
			'state' => $this->record['power_state'],
			'kernel' => $this->record['PV_kernel'],
			'weight' => $this->record['VCPUs_params']['weight'],
			'cap' => $this->record['VCPUs_params']['cap'],
			's_max_ram' => $this->record['memory_static_max'],
			's_min_ram' => $this->record['memory_static_min'],
			'd_max_ram' => $this->record['memory_dynamic_max'],
			'd_min_ram' => $this->record['memory_dynamic_min'],
			'auto_power_on' => $this->record['auto_power_on'],
			'vcpu_max' => $this->record['VCPUs_max'],
			'vcpus_at_startup' => $this->record['VCPUs_at_startup'],
			'actions_after_shutdown' => $this->record['actions_after_shutdown'],
			'actions_after_reboot' => $this->record['actions_after_reboot'],
			'actions_after_crash' => $this->record['actions_after_crash'],
			'template' => $this->record['is_a_template'],
			'pvargs' => $this->record['PV_args'],
			'vifs' => $this->record['VIFs'],
			'vbds' => $this->record['VBDs'],
			'sid' => $this->record['uuid'],
			'vcpu_use' => $this->vcpu_use,
			'vcpu_number' => $this->metrics['VCPUs_number'],
			'date' => $this->date->timestamp,
			'lastupdate' => $this->lastupdate->timestamp
		);
	}

	public function migrate($dest,$live)
	{
		$port = array('port' => 8002);
		$params = array($this->xid, $dest, true, $port);
		$this->dom0->rpc_query('VM.migrate', $params);
	}

	public function start($is_paused = false)
	{
		$params = array($this->xid, $is_paused);
		$this->dom0->rpc_query('VM.start', $params);
	}

	public function refresh()
	{
		$this->record = $this->dom0->rpc_query('VM.get_record', $this->xid);
		$this->sid 			= $this->record['uuid'];
		$this->name 		= $this->record['name_description'];
		$this->domid		= $this->record['domid'];
		$this->state 		= $this->record['power_state'];

		$this->metrics = $this->dom0->rpc_query('VM_metrics.get_record',$this->record['metrics']);

		$this->vcpu_number = $this->metrics['VCPUs_number'];
		$this->date = $this->metrics['start_time'];
		$this->lastupdate = $this->metrics['last_updated'];

		$this->vcpu_use = array();
		foreach($this->metrics['VCPUs_utilisation'] as $cpu)
		{
			$this->vcpu_use[] = round($cpu * 100, 2);
		}
	}

	/**
	 * The dom0 this domU belongs to.
	 *
	 * @param Dom0
	 */
	private $dom0;

	private $name;
}
