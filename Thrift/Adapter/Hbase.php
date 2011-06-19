<?php

class Lily_Thrift_Adapter_Hbase extends Lily_Thrift_Adapter_Abstract
{
	
	
	public function __construct($options) {
		parent::__construct($options);
		
	}
	
	public function __destruct(){
		$this->closeConnection();
	}
	
	protected function openConnection($host, $port) {
		$this->socket = new TSocket($host, $port);
		$this->socket->setSendTimeout($this->timeout_send);
		$this->socket->setRecvTimeout($this->timeout_receive);
		
		$this->transport = new TBufferedTransport($this->socket, $this->buffer_size);
		if ($this->accelerated) {
			$this->protocol = new TBinaryProtocolAccelerated($this->transport);
		} else {
			$this->protocol = new TBinaryProtocol($this->transport);
		}
		$this->client = new HbaseClient($this->protocol);
		$this->transport->open();
	}
	
	protected function closeConnection() {
		if (null !== $this->client) {
			if ($this->transport->isOpen()) {
				$this->transport->close();
			}
			if ($this->socket->isOpen()) {
				$this->socket->close();
			}
			$this->transport = null;
			$this->socket = null;
			$this->protocol = null;
			$this->client = null;
		}
	}
	
	
}
