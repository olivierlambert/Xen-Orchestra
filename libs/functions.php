<?php

// connect to the RPC server XEN
function connect($dom0,$port,$user,$pass)
{
	
	$method = "session.login_with_password";
	$params = array ($user,$pass);
	$request = xmlrpc_encode_request($method,$params);
	$context = stream_context_create(array('http' => array(
		'method' => "POST",
		'header' => "Content-Type: text/xml",
		'content' => $request
	)));
	
	$file = file_get_contents("http://".$dom0.":".$port, false, $context);
	$response = xmlrpc_decode($file);
	//fclose($file);
	if (xmlrpc_is_fault($response)) 
	{
		trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
	} 
	else 
	{
		//$db = @create_db ("vm");
		
		//print_r($response);
		//$return = current($response);
		//return $return;
		$id = $response['Value'];
		return $id;
	}
		// display_dom0($id);
		
		
		/*
		$result_vm = list_vm($id);
		$result_network = list_network($id);
		$result_vif = list_vif($id);
		
		// Direct Table access query
		echo '<table>
		<tr>
			<td>Nom</td>
			<td>ID</td>
			<td>Etat</td>
			<td>Kernel</td>
			<td>Poids CPU</td>
			<td>Part CPU</td>
		</tr>';
		foreach ($result_vm as $val)
		{
			echo '
			<tr>
			<td>'.$val[1].'</td>
			<td>'.$val[20].'</td>
			<td>'.$val[3].'</td>
			<td>'.$val[18].'</td>
			<td>'.$val[10]['weight'].'</td>
			<td>'.$val[10]['cap'].'</td>
			</tr>';
		}
		echo '</table>';
			*/
		
		// DB storing and query
		/*
		populate_db($db,$result);
		$query = sqlite_query($db,"SELECT * FROM vm ;");
		
		echo '<table>
				  <tr>
					  <td>Nom</td>
					  <td>ID</td>
					  <td>Etat</td>
					  <td>Kernel</td>
				  </tr>';
			  
		while ($entry = sqlite_fetch_array($query, SQLITE_NUM))
		{
			echo '
			<tr>
			<td>'.$entry[1].'</td>
			<td>'.$entry[20].'</td>
			<td>'.$entry[3].'</td>
			<td>'.$entry[18].'</td>
			</tr>';
		}
		echo '</table>';
		*/

		/*
		 * id,name,descr,power_state,autopower,
		suspend,mem_stat_max,mem_stat_min,mem_dyn_max,mem_dyn_min,vcpu_param,
		vcpu_max,vcpu_start,act_shut,act_reboot,act_crash,vifs,vbds,kernel,platform,domid)
		 * 
		 * */
		//create_db ("vm");
		
		/*
		//echo $id;
		//$method = "host.get_resident_VMs";
		$method = "VM.get_name_label";
		$params = array ($id,"e785e146-f559-1199-da0d-52d5d7b75ada");
		$request = xmlrpc_encode_request($method,$params);
		$context = stream_context_create(array('http' => array(
		'method' => "POST",
		'header' => "Content-Type: text/xml",
		'content' => $request
		)));
		$file = file_get_contents("http://".$dom0.":".$port, false, $context);
		$response2 = xmlrpc_decode($file);
		print_r($response2);*/
}


function display_dom0($id,$dom0)
{

	$result_vm = list_vm($id,$dom0);
	$result_network = list_network($id,$dom0);
	$result_vif = list_vif($id,$dom0);

	// Direct Table access query
	echo '<br/><table>
	<caption>Dom0 '.$dom0.'</caption>
	<tr>
		<th>Nom</th>
		<th>ID</th>
		<th>Etat</th>
		<th>Kernel</th>
		<th>Poids CPU</th>
		<th>Part CPU</th>
		<th>Static MAX memory</th>
		<th>Dynamic MAX memory</th>
		<th>Static MIN memory</th>
		<th>Dynamic MIN memory</th>
	</tr>';
	foreach ($result_vm as $val)
	{
		for ($i=6;$i<10;$i++)
		{
			if ($val[$i]>=1024)
			{ $val[$i] = $val[$i]/1024 ." Go"; }
			else
			{ $val[$i] = $val[$i] ." Mo"; }
		}
		echo '
		<tr>
		<td>'.$val[1].'</td>
		<td>'.$val[20].'</td>
		<td>'.$val[3].'</td>
		<td>'.$val[18].'</td>
		<td>'.$val[10]['weight'].'</td>
		<td>'.$val[10]['cap'].'</td>
		<td>'.$val[6].' </td>
		<td>'.$val[7].' </td>
		<td>'.$val[8].' </td>
		<td>'.$val[9].' </td>
		</tr>';
	}
	echo '</table><br/>';
	
	return 0;
}

