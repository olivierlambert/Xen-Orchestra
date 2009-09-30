<?php

class Dom0
{
	public
		$list_id_vm,
		$vm_table,
		$vif_record,
		$handle;

	//public $migrated; // table of name of migrated VM
	public $id_dom0,$id_metrics_dom0;

	/**
	 * Create a new Dom0 object.
	 *
	 * @param address  ...
	 * @param port     ...
	 * @param user     ...
	 * @param password ...
	 *
	 * @TODO Write proper doc, check arguments.
	 */
	public function __construct($address, $port, $user, $password)
	{
		$this->id = $address . ':' . $port;
		$this->address = $address;
		$this->port = $port;
		$this->user = $user;
		$this->password = $password;

		$this->connect();

		$this->list_id_vm = $this->handle->send('VM.get_all');

		$this->create_object_vm();
	}

	public function __get ($name)
	{
		switch ($name)
		{
			case 'address':
			case 'id':
			case 'port':
			case 'user':
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

	public function create_object_vm()
	{
		$db = DB::get_instance();
		$this->vm_table = array ();
		foreach ($this->list_id_vm as $val)
		{
			$domU = new DomU($val,$this->handle);
			if (Model::get_domU($domU->name,$domU->state,$this->id) === null)
			{
				$db->query('INSERT INTO domU (name,state,id) VALUES ("'.$domU->name.'","'.$domU->state.'","'.$this->id.'")');
			}
			$this->vm_table[] = $domU;
		}
	}

	public function vm_attached_number()
	{
		// connect to the DB
		$db = DB::get_instance();
		$dbresult = $db->query('SELECT COUNT(*) FROM domU WHERE id="'.$this->id.'" AND state!="Migrated"');
		$count = $dbresult->fetchSingle();

		return $count-1;
	}

	public function host_infos()
	{
		$this->id_dom0 = $this->handle->send('host.get_all');
		$this->id_metrics_dom0 = $this->handle->send('host_metrics.get_all');
		$this->id_metrics_dom0 = $this->id_metrics_dom0[0];
	}

	public function host_record()
	{
		$this->host_infos();
		return (
		//$this->handle->send("host.get_cpu_configuration",$this->id_dom0),
		//$this->handle->send("host.get_get_sched_policy",$this->id_dom0)
		//$this->handle->send("host.get_supported_bootloaders",$this->id_dom0)
		//$this->handle->send("host.get_metrics",$this->id_dom0)
		$this->handle->send('host.list_methods')
		//$this->id_metrics_dom0
		//$this->handle->send("host.get_record",$this->id_metrics_dom0)
		//$this->handle->send("host.get_capabilities",$this->id_dom0)
		);
	}

	public function get_vif_info($id)
	{
		$this->vif_record = $this->handle->send('VIF.get_record',$id);
		return $this->vif_record;
	}

	public function get_uuid($i)
	{
		$domU = $this->vm_table[$i];
		$string = $domU->sid;
		return $string;
	}

	public function get_state($i)
	{
		$domU = $this->vm_table[$i];
		$string = $domU->state;
		return $string;
	}

	public function get_record($i)
	{
		$domU = $this->vm_table[$i];
		$string = $domU->record;
		return $string;
	}

	public function destroy_vm($i)
	{
		$domU = $this->vm_table[$i];
		$domU->destroy();
	}

	public function shutdown_vm($i)
	{
		$domU = $this->vm_table[$i];
		$domU->shutdown();
	}

	public function start_vm($i)
	{
		$is_paused = false;
		$domU = $this->vm_table[$i];
		$domU->start($is_paused);
	}

	public function pause_vm($i)
	{
		$domU = $this->vm_table[$i];
		$domU->pause();
	}

	public function unpause_vm($i)
	{
		$domU = $this->vm_table[$i];
		$domU->unpause();
	}

	public function get_name($i)
	{
		$domU = $this->vm_table[$i];
		return $domU->name;
	}

	public function migrate_vm($i,$dest,$live)
	{
		$domU = $this->vm_table[$i];
		$domU->migrate($dest,$live);
	}

	public function __toString()
	{
		return $this->id;
	}

////////////////////// DISPLAY PART OF CLASS /////////////////////////
/////////// TODO : Put this stuff in another class ? /////////////////
//////////////////////////////////////////////////////////////////////


	public function display_page_migrate($i,$other_domains) {
		$domU = $this->vm_table[$i];
		$array = $domU->get_all_infos();
		echo '<table>
		<tr>
			<th>Live Migration Target</th>
		</tr>
		';
		if (count($other_domains)>0) {
			foreach ($other_domains as $val) {
				echo '<tr><td><a href="vm.php?vm='.$i.'&action=migrate_vm&dom0='.$this->id.'&target='.$val.'">'.$val.'</a></tr></td>';
			}
		}
		else {
			echo '<tr><td>No other Dom0\'s found !</tr></td></table>';
		}
	}

	public function display_page_vm($i,$other_domains) {

		$domU = $this->vm_table[$i];
		$array = $domU->get_all_infos();
		$vifc = $this->get_vif_info($array[19]);
		$domU->metrics_all($i);
		$cpu_use = $domU->vcpu_use;
		$cpu_number = $domU->vcpu_number;
		$started = $domU->date->timestamp;
		$modified = $domU->lastupdate->timestamp;
		// Round operation for RAM count
		for ($j=6;$j<10;$j++) {
			if ($array[$j]>=1073741824) {$array[$j] = round($array[$j]/(1024*1024*1024)).' Go'; }
			else { $array[$j] = round($array[$j]/(1024*1024)) .' Mo'; }
		}
		//<div id="left">

		echo '<h2>"'.$array[1].'" is '.$array[2].'</h2>';
		if ($array[2]=="Running") {
				echo '
				<p class="left"><img border=0 title="Live Migration" src="img/migrate.png"></p>
				<table>
			<tr>
				<th>Live Migration Target</th>
			</tr>
			';
			if (count($other_domains)>0) {
				foreach (array_keys($other_domains) as $val) {
					list($address, $port) = explode(':', $val, 2);
					echo '<tr><td><a href="vm.php?vm='.$i.'&action=migrate_vm&dom0='.$this->id.'&target='.$address.'">'.$address.'</a></tr></td>';
				}
			}
			else {
				echo '<tr><td>No other Dom0\'s found !</tr></td>';
			}
			echo '</table><br/>';

		}
		/* CLONE VM : doesn't work with API, cf DomU.php
		if ($array[2]=="Halted") {
			// test clone
			$address = 'clone_'.$array[1];
			echo '<br/><a href="vm.php?vm='.$i.'&action=clone_vm&dom0='.$this->domN.'&target='.$address.'">'.$address.'</a><br/>';
		}*/
		//<h3>"'.$array[1].'" is '.$array[2].' (on '.$this->address.')</h3>
		echo '
				<p class="left"><img border=0 title="CPU" src="img/cpu.png"></p>
				<table>
				<th>Weight</th>
				<th>Cap</th>
				<th>VCPU at startup</th>
				<th>VCPU Max</th>
			</tr>
			<tr>
				<td>'.$array[4].'</td>
				<td>'.$array[5].'</td>
				<td>'.$array[12].'</td>
				<td>'.$array[13].'</td>
			</tr>
			</table>
			<br/>
			<p class="left"><img border=0 title="RAM" src="img/ram.png"></p>
			<table>
			<tr>
				<th>Memory static max</th>
				<th>Memory static min</th>
				<th>Memory dynamic max</th>
				<th>Memory dynamic min</th>
			</tr>
			<tr>
				<td>'.$array[6].'</td>
				<td>'.$array[7].'</td>
				<td>'.$array[8].'</td>
				<td>'.$array[9].'</td>
			</tr>
		</table>
		<br/>
		</table>
		<p class="left"><img border=0 title="Network" src="img/network.png"></p>
			<table>
			<tr>
				<th>Device</th>
				<th>MAC address</th>
				<th>MTU</th>
			</tr>
			<tr>
				<td>'.$vifc['device'].'</td>
				<td>'.$vifc['MAC'].'</td>
				<td>'.$vifc['MTU'].'</td>
			</tr>
		</table>
		<br/>
		<p class="left"><img border=0 title="Behavior" src="img/behavior.png"></p>
		<table>
			<tr>
				<th>After shutdown</th>
				<th>After reboot</th>
				<th>After crash</th>
			</tr>
			<tr>
				<td>'.$array[14].'</td>
				<td>'.$array[15].'</td>
				<td>'.$array[16].'</td>
			</tr>
		</table>
		';
	}
	public function detect_migrated()
	{
		// connect to the DB
		$db = DB::get_instance();
		for($i=1; $i<count($this->vm_table);$i++)
		{
			// displays rows for each VM
			$vm = $this->vm_table[$i];
			$dbresult = $db->query('SELECT COUNT (name) FROM domU WHERE name="'.$vm->name.'"');
			$result = $dbresult->fetchSingle();
			echo 'OCCURENCES : '.$result.' // ';
			if ($result>1 && $vm->state=='Halted')
			{
				//$db->query('UPDATE domU SET state="Migrated" WHERE name="'.$vm->name.'" AND id="'.$this->id.'"');
				//$vm->state = 'Migrated';
				unset($this->vm_table[$i]);
			}
		}
	}

	public function vm_json($i) {
	$db = DB::get_instance();
	// displays rows for each VM
	$vm = $this->vm_table[$i];
	$vm->metrics_all($i);
	//if (Model::get_domU($vm->name, "Migrated", $this->id)) {return null;}
	//$dbresult = $db->query('SELECT state FROM domU WHERE name="'.$vm->name.'" AND id="'.$this->id.'" AND state!="Migrated"');
	$state = $vm->get_state();
	$cpu_use = $vm->vcpu_use;
	$cpu_counter = array();
	// build array of cpu
	foreach($cpu_use as $cpu) {
		$cpu_counter[] = round($cpu*100,2);
	}

	return $result = array(
					'name' => $vm->name,
					'state' => $state,
					'cpu_number' => $vm->vcpu_number,
					'cpu_use' => $cpu_counter,
					'started' => $vm->date->timestamp,
					'modified' => $vm->lastupdate->timestamp);
	}

	public function table_dom0()
	{
		$this->list_id_vm = $this->handle->send('VM.get_all');
		$this->create_object_vm();
		$this->detect_migrated();
		
		$result = array();
		$domUs = array();
		$vm_number = $this->vm_attached_number();

		if ($vm_number<1)
		{
			$result = array(
					'id' => $this->id,
					'name' => $this->address,
					'vm_number' => 0,
					'domUs' => null
					);
		}
		else
		{
			$n = count($this->vm_table);
			for($i=1; $i<$n;$i++)
			{
				$domUs[] = $this->vm_json($i);
			}
			$result = array(
					'id' => $this->id,
					'name' => $this->address,
					'vm_number' => $vm_number,
					'domUs' => $domUs);
		}

		return $result;
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
	private $user;

	private function connect()
	{
		$method = "session.login_with_password";
		$params = array ($this->user,$this->password);
		$request = xmlrpc_encode_request($method,$params);
		$context = stream_context_create(array('http' => array(
			'method' => "POST",
			'header' => "Content-Type: text/xml",
			'content' => $request
		)));

		$file = file_get_contents("http://".$this->address.":".$this->port, false, $context);
		if (!$file) {throw new Exception("Can't connect to $this->address");}

		$response = xmlrpc_decode($file);
		if (xmlrpc_is_fault($response))
		{
			trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
		}
		else
		{
			$id = $response['Value'];
			$this->handle = new Rpc($this->address,$this->port,$id);
		}
	}
}
