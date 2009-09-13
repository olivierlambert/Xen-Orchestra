<?php

class Dom0 {
	
	public $domN; // Dom0 number : is it dom0 "1" or dom0 "2" ? etc.
	public $address; // IP address or DNS name
	public $port; // Port for Xend daemon
	public
		$list_id_vm,
		$vm_table,
		$vif_record,
		$vm_metrics;
	public $handle,$idvm;
	public $database;
	//public $migrated; // table of name of migrated VM
	private $user,$pass; // user and password for Xend API
	
	// AUTO GETTERS, call with e.g : obj->id 
	/*
	public function __get($attr) {
		if(isset($this->$attr)) return $this->$attr;
		else throw new Exception('Unknown attribute '.$attr);
	}
	
	// AUTO SETTERS
	public function __set($attr,$value) {
		if(isset($this->$attr)) $this->$attr = $value;
		else throw new Exception('Unknow attribute '.$attr);
	}*/

	
	// CONSTR
	public function __construct($domN,$address,$port,$user,$pass) {
		
		$this->domN = $domN;
		$this->address = $address;
		$this->port = $port;
		$this->user = $user;
		$this->pass = $pass;
		$this->connect();
		$this->all_id_vm();
		$this->create_object_vm();
	}
	
	private function connect() {
		
		$method = "session.login_with_password";
		$params = array ($this->user,$this->pass);
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
	
	// TO DO : mettre dans le constructeur
	public function all_id_vm() {
	
	// display all detailed info of attached VM to this Dom0
		$this->list_id_vm = $this->handle->send("VM.get_all");
	}
	
	public function create_object_vm() {
		//Find already Migrated+Halted VM in order to NOT display them
		$db = sqlite_factory("database/test");
		//$dbresult = $db->query("SELECT name FROM migrated");
		//$migrated_array = $dbresult->fetchAll();
		//$migrated_array = $dbresult->fetchAll();
		//print_r($migrated_array);
		// first run : fill database with all VM names
		
		$this->vm_table = array ();
		foreach ($this->list_id_vm as $val) {
			$domU = new DomU($val,$this->handle);
			$db->query("INSERT INTO domU (vm_name,state) VALUES ('$domU->name','$domU->state')");
			$this->vm_table[] = $domU;
		}
		/*
		// second run : display all vm except "duplicate and halted" VM (becaue it's equivalent to a migrated VM)
		foreach ($this->list_id_vm as $val) {
			$domU = new DomU($val,$this->handle);
			//print_r($this->migrated);
			//$not_display = array_search($domU->name,$migrated_array);
			$dbresult = $db->query("SELECT vm_name FROM domU WHERE vm_name='$domU->name'");
			$duplicate = $dbresult->fetchAll();
			print_r($duplicate);
			
			if ($not_display === false) {
				 $this->vm_table[$i] = serialize($domU);
			 }
			elseif ($domU->state != "Halted") {
				//$db->query("DELETE FROM migrated WHERE name='$domU->name'");
				$this->vm_table[$i] = serialize($domU);
			}
			$i++;
		}*/
	}
	
	public function vm_attached_number() {
		// connect to the DB
		$db = sqlite_factory("database/test");
		$i = 0;
		foreach ($this->vm_table as $vm) {
			$dbresult = $db->query("SELECT vm_name FROM domU WHERE vm_name='$vm->name'");
			$duplicate = $dbresult->numRows();
			if (!($duplicate>1 and $vm->state=="Halted")) {
				$i++; // THIS IS NOT A MIGRATED VM: COUNT IT!!
			}
		}
		// minus 1 because Dom0 count as a machine
		return $i-1;
	}
		//return count($this->vm_table)-1;
	
	
	public function put_in_database() {
		
		
	}
	
	public function get_vif_info($id) {	
		$this->vif_record = $this->handle->send("VIF.get_record",$id);
		return $this->vif_record;
	}
	
	public function get_vm_metrics() {	
		$this->vm_metrics = $this->handle->send("host.get_record");
		return $this->vm_metrics;
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
	
	public function get_vm_name($i) {
		$domU = $this->vm_table[$i];
		return $domU->name;
	}
	
	public function migrate_vm($i,$dest,$live) {
		$domU = $this->vm_table[$i];
		$domU->migrate($dest,$live);
	}
	
	public function is_migrated($i) {
		$domU = $this->vm_table[$i];
		return $domU->migrated;
	}

	public function set_migrated($i,$bool) {
		$domU = $this->vm_table[$i];
		$domU->set_migrated($bool);
	}
	
	// to String
	
	public function __toString() {
		
		return $this->address.' avec l\'ID : '.$this->handle->id;
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
				echo '<tr><td><a href="vm.php?vm='.$i.'&action=migrate_vm&dom0='.$this->domN.'&target='.$val.'">'.$val.'</a></tr></td>';
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
		//print_r($array);
		// Round operation for RAM count
		for ($j=6;$j<10;$j++) {
			if ($array[$j]>=1073741824) {$array[$j] = round($array[$j]/(1024*1024*1024))." Go"; }
			else { $array[$j] = round($array[$j]/(1024*1024)) ." Mo"; }
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
				foreach ($other_domains as $val) {
					echo '<tr><td><a href="vm.php?vm='.$i.'&action=migrate_vm&dom0='.$this->domN.'&target='.$val.'">'.$val.'</a></tr></td>';
				}
			}
			else {
				echo '<tr><td>No other Dom0\'s found !</tr></td>';
			}
			echo '</table><br/>';
		}
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
	
	public function display_row_vm($i) {
		// connect to the DB
		$db = sqlite_factory("database/test");
		
		// displays rows for each VM
		$vm = $this->vm_table[$i];
		$dbresult = $db->query("SELECT vm_name FROM domU WHERE vm_name='$vm->name'");
		$duplicate = $dbresult->numRows();
		$title_window = "<b>$vm->name</b> on $this->address";
		if ($duplicate>1 and $vm->state=="Halted") {
			// THIS IS A MIGRATED VM : DO NOT DISPLAY !!
		}
		else {
		
			$array = $vm->get_preview();
			// Display different icons depending of the state
			if ($array['state']=="Running") {
				$id = "pause";
				$action1 = "pause_vm";
				$icon1 = "pause.png";
				$title1 = "Pause this DomU";
				$action2 = "shutdown_vm";
				$icon2 = "stop.png";
				$title2 = "Halt this DomU";
			}
			elseif ($array['state']=="Paused") {
				$id = "unpause";
				$action1 = "unpause_vm";
				$icon1 = "play.png";
				$title1 = "Unpause this DomU";
				$action2 = "shutdown_vm";
				$icon2 = "stop.png";
				$title2 = "Halt this DomU";
			}
			elseif ($array['state']=="Halted") {
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
			<td><a href="index.php?vm='.$i.'&action='.$action1.'&dom0='.$this->domN.'">
			<img border=0 title="'.$title1.'" src="img/'.$icon1.'"></a>
			<a href="index.php?vm='.$i.'&action='.$action2.'&dom0='.$this->domN.'">
			<img border=0 title="'.$title2.'" src="img/'.$icon2.'"></a>
			</td>';
			// add Edit icon
			echo '
			<td><a href="#"><img border=0 title="Edit this DomU" onclick="disp_vm('.$i.','.$this->domN.',\''.$title_window.'\');manualreload()" src="img/action.png"></a></td>
			</tr> ';
		}
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
					<th>ID</th>
					<th>Name</th>
					<th>State</th>
					<th>Actions</th>
					<th>Advanced</th>
				</tr>';
			for($i=1; $i<count($this->vm_table);$i++) {
				print_r($this->is_migrated($i));
				if (!$this->is_migrated($i)) {
					$this->display_row_vm($i);
				}
			}
			echo '</table><br/>';
		}
	}
	
	public function ajax() {
		return "TOTO";
	}
	
	public function find_migrated_vm() {
		for($i=1; $i<count($this->vm_table);$i++) {
			if ($this->is_migrated($i)) {
				array_push($result,$i);
			}
		return $result;
		}
	}

	

}
