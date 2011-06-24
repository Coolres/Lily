<?php

abstract class Lily_Database_Adapter_Abstract
{	
	protected $connection;
	
	protected $host;
	protected $username;
	protected $password;
	protected $database;
	
	public function __construct($options) {
		if (isset($options['host'])) {
			$this->host = $options['host'];
		}
		
		if (isset($options['username'])) {
			$this->username = $options['username'];
		} elseif (isset($options['user'])) {
			$this->username = $options['user'];
		}
		
		if (isset($options['password'])) {
			$this->password = $options['password'];
		} elseif (isset($options['pass'])) {
			$this->password = $options['pass'];
		}
		
		if (isset($options['database'])) {
			$this->database = $options['database'];
		}
	}
	
	protected function getConnection() {
		if (null === $this->connection) {
			$this->connect();
		}
		return $this->connection;
	}


	abstract protected function connect();

	abstract public function getFoundRows();

	abstract public function getAffectedRows();

	abstract public function query($query);

	abstract public function update($query);

	abstract public function insert($query);

	abstract public function selectDatabase($database);
	
	abstract public function escapeString($string);

	abstract public function escapeInt($string);

	abstract public function getErrorMessage();

	abstract public function getErrorCode();

	public function __call($name, $arguments) {
		$connection = $this->getConnection();

		$result = null;
		$start = microtime(true);
		$args = func_get_args();
		$result = call_user_func_array(array($connection, $name), $arguments);
		$end = microtime(true);
		$diff = $end - $start;
		return $result;
	}
}