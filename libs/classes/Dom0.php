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

	public function create_object_vm() {
		$db = DB::get_instance();
		$this->vm_table = array ();
		foreach ($this->list_id_vm as $val) {
			$domU = new DomU($val,$this->handle);
			if (Model::get_domU($domU->name,$domU->state,$this->id) === null) {
				$db->query('INSERT INTO domU (name,state,id) VALUES ("'.$domU->name.'","'.$domU->state.'","'.$this->id.'")');
				
			}
			$this->vm_table[] = $domU;
		}
	}

	public function vm_attached_number() {
		// connect to the DB
		$db = DB::get_instance();
		$dbresult = $db->query('SELECT COUNT(*) FROM domU WHERE id="'.$this->id.'" AND state!="Migrated"');
		$count = $dbresult->fetchSingle();

		return $count-1;
	}

	public function host_infos() {
		$this->id_dom0 = $this->handle->send('host.get_all');
		$this->id_metrics_dom0 = $this->handle->send('host_metrics.get_all');
		$this->id_metrics_dom0 = $this->id_metrics_dom0[0];
	}

	public function host_record() {
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

	public function get_vif_info($id) {
		$this->vif_record = $this->handle->send('VIF.get_record',$id);
		return $this->vif_record;
	}

	public function get_uuid($i) {
		$domU = $this->vm_table[$i];
		$string = $domU->sid;
		return $string;
	}

	public function get_state($i) {
		$domU = $this->vm_table[$i];
		$string = $domU->state;
		return $string;
	}

	public function get_record($i) {
		$domU = $this->vm_table[$i];
		$string = $domU->record;
		return $string;
	}

	public function destroy_vm($i) {
		$domU = $this->vm_table[$i];
		$domU->destroy();
	}

	public function shutdown_vm($i) {
		$domU = $this->vm_table[$i];
		$domU->shutdown();
	}

	public function start_vm($i) {
		$is_paused = false;
		$domU = $this->vm_table[$i];
		$domU->start($is_paused);
	}

	public function pause_vm($i) {
		$domU = $this->vm_table[$i];
		$domU->pause();
	}

	public function unpause_vm($i) {
		$domU = $this->vm_table[$i];
		$domU->unpause();
	}

	public function get_name($i) {
		$domU = $this->vm_table[$i];
		return $domU->name;
	}

	public function migrate_vm($i,$dest,$live) {
		$domU = $this->vm_table[$i];
		$domU->migrate($dest,$live);
	}
	/*
	public function is_migrated($i) {
		$domU = $this->vm_table[$i];
		return $domU->migrated;
	}

	public function set_migrated($i,$bool) {
		$domU = $this->vm_table[$i];
		$domU->set_migrated($bool);
	}

	public function clone_vm($i,$name) {
		$domU = $this->vm_table[$i];
		$domU->clonevm($name);
	}
	*/
	// to String

	public function __toString() {
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
	public function detect_migrated() {
		// connect to the DB
		$db = DB::get_instance();
		for($i=1; $i<count($this->vm_table);$i++) {
			// displays rows for each VM
			$vm = $this->vm_table[$i];
			$dbresult = $db->query('SELECT COUNT (name) FROM domU WHERE name="'.$vm->name.'"');
			$result = $dbresult->fetchSingle();

			if ($result>1 && $vm->state=='Halted') {
				// THIS IS A MIGRATED VM : DO NOT DISPLAY !!
				// update state to migrated
				//echo 'MIGREE : '.$vm->name.' !!';
				$db->query('UPDATE domU SET state="Migrated" WHERE name="'.$vm->name.'" AND id="'.$this->id.'"');
			}
		}
	}

	public function display_row_vm($i) {
		// connect to the DB
		$db = DB::get_instance();

		// displays rows for each VM
		$vm = $this->vm_table[$i];
		$dbresult = $db->query('SELECT state FROM domU WHERE name="'.$vm->name.'" AND id="'.$this->id.'"');
		$state = $dbresult->fetchSingle();
		$title_window = '<b>'.$vm->name.'</b> on '.$this->address.'';
		if ($state=='Migrated') {
			// THIS IS A MIGRATED VM : DO NOT DISPLAY !!
		}
		else {
			$array = $vm->get_preview();
			$vm->metrics_all($i);

			// extra infos
			$cpu_use = $vm->vcpu_use;
			$cpu_number = $vm->vcpu_number;
			$started = $vm->date->timestamp;
			$modified = $vm->lastupdate->timestamp;

			// Display different icons depending of the state
			if ($array['state']=='Running') {
				$id = "pause";
				$action1 = "pause_vm";
				$icon1 = "pause.png";
				$title1 = "Pause this DomU";
				$action2 = "shutdown_vm";
				$icon2 = "stop.png";
				$title2 = "Halt this DomU";
			}
			elseif ($array['state']=='Paused') {
				$id = "unpause";
				$action1 = "unpause_vm";
				$icon1 = "play.png";
				$title1 = "Unpause this DomU";
				$action2 = "shutdown_vm";
				$icon2 = "stop.png";
				$title2 = "Halt this DomU";
			}
			elseif ($array['state']=='Halted') {
				$id = "start";
				$action1 = "start_vm";
				$icon1 = "start.png";
				$title1 = "Start this DomU";
				$action2 = "destroy_vm";
				$icon2 = "destroy.png";
				$title2 = "Remove this DomU from Xen Management";
			}
			// fill the line with each value
			echo '<tr>';
			foreach ($array as $val) {
				echo '<td>'.$val.'</td>';
			}
			// add action icons
			echo '
			<td><a href="index.php?vm='.$i.'&action='.$action1.'&dom0='.$this->id.'">
			<img border=0 title="'.$title1.'" src="img/'.$icon1.'"></a>
			<a href="index.php?vm='.$i.'&action='.$action2.'&dom0='.$this->id.'">
			<img border=0 title="'.$title2.'" src="img/'.$icon2.'"></a>
			<a href="#"><img border=0 title="Edit this DomU" onclick="disp_vm('.$i.',\''.$this->id.'\',\''.$title_window.'\')" src="img/action.png"></a></td>

			<td>';
			// CPU counter
			foreach ($cpu_use as $cpu) {
				$val = round($cpu*100,2);
				if ($val < 25) {
					echo '<img border=0 title="'.$val.'" src="img/cgreen.png">';
				}
				elseif ($val < 50) {
					echo '<img border=0 title="'.$val.'" src="img/cyellow.png">';
				}
				elseif ($val < 75) {
					echo '<img border=0 title="'.$val.'" src="img/corange.png">';
				}
				else {
					echo '<img border=0 title="'.$val.'" src="img/cred.png">';
				}
			}
			echo '</td></tr>';
		}
	}

	public function display_frame_vm($i) {
		// connect to the DB
		$db = DB::get_instance();

		// displays rows for each VM
		$vm = $this->vm_table[$i];
		$dbresult = $db->query("SELECT state FROM domU WHERE name='$vm->name' AND domN='$this->id'");
		$state = $dbresult->fetchSingle();
		$title_window = "<b>$vm->name</b> on $this->address";
		if ($state=="Migrated") {
			// THIS IS A MIGRATED VM : DO NOT DISPLAY !!
		}
		else {
			$array = $vm->get_preview();
			$vm->metrics_all($i);

			// extra infos
			$cpu_use = $vm->vcpu_use;
			$cpu_number = $vm->vcpu_number;
			$started = $vm->date->timestamp;
			$modified = $vm->lastupdate->timestamp;

			// Display different icons depending of the state
			if ($array['state']=="Running") {
				$action1 = "pause_vm";
				$icon1 = "pause.png";
				$title1 = "Pause this DomU";
				$action2 = "shutdown_vm";
				$icon2 = "stop.png";
				$title2 = "Halt this DomU";
			}
			elseif ($array['state']=="Paused") {
				$action1 = "unpause_vm";
				$icon1 = "play.png";
				$title1 = "Unpause this DomU";
				$action2 = "shutdown_vm";
				$icon2 = "stop.png";
				$title2 = "Halt this DomU";
			}
			elseif ($array['state']=="Halted") {
				$action1 = "start_vm";
				$icon1 = "start.png";
				$title1 = "Start this DomU";
				$action2 = "destroy_vm";
				$icon2 = "destroy.png";
				$title2 = "Remove this DomU from Xen Management";
			}
			else {
				// Crashed, TODO.
			}
			// fill the line with each value
			$return = '<tr>';
			foreach ($array as $val) {
				$return .= '<td>'.$val.'</td>';
			}
			// add action icons
			$return .= '
			<td><a href="index.php?vm='.$i.'&action='.$action1.'&dom0='.$this->id.'">
			<img border=0 title="'.$title1.'" src="img/'.$icon1.'"></a>
			<a href="index.php?vm='.$i.'&action='.$action2.'&dom0='.$this->id.'">
			<img border=0 title="'.$title2.'" src="img/'.$icon2.'"></a>
			<a href="#"><img border=0 title="Edit this DomU" onclick="disp_vm('.$i.',\''.$this->id.'\',\''.$title_window.'\')" src="img/action.png"></a></td>

			<td>';
			// CPU counter
			foreach ($cpu_use as $cpu) {
				$val = round($cpu*100,2);
				if ($val < 25) {
					$return .= '<img border=0 title="'.$val.'" src="img/cgreen.png">';
				}
				elseif ($val < 50) {
					$return .= '<img border=0 title="'.$val.'" src="img/cyellow.png">';
				}
				elseif ($val < 75) {
					$return .= '<img border=0 title="'.$val.'" src="img/corange.png">';
				}
				else {
					$return .= '<img border=0 title="'.$val.'" src="img/cred.png">';
				}
			}
			$return .= '</td></tr>';
		}
	return $return;
	}

	public function display_table_all_vm() {
		// if there is no DomU attached
		if ($this->vm_attached_number()<1) {
			echo '<h4>No DomU detected on '.$this->address.'</h4>';
		}
		else {
			echo '<br/><table>
				<caption>Dom0 '.$this->address.'</caption>
				<tr>
					<th>Name</th>
					<th>State</th>
					<th>Actions</th>
					<th>Load</th>
				</tr>';
			//$this->detect_migrated();
			for($i=1; $i<count($this->vm_table);$i++) {
					$this->display_row_vm($i);
			}
			echo '</table><br/>';
		}
	}

	public function display_frame_all_vm() {
		// if there is no DomU attached
		if ($this->vm_attached_number()<1) {
			$return = '<p class="center">No DomU detected on '.$this->address.' !</p>';
		}
		else {
			$return = '<table>
				<tr>
					<th>Name</th>
					<th>State</th>
					<th>Actions</th>
					<th>Load</th>
				</tr>';

			for($i=1; $i<count($this->vm_table);$i++) {
					$return .= $this->display_frame_vm($i);
			}
			$return .= '</table>';
		}
			return $return;
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

	private function connect() {

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
