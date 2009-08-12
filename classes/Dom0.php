<?php

class Dom0 {
	
	public $id;
	public $domN; // is it dom01 or dom02 ? etc.
	public $address;
	public $port;
	public $list_id_vm,$vm_table;
	public $handle,$idvm;
	public $database;
	private $user,$pass;
	
	// AUTO GETTERS, call with e.g : obj->id 
	public function __get($attr) {
		if(isset($this->$attr)) return $this->$attr;
		else throw new Exception('Unknown attribute '.$attr);
	}
	
	// AUTO SETTERS
	public function __set($attr,$value) {
		if(isset($this->$attr)) $this->$attr = $value;
		else throw new Exception('Unknow attribute '.$attr);
	}

	
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
		$response = xmlrpc_decode($file);
		if (xmlrpc_is_fault($response)) 
		{
			trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
		} 
		else 
		{
			$this->id = $response['Value'];
			$this->handle = new Rpc($this->address,$this->port,$this->id);
		}
	}
	
	public function create_object_vm() {
		
		foreach ($this->list_id_vm as $val) {
			$domU = new DomU($val,$this->handle);
			$this->vm_table[$i] = serialize($domU);
			$i++;
		}
		//echo unserialize($this->vm_table[1]);
	}
	
	public function put_in_database() {
		
		
	}
	
	public function get_uuid($i) {
		$domU = unserialize($this->vm_table[$i]);
		$string = $domU->sid;
		return $string;
	}
	
	public function get_record($i) {
		$domU = unserialize($this->vm_table[$i]);
		$string = $domU->record;
		return $string;
	}
	
	public function display_page_vm($i,$other_domains) {
		
		$domU = unserialize($this->vm_table[$i]);
		$string = $domU->get_all_infos();
		$array = explode(',',$string);
		
		// Round operation for RAM count
		for ($j=6;$j<10;$j++) {
			if ($array[$j]>=1073741824) {$array[$j] = round($array[$j]/(1024*1024*1024))." Go"; }
			else { $array[$j] = round($array[$j]/(1024*1024)) ." Mo"; }
		}
		
		echo '
		<h2>DomU Settings</h2>
		<h3>"'.$array[1].'" is '.$array[2].' (on '.$this->address.')</h3>
		<div id="left">
			<br/>';
			if ($array[2]=="Running") {
				$pause_unpause = "Pause";
				$pause_action = 1;
				$icon_pause = "pause.png";
			}
			elseif ($array[2]=="Paused") {
				$pause_unpause = "Unpause";
				$pause_action = 2;
				$icon_pause = "play.png";
			}
			echo '<h3><img border=0 src="img/setting.png">Basic actions</h3>';
			echo '
			<table>
			<tr>
				<th>'.$pause_unpause.'</th>
				<th>Stop</th>
			</tr>
			<tr>
				<td><a href="vm.php?vm='.$i.'&action='.$pause_action.'&dom0='.$this->domN.'"><img border=0 src="img/'.$icon_pause.'"></a></td>
				<td><a href="vm.php?vm='.$i.'&action=2&dom0='.$this->domN.'"><img border=0 src="img/stop.png"></a></td>
						</table></p>';/*
				echo '<td>';
				foreach ($other_domains as $val) {
					echo '<a href="vm.php?vm='.$i.'&action=3&dom0='.$this->domN.'&target='.$val.'"><img border=0 src="img/migrate.png">'.$val.'</a>';
					}
					
			echo '</td></tr>';*/
			echo '<h3><img border=0 src="img/migrate.png">Live Migration</h3>';
			echo '<table>
					  <tr>
						  <th>Target</th>
					  </tr>
				  ';
			if (count($other_domains)>0) {
				foreach ($other_domains as $val) {
					echo '<tr><td><a href="vm.php?vm='.$i.'&action=3&dom0='.$this->domN.'&target='.$val.'">'.$val.'</a></tr></td>';
				}
			}
			else {
				echo '<tr><td>No other Dom0\'s found !</tr></td>';
			}
			
			
				
			echo '</table></div>
		

		<br/><table>
			<caption>CPU Infos</caption>
			<tr>
				<th>Weight</th>
				<th>Cap</th>
				<th>VCPU at startup</th>
				<th>VCPU Max</th>
			</tr>
			<tr>
				<td> '.$array[4].'</td>
				<td> '.$array[5].'</td>
				<td> '.$array[12].'</td>
				<td> '.$array[13].'</td>
			</tr>
		</table>
		
		<br/><table>
			<caption>Memory Infos</caption>
			<tr>
				<th>Memory static max</th>
				<th>Memory static min</th>
				<th>Memory dynamic max</th>
				<th>Memory dynamic min</th>
			</tr>
			<tr>
				<td> '.$array[6].'</td>
				<td> '.$array[7].'</td>
				<td> '.$array[8].'</td>
				<td> '.$array[9].'</td>
			</tr>
		</table>
		
		<br/><table>
			<caption>Behavior</caption>
			<tr>
				<th>After shutdown</th>
				<th>After reboot</th>
				<th>After crash</th>
			</tr>
			<tr>
				<td> '.$array[14].'</td>
				<td> '.$array[15].'</td>
				<td> '.$array[16].'</td>
			</tr>
		</table>
		
		';
	}
	
	public function display_row_vm($i) {
		// displays rows for each VM
		$vm = unserialize($this->vm_table[$i]);
		$string = $vm->get_preview();
		$array = explode(',',$string);
		echo '';
		echo '<tr>';
		for($j=0; $j<count($array);$j++) {
			echo '<td>'.$array[$j].'</td>';
		}
		echo '<td><a href="vm.php?vm='.$i.'&dom0='.$this->domN.'"><img border=0 src="img/action.png"></a></td></tr> ';
	}
	
	public function display_table_all_vm() {
		
		echo '<br/><table>
			<caption>Dom0 '.$this->address.'</caption>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>State</th>
				<th>Kernel</th>
				<th>Edit</th>
			</tr>';

		for($i=1; $i<count($this->vm_table);$i++) {
			$this->display_row_vm($i);
		}
		echo '</table><br/>';
	}
	
	public function pause_vm($i) {
		$domU = unserialize($this->vm_table[$i]);
		$domU->pause();
	}
	
	public function unpause_vm($i) {
		$domU = unserialize($this->vm_table[$i]);
		$domU->unpause();
	}
	
	public function migrate_vm($i,$dest,$live) {
		$domU = unserialize($this->vm_table[$i]);
		$domU->migrate($dest,$live);
	}
	
	public function all_id_vm() {
	
	// display all detailed info of attached VM to this Dom0
		$this->list_id_vm = $this->handle->send("VM.get_all");
	}
	
	// to String
	
	public function __toString() {
		
		return "$this->address avec l'ID : $this->id";
	}
	

}
