<?php

class Rpc {
	
	public $id;
	public $address;
	public $port;
	public $params;
	public $method;
	public $response;
	
	public function __construct($address,$port,$id) {
		
		$this->address = $address;
		$this->port = $port;
		$this->id = $id;
	}
	
	public function send ($method,$params=null)
	{
		$this->method = $method;
		if ($params==null) $this->params = $this->id;
		else {
			//$table = explode(",",$params);
			//print_r($params);
			$this->params = array_merge((array)$this->id,(array)$params);
		}
		
		$request = xmlrpc_encode_request($method,$this->params);
		$context = stream_context_create(array('http' => array(
		'method' => "POST",
		'header' => "Content-Type: text/xml",
		'content' => $request
		)));
		$file = file_get_contents("http://".$this->address.":".$this->port, false, $context);
		$response = xmlrpc_decode($file);

		if ($response['Status'] == "Success") {
			return $this->response = $response['Value'];
		}
		elseif ($response['Status'] == "Failure") {
			return $this->response = $response['ErrorDescription'];
		}
	}
	
	public function __toString() {
		
		return "$this->response";
	}
}
