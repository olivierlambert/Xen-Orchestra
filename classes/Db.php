<?php

// DB CLASS
// create an SQLite Database
class Db {

	public $db_name;
	
	public function __construct($db_name) {
		
		$this->db_name = "database/".$db_name;
		
		if ($db = @sqlite_open($this->db_name, 0666, $sqliteerror)) 
		{
			// FLUSH tables
			@sqlite_query($db,'DROP TABLE vm');
			@sqlite_query($db,'DROP TABLE vif');
			@sqlite_query($db,'DROP TABLE network');
			@sqlite_query($db,'DROP TABLE metrics');
			@sqlite_query($db,'DROP TABLE dom0');
			
			// CREATE tables
			sqlite_query($db,'CREATE TABLE dom0 (object text)');
			
			@sqlite_query($db,'CREATE TABLE network (id varchar(128), 
			label varchar(256),
			descr varchar(256),
			vifs varchar(1024),
			default_gateway varchar(128),
			default_netmask varchar(128))');
			
			@sqlite_query($db,'CREATE TABLE vif (id varchar(128), 
			network varchar(256),
			vm varchar(256),
			mac varchar(128),
			currently_attached varchar(256),
			metrics varchar(1024))');
			
			@sqlite_query($db,'CREATE TABLE vm (id varchar(128),
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
			
			@sqlite_query($db,'CREATE TABLE metrics (id varchar(128),
			name varchar(256),
			memory int,
			num_vcpu int,
			use_vcpu int,
			vcpu int,
			flags varchar(128),
			start_time varchar(256),
			last_updated varchar(256))');
		}
		else 
		{
			throw new Exception('Database creation error.');
		}
	}
	
	
}
