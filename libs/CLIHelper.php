<?php

/**
 * TODO: write doc
 */
class CLIHelper
{
	public function prompt($prompt)
	{
		$this->write($prompt);
		return $this->read();
	}

	public function read($file_handle = STDIN)
	{
		return rtrim(fgets($file_handle), PHP_EOL);
	}

	public function write($message, $file_handle = STDOUT)
	{
		fwrite($file_handle, $message);
	}

	public function writeln($message = '', $file_handle = STDOUT)
	{
		$this->write($message . PHP_EOL);
	}
}

