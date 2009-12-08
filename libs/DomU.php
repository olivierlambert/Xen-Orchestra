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
				// Need to separate clean and hard shutdown, e.g poweroff
				//$this->dom0->rpc_query('VM.hard_shutdown', $this->xid);
				$this->dom0->rpc_query('VM.clean_shutdown',$this->id);
				break;
			case 'poweroff':
				$this->dom0->rpc_query('VM.hard_shutdown', $this->xid);
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
/*
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
*/
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