function display_domU($id,$dom0,$id_vm)
{
	$params = array($id,$id_vm);

	// get label request
	$get_name = send($dom0,$id,"VM.get_name_label",$params);
	$label = $get_name['Value'];
	// get power state request
	$get_power_state = send($dom0,$id,"VM.get_power_state",$params);
	$power_state = $get_power_state['Value'];
	// get name description
	$get_name_description = send($dom0,$id,"VM.get_name_description",$params);
	$description = $get_name_description['Value'];
	// get autopower on
	$get_auto_power_on = send($dom0,$id,"VM.get_auto_power_on",$params);
	$autopower = $get_auto_power_on['Value'];
	// get suspend VDI
	$get_suspend_vdi = send($dom0,$id,"VM.get_suspend_VDI",$params);
	$suspend_vdi = $get_suspend_vdi['Value'];
	// get memory static max
	$get_memory_static_max = send($dom0,$id,"VM.get_memory_static_max",$params);
	$memory_static_max = $get_memory_static_max['Value']/(1024*1024); //put in Meg
	// get memory dynamic max
	$get_memory_dynamic_max = send($dom0,$id,"VM.get_memory_dynamic_max",$params);
	$memory_dynamic_max = $get_memory_dynamic_max['Value']/(1024*1024); //put in Meg
	// get memory static min
	$get_memory_static_min = send($dom0,$id,"VM.get_memory_static_min",$params);
	$memory_static_min = $get_memory_static_min['Value']/(1024*1024); //put in Meg
	// get memory dynamic min
	$get_memory_dynamic_min = send($dom0,$id,"VM.get_memory_dynamic_min",$params);
	$memory_dynamic_min = $get_memory_dynamic_min['Value']/(1024*1024); //put in Meg
	// get VCPU params
	$get_vcpus_params = send($dom0,$id,"VM.get_VCPUs_params",$params);
	$vcpus_params = $get_vcpus_params['Value'];
	// get VCPU max
	$get_vcpus_max = send($dom0,$id,"VM.get_VCPUs_max",$params);
	$vcpus_max = $get_vcpus_max['Value'];
	// get VCPU at boot
	$get_vcpus_at_startup = send($dom0,$id,"VM.get_VCPUs_at_startup",$params);
	$vcpus_at_startup = $get_vcpus_at_startup['Value'];
	// get actions after shutdown
	$get_actions_after_shutdown = send($dom0,$id,"VM.get_actions_after_shutdown",$params);
	$actions_after_shutdown = $get_actions_after_shutdown['Value'];
	// get actions after reboot
	$get_actions_after_reboot = send($dom0,$id,"VM.get_actions_after_reboot",$params);
	$actions_after_reboot = $get_actions_after_reboot['Value'];
	// get actions after crash
	$get_actions_after_crash = send($dom0,$id,"VM.get_actions_after_crash",$params);
	$actions_after_crash = $get_actions_after_crash['Value'];
	// get consoles (array of ID of consoles)
	// $get_consoles = send($id,"VM.get_consoles",$params);
	// $consoles = $get_consoles['Value'];
	// get vifs (array of ID of Interfaces)
	$get_vifs = send($dom0,$id,"VM.get_VIFs",$params);
	$vifs = $get_vifs['Value'];
	// get VBD (array of ID of VBDs)
	$get_vbds = send($dom0,$id,"VM.get_VBDs",$params);
	$vbds = $get_vbds['Value'];
	// get PV kernel
	$get_pv_kernel = send($dom0,$id,"VM.get_PV_kernel",$params);
	$pv_kernel = substr($get_pv_kernel['Value'],14);
	// get platform
	$get_platform = send($dom0,$id,"VM.get_platform",$params);
	$platform = $get_platform['Value'];
	// get domid
	$get_domid = send($dom0,$id,"VM.get_domid",$params);
	$domid = $get_domid['Value'];


	// Super table with all data on VM and Dom0
	$stable = array(
	$id_vm,
	$label,
	$description,
	$power_state,
	$autopower,
	$suspend_vdi,
	$memory_static_max,
	$memory_dynamic_max,
	$memory_static_min,
	$memory_dynamic_min,
	$vcpus_params,
	$vcpus_max,
	$vcpus_at_startup,
	$actions_after_shutdown,
	$actions_after_reboot,
	$actions_after_crash,
	$vifs,$vbds,
	$pv_kernel,
	$platform,
	$domid);

	$i++;
	}
	// Direct Table access query
	echo '<br/><table>
	<caption>Dom0 '.$stable[1].'</caption>
	<tr>
		<th>Nom</th>
		<th>ID</th>
		<th>Etat</th>
		<th>Kernel</th>
		<th>Poids CPU</th>
		<th>Part CPU</th>
		<th>Static MAX memory</th>
		<th>Dynamic MAX memory</th>
		<th>Static MIN memory</th>
		<th>Dynamic MIN memory</th>
	</tr>';
	//print_r($stable);
	//print_r ($state);
	for ($i=6;$i<10;$i++)
	{
		if ($stable[$i]>=1024)
		{ $stable[$i] = $stable[$i]/1024 ." Go"; }
		else
		{ $stable[$i] = $stable[$i] ." Mo"; }
	}
	echo '
	<tr>
	<td>'.$stable[1].'</td>
	<td>'.$stable[20].'</td>
	<td>'.$stable[3].'</td>
	<td>'.$stable[18].'</td>
	<td>'.$stable[10]['weight'].'</td>
	<td>'.$stable[10]['cap'].'</td>
	<td>'.$stable[6].' </td>
	<td>'.$stable[7].' </td>
	<td>'.$stable[8].' </td>
	<td>'.$stable[9].' </td>
	</tr>';
}
echo '</table><br/>';
	return 0;
}


