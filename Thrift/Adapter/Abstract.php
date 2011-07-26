<?php

abstract class Lily_Thrift_Adapter_Abstract
{
	protected $name = '';
	protected $port = 9090;
	protected $hosts = array();
	protected $timeout_send = 2000;
	protected $timeout_receive = 2000;
	protected $accelerated = false;
	protected $verbose = false;
	protected $buffer_size = 8192;
	protected $max_retry = 3;
	
	protected $client;
	protected $socket;
	protected $transport;
	protected $protocol;
	private $connection_id;
	
	public function __construct($options=array()) {
		// Role Name of adapter
		if (isset($options['name'])) {
			$this->name = $options['name'];
		}
		
		if (isset($options['verbose'])) {
			$this->verbose = $options['verbose'];
		}
		
		// Host
		if (isset($options['host'])) {
			// Hosts
			if (is_array($options['host'])) {
				foreach ($options['host'] as $string) {
					$this->hosts = array_merge($this->hosts, $this->parseHosts($string));
				}
			} else {
				$this->hosts = $this->parseHosts($options['host']);
			}
		} else {
			throw new Lily_Config_Exception('thrift.role.$role.host');
		}
			
		// Port
		if (isset($options['port'])) {
			$this->port = $options['port'];
		}
		
		// Timeouts
		if (isset($options['timeout'])) {
			if (isset($options['timeout']['send'])) {
				$this->timeout_send = $options['timeout']['send'];
			}
			
			if (isset($options['timeout']['receive'])) {
				$this->timeout_receive = $options['timeout']['receive'];
			}
		}
		
		if (isset($options['buffer_size'])) {
			$this->buffer_size = $options['buffer_size'];
		}
		
		// User pre-compiled thrift
		if (isset($options['accelerated'])) {
			$this->accelerated = $options['accelerated'];
		}
		
		if (isset($options['max_retry'])) {
			$this->max_retry = $options['max_retry'];
		}
	}

	public function __call($name, $arguments) {
		if (null === $this->client) {
			$this->rotateConnection();
		}
		$result = null;
		$start = microtime(true);
		for ($i=0; $i<$this->max_retry; $i++) {
			try {
				if ($this->client === null) continue;
				$result = call_user_func_array(array($this->client, $name), $arguments);
				$end = microtime(true);
				$ellapsed = $end - $start;
				
				$log = sprintf(
					"%s, %s, ellapsed %s, %s",
					isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
					isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
					$ellapsed, 
					$name
				);

				$this->profile($log, array("Arguments:" => $arguments, "Result:" => $result));
				return $result;
			} catch (TTransportException $te) {
				$this->profile("TTransportException, rotating connection.",  $te);
				$this->rotateConnection();
				if ($i+1 == $this->max_retry) throw $te;
			} catch (Exception $e) {
				$this->profile("Unknown Exception, rotating connection.", $e);
				Lily_Log::error("$thrift.{$this->name} Connection caught unknown error.". $e->getTraceAsString(), $e->getMessage());
				if ($i+1 == $this->max_retry) throw $e;
				$this->rotateConnection();
			}
		}
		return $result;
	}
	
	private function parseHosts($string) {
		$parts = explode(',', $string);
		$result = array();
		foreach ($parts as $temp) {
			if ($pos = strpos($temp, ':')) {
				$host = substr($temp, 0, $pos);
				$port = substr($temp, $pos+1);
			} else  {
				$host = $temp;
				$port = null;
			}
			$result[] = array('host' => $host, 'port' => $port);
		}
		return $result;
	}
	
	protected function profile($message, $object=null) {
		if ($this->verbose) {
			Lily_Log::write("thrift_{$this->name}_profile", $message, $object);
		} else {
			Lily_Log::write("thrift_{$this->name}_profile", $message);
		}		
	}
	
	protected function rotateConnection() 
	{
		if ($this->client && count($this->hosts) <= 1) {
			return;
		}
		$this->closeConnection();
		
		for ($i=0; $i<$this->max_retry; $i++) {
			if (null === $this->connection_id) {
				// Randomize first connection! Dont want to rape the first host!
				$this->connection_id = rand(0, count($this->hosts)-1);
			} else {
				if (++$this->connection_id >= count($this->hosts)) {
					$this->connection_id = 0;
				}
			}
			
			try {
				$host = $this->hosts[$this->connection_id];
				$port = isset($host['port']) ? $host['port'] : $this->port;
				$this->openConnection($host['host'], $port);
				if ($this->client !== null) break;
			} catch (TException $te) {
				$this->closeConnection();
				$this->profile($te->getMessage());
				if ($i+1 == $this->max_retry) throw $te;
			}
		}
	}
	
	abstract protected function openConnection($host, $port);
	
	abstract protected function closeConnection();

}
