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
			sqlite_query($db,'CREATE TABLE dom0 (id int, object text)');
			
			@sqlite_query($db,'CREATE TABLE network (id int, object text)');
			
			@sqlite_query($db,'CREATE TABLE vif (id int, object text');
			
			@sqlite_query($db,'CREATE TABLE vm (id int, object text');
			
			@sqlite_query($db,'CREATE TABLE metrics (id int, object text');
			
			return $db;
		}
		else 
		{
			throw new Exception('Database creation error.');
		}
	}
	
	public function update_engine($dom01,$i) {
		
		$query = $db->query("SELECT object FROM dom0 WHERE id = $i");
		$result = $query->fetchSingle();
		$dom02 = unserialize($result);
		
		// if there is already the Dom0 in database, we need to update the whole thing
		if ($dom01->address == $dom02->address) {
			$db->query("UPDATE dom0 SET object='$domobject' WHERE id='$i'");
		}
		else {
			$db->query("INSERT INTO dom0 (id,object) VALUES ('$i','$domobject')");
		}
	}
	
	
}
