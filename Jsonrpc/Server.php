<?php


class Lily_Jsonrpc_Server
{
	private $adapter;
	
	public function __construct(Lily_Jsonrpc_Adapter_Abstract $adapter) {
		$this->adapter = $adapter;
	}
	
	public function run() {
		$response = new Lily_Rpc_Response();
		try {
			$request = $this->adapter->readRequest();
			Lily_Log::write("jsonrpc", "Request is ", $request);
			$resource = Lily_Jsonrpc_Manager::getResource($request->getResource());
			if ($resource === null) {
				throw new Lily_Jsonrpc_Exception("Specified resource, '{$request->getResource()}', is not a valid resource");
			}
			
			$meta = $resource->getMethodMeta($request->getMethod());
			if ($meta === null) {
				throw new Lily_Jsonrpc_Exception("Method, '{$request->getMethod()}', doesn't exist or cannot be found in registered methods.");
			}
			$method = isset($meta['method']) ? $meta['method'] : $request->getMethod();
			
	
			try{
				$reflection_method = new ReflectionMethod($resource, $method);	
			} catch (ReflectionException $e) {
				$error_message = "Method, '{$method}', doesn't exist or cannot be found in resource scope.";
				throw new Lily_Jsonrpc_Exception($error_message); 
			}
			
			// Try to invoke the method, if there's a reflection error its because of function visibility
			try {
				$params = $request->getParams();
				$num_req_params = $reflection_method->getNumberOfRequiredParameters();
				$method_params = $reflection_method->getParameters();
				if ($num_req_params > count($params)) {
					$error_message = "Method requires $num_req_params parameter(s), " 
						. count($params) . " provided. " . implode(', ', $method_params);
					throw new Lily_Jsonrpc_Exception($error_message);
				}
				
				if (empty($params)) {
					$result = $reflection_method->invoke($resource);
				} else {
					$result = $reflection_method->invokeArgs($resource, $params);
				}
			} catch (ReflectionException $e) {
				$error_message = "Method, '{$method}', cannot be called from public context.";
				throw new Lily_Jsonrpc_Exception($e->getMessage());
			}
	
			$response->setResult($result)
				->setId($request->getId());
		} catch (Exception $e) {
			Lily_Log::error($e->getMessage(), $e);
			$response = new Lily_Rpc_Response();
			$response->setResult(null)
				->setError($e->getMessage())
				->setId(null === $request ? -1 : $request->getId());
		}
		$this->adapter->sendResponse($response);
	}
}
