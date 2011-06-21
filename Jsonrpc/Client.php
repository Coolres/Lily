<?php


class Lily_Jsonrpc_Client
{
	private $_resource;
	
	public function __construct(Lily_Rpc_Resource_Abstract& $resource) {
		$this->_resource = $resource;
	}
	
	public function __call($method, $args) {
		Lily_Jsonrpc_Manager::$request_id++;
		$request = new Lily_Rpc_Request();
		
		$meta = $this->_resource->getMethodMeta($method);
		$request->setResource($this->_resource->getName())
			->setMethod($meta['method'])
			->setParams($args)
			->setPath($meta['path'])
			->setId(Lily_Jsonrpc_Manager::$request_id);
		$adapter = Lily_Jsonrpc_Manager::getAdapter($meta['role']);
		try {
			$response = $adapter->sendRequest($request);
		} catch (Exception $e) {
			Lily_Log::error("Lily_Jsonrpc_Client exception detected, `{$e->getMessage()}`,  when sending request:", $request);
			throw $e;
		}
		return $response->getResult();
	}
	
}
