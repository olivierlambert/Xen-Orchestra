<?php

class DomU
{

	public $name,$sid,$id,$xid,$state,$kernel,$weight,$cap,$record;
	public $s_max_ram,$s_min_ram,$d_max_ram,$d_min_ram,$template;
	public $auto_power_on,$suspend_vdi,$vcpu_max,$vcpus_at_startup,$actions_after_shutdown;
	public $actions_after_crash,$actions_after_reboot,$pvargs;
	public $vifs,$vbds,$consoles;
	public $migrated;
	public $metricsid,$metrics;
	public $vcpu_use,$vcpu_number,$date,$lastupdate;

	public function __construct($id, Dom0 $dom0)
	{
		$this->id = $id;
		$this->dom0 = $dom0;
		$this->record = $this->dom0->rpc_query('VM.get_record',$this->id);

		// build record
		$this->sid 			= $this->record['uuid'];
		$this->name 		= $this->record['name_description'];
		$this->xid 			= $this->record['domid'];
		$this->state 		= $this->record['power_state'];
		$this->kernel 		= $this->record['PV_kernel'];
		$this->weight 		= $this->record['VCPUs_params']['weight'];
		$this->cap 			= $this->record['VCPUs_params']['cap'];
		$this->s_max_ram	= $this->record['memory_static_max'];
		$this->s_min_ram	= $this->record['memory_static_min'];
		$this->d_max_ram	= $this->record['memory_dynamic_max'];
		$this->d_min_ram	= $this->record['memory_dynamic_min'];
		$this->auto_power_on= $this->record['auto_power_on'];
		$this->vcpu_max 	= $this->record['VCPUs_max'];
		$this->template		= $this->record['is_a_template'];
		$this->pvargs		= $this->record['PV_args'];
		$this->vifs			= $this->record['VIFs'];
		$this->vbds			= $this->record['VBDs'];
		$this->consoles		= $this->record['consoles'];
		$this->metricsid	= $this->record['metrics'];
		$this->vcpus_at_startup 		= $this->record['VCPUs_at_startup'];
		$this->actions_after_shutdown 	= $this->record['actions_after_shutdown'];
		$this->actions_after_reboot		= $this->record['actions_after_reboot'];
		$this->actions_after_crash 		= $this->record['actions_after_crash'];


		$this->metrics = $this->dom0->rpc_query('VM_metrics.get_record',$this->metricsid);

		$this->vcpu_number = $this->metrics['VCPUs_number'];
		$this->date = $this->metrics['start_time'];
		$this->lastupdate = $this->metrics['last_updated'];

		$this->vcpu_use = array();
		foreach($this->metrics['VCPUs_utilisation'] as $cpu)
		{
			$this->vcpu_use[] = round($cpu * 100, 2);
		}
	}

	public function __call($name, $arguments)
	{
		switch ($name)
		{
			case 'destroy':
			case 'pause':
			case 'resume':
			case 'suspend':
			case 'unpause':
				$this->dom0->rpc_query ('VM.' . $name, $this->id);
				break;
			case 'reboot':
				$this->dom0->rpc_query('VM.hard_reboot', $this->id);
				break;
			case 'shutdown':
				//* TODO: decide wether we use hard or clean shutdown.
				$this->dom0->rpc_query('VM.hard_shutdown', $this->id);
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
			case 'state':
				return $this->dom0->rpc_query('VM.get_power_state',$this->id);
			case 'dom0':
				return $this->$name;
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
			'xid' => $this->xid,
			'name' => $this->name,
			'state' => $this->state,
			'kernel' => $this->kernel,
			'weight' => $this->weight,
			'cap' => $this->cap,
			's_max_ram' => $this->s_max_ram,
			's_min_ram' => $this->s_min_ram,
			'd_max_ram' => $this->d_max_ram,
			'd_min_ram' => $this->d_min_ram,
			'auto_power_on' => $this->auto_power_on,
			'suspend_vdi' => $this->suspend_vdi,
			'vcpu_max' => $this->vcpu_max,
			'vcpus_at_startup' => $this->vcpus_at_startup,
			'actions_after_shutdown' => $this->actions_after_shutdown,
			'actions_after_reboot' => $this->actions_after_reboot,
			'actions_after_crash' => $this->actions_after_crash,
			'template' => $this->template,
			'pvargs' => $this->pvargs,
			'vifs' => $this->vifs,
			'vbds' => $this->vbds,
			'sid' => $this->sid,
			'vcpu_use' => $this->vcpu_use,
			'vcpu_number' => $this->vcpu_number,
			'date' => $this->date->timestamp,
			'lastupdate' => $this->lastupdate->timestamp
		);
	}

	public function migrate($dest,$live)
	{
		$port = array('port' => 8002);
		$params = array($this->id, $dest, true, $port);
		$this->dom0->rpc_query('VM.migrate', $params);
	}

	public function set_migrated($bool)
	{
		$this->migrated = $bool;
	}

	public function start($is_paused)
	{
		$params = array($this->id, $is_paused);
		$this->dom0->rpc_query('VM.start', $params);
	}

	/**
	 * The dom0 this domU belongs to.
	 *
	 * @param Dom0
	 */
	private $dom0;
}