/*		$memory_static_max,
		$memory_dynamic_max,
		$memory_static_min,
		$memory_dynamic_min,*/
function list_network($id,$dom0)
{
	$network_list = send($dom0,$id,"network.get_all",$id);
	foreach($network_list['Value'] as $id_network)
	{
		$params = array($id,$id_network);
		$get_name[$i] = send($dom0,$id,"network.get_name_label",$params);
		$label = $get_name[$i]['Value'];
		$get_name_description[$i] = send($dom0,$id,"network.get_name_description",$params);
		$description = $get_name_description[$i]['Value'];
		$get_vifs[$i] = send($dom0,$id,"network.get_VIFs",$params);
		$vifs = $get_vifs[$i]['Value'];
		//$vifs = implode(",",$get_vifs[$i]['Value']);
		$get_default_gateway[$i] = send($dom0,$id,"network.get_default_gateway",$params);
		$default_gateway = $get_default_gateway[$i]['Value'];
		$get_default_netmask[$i] = send($dom0,$id,"network.get_default_netmask",$params);
		$default_netmask = $get_default_netmask[$i]['Value'];
		
		// Super table with all data on VM and Dom0
		$stable[$i] = array($id_network,$label,$description,$vifs,$default_gateway,
		$default_netmask);
		$i++;
	}
	return $stable;
}

function list_vif($id,$dom0)
{
	$vif_list = send($dom0,$id,"VIF.get_all",$id);
	//print_r($vif_list);
	foreach($vif_list['Value'] as $id_vif)
	{
		$params = array($id,$id_vif);
		$get_device[$i] = send($dom0,$id,"VIF.get_device",$params);
		$device = $get_device[$i]['Value'];
		$get_network[$i] = send($dom0,$id,"VIF.get_network",$params);
		$network = $get_network[$i]['Value'];
		$get_vm[$i] = send($dom0,$id,"VIF.get_VM",$params);
		$vm = $get_vm[$i]['Value'];
		$get_mac[$i] = send($dom0,$id,"VIF.get_MAC",$params);
		$mac = $get_mac[$i]['Value'];
		$get_currently_attached[$i] = send($dom0,$id,"VIF.get_currently_attached",$params);
		$currently_attached = $get_currently_attached[$i]['Value'];
		$get_metrics[$i] = send($dom0,$id,"VIF.get_metrics",$params);
		$metrics = $get_metrics[$i]['Value'];
		
		// Super table with all data on VM and Dom0
		$stable[$i] = array($device,$network,$vm,$mac,$currently_attached,$metrics);
		$i++;
	}
	return $stable;
}

