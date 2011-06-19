<?php 

class Xmlrpc_Client
{
	public static $request_id = 0;
	private $_adapter;
	private $_resource;
	
	public function __construct(Xmlrpc_Adapter_Abstract& $adapter, 
								Rpc_Resource_Abstract& $resource) {
		$this->_adapter = $adapter;
		$this->_resource = $resource;
	}
	
	public function __call($method, $args) {
		self::$request_id++;
		$request = new Rpc_Request();
		
		$meta = $this->_resource->getMeta($method);
		
		$request->setResource($meta['resource'])
			->setMethod($method)
			->setParams($params)
			->setId(self::$request_id);
		try {
			$response = $this->_adapter->sendRequest($request);
		} catch (Exception $e) {
			Lily_Log::error("Xmlrpc Client exception detected, `{$e->getMessage()}`,  when sending request:", $request);
			throw $e;
		}
		return $response->getResult();
	}
}
