<?php

class MessengerJSON
{
	const ERR_NONE = 0;

	const ERR_UNKNOWN = 1;

	public function __construct($auto_send = false)
	{
		$this->auto_send = $auto_send;
	}

	public function __destruct()
	{
		if ($this->auto_send)
		{
			$this->send();
		}
	}

	public function &__get($name)
	{
		return $this->data[$name];
	}

	public function __isset($name)
	{
		isset($this->data[$name]);
	}

	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	public function __unset($name)
	{
		unset($this->data[$name]);
	}

	public function error($message, $code = self::ERR_UNKNOWN)
	{
		$this->error_code = $code;
		$this->error_msg = $message;
	}

	public function get()
	{
		$data = $this->data + array(
			'error_code' => $this->error_code,
			'error_message' => $this->error_msg
		);
		return json_encode($data);
	}

	public function send()
	{
		$this->auto_send = false;
		echo $this->get();
	}

	private $auto_send;

	private $data = array();

	private $error_msg = 'No error';

	private $error_code = self::ERR_NONE;
}