function list_metrics($id,$dom0)
{
	/*
	$metrics_list = send($id,"VM_guest_metrics.get_all",$id);
	print_r($metrics_list);
	$vm = $metrics_list['Value'][0];
	$params = array($id,$vm);
	$vm_metrics = send($id,"VM_guest_metrics.get_memory",$params);
	print_r($vm_metrics);
	$ram = $vm_metrics['Value']/(1024*1024);
	echo $ram;
	return $metrics_list;*/
	$method_list = send($dom0,$id,"host_metrics.get_memory_total",$id);
	return $method_list;
}
// List VM in a big table
function list_vm($id,$dom0)
{
	// SETTER FUNCTION TO GATHER ALL DATA ON VM
	// TODO : get_user_version // get_resident_on
	$vm_list = send($dom0,$id,"VM.get_all",$id);

	foreach($vm_list['Value'] as $id_vm)
	{
		// ID of session and ID of current VM 
		$params = array($id,$id_vm);
		
		// get label request
		$get_name[$i] = send($dom0,$id,"VM.get_name_label",$params);
		$label = $get_name[$i]['Value'];
		// get power state request
		$get_power_state[$i] = send($dom0,$id,"VM.get_power_state",$params);
		$power_state = $get_power_state[$i]['Value'];
		// get name description
		$get_name_description[$i] = send($dom0,$id,"VM.get_name_description",$params);
		$description = $get_name_description[$i]['Value'];
		// get autopower on
		$get_auto_power_on[$i] = send($dom0,$id,"VM.get_auto_power_on",$params);
		$autopower = $get_auto_power_on[$i]['Value'];
		// get suspend VDI
		$get_suspend_vdi[$i] = send($dom0,$id,"VM.get_suspend_VDI",$params);
		$suspend_vdi = $get_suspend_vdi[$i]['Value'];
		// get memory static max
		$get_memory_static_max[$i] = send($dom0,$id,"VM.get_memory_static_max",$params);
		$memory_static_max = $get_memory_static_max[$i]['Value']/(1024*1024); //put in Meg
		// get memory dynamic max
		$get_memory_dynamic_max[$i] = send($dom0,$id,"VM.get_memory_dynamic_max",$params);
		$memory_dynamic_max = $get_memory_dynamic_max[$i]['Value']/(1024*1024); //put in Meg
		// get memory static min
		$get_memory_static_min[$i] = send($dom0,$id,"VM.get_memory_static_min",$params);
		$memory_static_min = $get_memory_static_min[$i]['Value']/(1024*1024); //put in Meg
		// get memory dynamic min
		$get_memory_dynamic_min[$i] = send($dom0,$id,"VM.get_memory_dynamic_min",$params);
		$memory_dynamic_min = $get_memory_dynamic_min[$i]['Value']/(1024*1024); //put in Meg
		// get VCPU params
		$get_vcpus_params[$i] = send($dom0,$id,"VM.get_VCPUs_params",$params);
		$vcpus_params = $get_vcpus_params[$i]['Value'];
		// get VCPU max
		$get_vcpus_max[$i] = send($dom0,$id,"VM.get_VCPUs_max",$params);
		$vcpus_max = $get_vcpus_max[$i]['Value'];
		// get VCPU at boot
		$get_vcpus_at_startup[$i] = send($dom0,$id,"VM.get_VCPUs_at_startup",$params);
		$vcpus_at_startup = $get_vcpus_at_startup[$i]['Value'];
		// get actions after shutdown
		$get_actions_after_shutdown[$i] = send($dom0,$id,"VM.get_actions_after_shutdown",$params);
		$actions_after_shutdown = $get_actions_after_shutdown[$i]['Value'];
		// get actions after reboot
		$get_actions_after_reboot[$i] = send($dom0,$id,"VM.get_actions_after_reboot",$params);
		$actions_after_reboot = $get_actions_after_reboot[$i]['Value'];
		// get actions after crash
		$get_actions_after_crash[$i] = send($dom0,$id,"VM.get_actions_after_crash",$params);
		$actions_after_crash = $get_actions_after_crash[$i]['Value'];
		// get consoles (array of ID of consoles)
		// $get_consoles[$i] = send($id,"VM.get_consoles",$params);
		// $consoles = $get_consoles[$i]['Value'];
		// get vifs (array of ID of Interfaces)
		$get_vifs[$i] = send($dom0,$id,"VM.get_VIFs",$params);
		$vifs = $get_vifs[$i]['Value'];
		// get VBD (array of ID of VBDs)
		$get_vbds[$i] = send($dom0,$id,"VM.get_VBDs",$params);
		$vbds = $get_vbds[$i]['Value'];
		// get PV kernel
		$get_pv_kernel[$i] = send($dom0,$id,"VM.get_PV_kernel",$params);
		$pv_kernel = substr($get_pv_kernel[$i]['Value'],14);
		// get platform
		$get_platform[$i] = send($dom0,$id,"VM.get_platform",$params);
		$platform = $get_platform[$i]['Value'];
		// get domid
		$get_domid[$i] = send($dom0,$id,"VM.get_domid",$params);
		$domid = $get_domid[$i]['Value'];


		// Super table with all data on VM and Dom0
		$stable[$i] = array(
		$id_vm,
		$label,
		$description,
		$power_state,
		$autopower,
		$suspend_vdi,
		$memory_static_max,
		$memory_dynamic_max,
		$memory_static_min,
		$memory_dynamic_min,
		$vcpus_params,
		$vcpus_max,
		$vcpus_at_startup,
		$actions_after_shutdown,
		$actions_after_reboot,
		$actions_after_crash,
		$vifs,$vbds,
		$pv_kernel,
		$platform,
		$domid);
		
		$i++;
	}
	//print_r($stable);
	//print_r ($state);
	return $stable;
}



// At first call, create an SQLite Database
function create_db ($db_name)
{
	if ($db = sqlite_open($db_name, 0666, $sqliteerror)) 
	{
		// net $id_network,$label,$description,$vifs,$default_gateway,$default_netmask
		// vif $device,$network,$vm,$mac,$currently_attached,$metrics
		sqlite_query($db,'DROP TABLE vm');
		sqlite_query($db,'DROP TABLE vif');
		sqlite_query($db,'DROP TABLE network');
		
		sqlite_query($db,'CREATE TABLE network (id varchar(128), 
		label varchar(256),
		descr varchar(256),
		vifs varchar(1024),
		default_gateway varchar(128),
		default_netmask varchar(128))');
		
		sqlite_query($db,'CREATE TABLE vif (id varchar(128), 
		network varchar(256),
		vm varchar(256),
		mac varchar(128),
		currently_attached varchar(256),
		metrics varchar(1024))');
		
		sqlite_query($db,'CREATE TABLE vm (id varchar(128),
		name varchar(256),
		descr varchar(1024),
		power_state varchar(256),
		autopower varchar(128),
		suspend varchar(128),
		mem_stat_max int,
		mem_stat_min int,
		mem_dyn_min int,
		mem_dyn_max int,
		vcpu_param varchar(256),
		vcpu_max int,
		vcpu_start int,
		act_shut varchar(256),
		act_reboot varchar(256),
		act_crash varchar(256),
		vifs varchar(1024),
		vbds varchar(1024),
		kernel varchar(256),
		platform varchar(128),
		domid int)');
		sqlite_query($db,'CREATE TABLE metrics (id varchar(128),
		name varchar(256),
		memory int,
		num_vcpu int,
		use_vcpu int,
		vcpu int,
		flags varchar(128),
		start_time varchar(256),
		last_updated varchar(256))');
		$result = sqlite_query($db,'select * from vm');
		return $db;
	}
	else 
	{
		die ($sqliteerror);
		return 1;
	}
}

// Populate Database with detected VM
function populate_db ($db,$vm_table)
{
	// flush table
	sqlite_query($db,"DELETE FROM vm");
	// populate table
	//print_r ($vm_table);
	foreach($vm_table as $val)
	{
		//$key = key($val);
		//$name = current($val);
		//echo "TOTO".$val[0];
		//echo "ID : ".$key." // Nom :".$name."<br/>";
		$query = "INSERT INTO vm (id,name,descr,power_state,autopower,
		suspend,mem_stat_max,mem_stat_min,mem_dyn_max,mem_dyn_min,vcpu_param,
		vcpu_max,vcpu_start,act_shut,act_reboot,act_crash,vifs,vbds,kernel,platform,domid)
		VALUES ('$val[0]','$val[1]','$val[2]','$val[3]','$val[4]','$val[5]',
		'$val[6]','$val[7]','$val[8]','$val[9]','$val[10]','$val[11]','$val[12]',
		'$val[13]','$val[14]','$val[15]','$val[16]','$val[17]','$val[18]','$val[19]',
		'$val[20]')";
		sqlite_query($db,$query);
	}
	// TO DO : put all discover VM in database
	/*,descr,power_state,autopower,
		suspend,mem_stat_max,mem_stat_min,mem_dyn_max,mem_dyn_min,vcpu_param,
		vcpu_max,vcpu_start,act_shut,act_reboot,act_crash,vifs,vbds,kernel,platform,domid)*/
	
}

function send ($dom0,$session,$method,$params)
{
	global $port;
	$request = xmlrpc_encode_request($method,$params);
	$context = stream_context_create(array('http' => array(
	'method' => "POST",
	'header' => "Content-Type: text/xml",
	'content' => $request
	)));
	$file = file_get_contents("http://".$dom0.":".$port, false, $context);
	$response = xmlrpc_decode($file);
	return $response;
	
}

