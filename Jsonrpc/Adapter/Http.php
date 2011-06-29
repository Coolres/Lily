<?php

class Lily_Jsonrpc_Adapter_Http extends Lily_Jsonrpc_Adapter_Abstract
{
	protected $timeout = 5;
	protected $connect_timeout = 5;
	protected $host = ''; //Base url
	protected $key = null;
	protected $port = 80;
	protected $profile = false;
	protected $accept_encoding;
	protected $compress_output;
	
	public function __construct(array $options) {
		$this->setOptions($options);
	}
	
	public function setOptions(array $options) {
		if (isset($options['timeout'])) {
			$this->timeout = $options['timeout'];
		}
		
		if (isset($options['connect_timeout'])) {
			$this->connect_timeout = $options['connect_timeout'];
		}
		
		if (isset($options['host'])) {
			$this->host = $options['host'];
		}
		
		if (isset($options['port'])) {
			$this->port = $options['port'];
		}
		
		if (isset($options['pass'])) {
			$this->key = $options['pass'];
		}
		
		if (isset($options['profile'])) {
			$this->profile = $options['profile'];
		}
		
		if (isset($args['accept_encoding'])) {
			$this->accept_encoding = $args['accept_encoding'];
		}
		
		if (isset($args['compress_output'])) {
			$this->compress_output = $args['compress_output'];
		}
	}

	public function sendRequest(Lily_Rpc_Request& $request) {
		$resource = $request->getResource();
		if (is_null($resource)) {
			throw new Lily_Jsonrpc_Exception("No resource set, cannot send request");
		}
		
		$method = $request->getMethod();
        $params = $request->getParams();
        $url = $this->host . $request->getPath();
        if ($this->key) {
          array_unshift($params, $this->key);
        }

        $ch = curl_init();
		$opts = array(
			CURLOPT_URL				=> $url,
			//CURLOPT_PORT			=> $this->port, 
			CURLOPT_POSTFIELDS		=> $request->toJson(),
			CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_0,
			CURLOPT_RETURNTRANSFER	=> 1,
			CURLOPT_ENCODING		=> 'deflate, gzip',
			CURLOPT_HTTPHEADER		=> array(
	        	'Accept: application/json',
	        	'Accept-Charset: UTF-8',
	        	'Accept-Encoding: deflate, gzip',
	        	'X-DB-BundleHash: '.md5(serialize($request)),
	        	'Content-Type: application/json; charset=UTF-8',
	        	'Connection: close'
        	),
        	CURLOPT_TIMEOUT			=> $this->timeout,
        	CURLOPT_CONNECTTIMEOUT	=> $this->connect_timeout
        );
		curl_setopt_array($ch, $opts);
		if ($this->profile) {
    		Lily_Log::write($this->profile, " url: {$url} " . PHP_EOL . "method: {$method}" . PHP_EOL . "args: ", $params);
		}
		
		$return = curl_exec($ch);
		$info = curl_getinfo($ch);
		$request->setInfo($info);
		curl_close($ch);
		
		if ($this->profile) {
			Lily_Log::write($this->profile, '', $info);
        }
	
        if (!$return) {
			Lily_Log::error("$url "  . curl_error($ch), $request->toJson());
			throw new Lily_Jsonrpc_Exception(curl_error($ch), curl_errno($ch));
		}
		$object = json_decode($return);
		if (!is_object($object)) {
			throw new Lily_Jsonrpc_Exception("Could not decode specified response: $return ");
		}

		if (isset($object->error) && $object->error !== null) {
			throw new Lily_Jsonrpc_Exception("Request returned with an error, '{$object->error}'");
		}
		
		$response = new Lily_Rpc_Response();
		if (!isset($object->id)) {
			throw new Lily_Jsonrpc_Exception("Response id not specified: $response");
		}
		$response->setId($object->id);

		if (!isset($object->result)) {
			throw new Lily_Jsonrpc_Exception("Result not specified: $return");
		}
		
		// Inspect the result
		$result = $object->result;
		$class_name  = isset($object->result_class) ? $object->result_class : null;
		if ($class_name && class_exists($class_name)) {
			$temp = new $class_name();
			$temp->populate($result);
			$result = $temp;
		}
		
		$response->setResult($object->result);
		return $response;
	}

	public function readRequest() {
		$json = '';
		if (isset($_REQUEST['request'])) {
			$json = stripslashes($_REQUEST['request']);
		} else {
			$json = file_get_contents("php://input");
		}
		
		if (empty($json)) {
			throw new Lily_Jsonrpc_Exception("No request specified");
		}
		
		$object = json_decode($json);
		if (empty($object)) {
			throw new Lily_Jsonrpc_Exception("Could not decode request");
		}

		if (!isset($object->id)) {
			throw new Lily_Jsonrpc_Exception("Request Id not specified");
		}
		
		$request = new Lily_Rpc_Request();
		$request->setId($object->id);
		if (isset($object->resource)) {
			$request->setResource($object->resource);
		}
		
		if (!isset($object->method)) {
			throw new Lily_Jsonrpc_Exception("Method not specified");
		}
		$request->setMethod($object->method);

		if (isset($object->params)) {
			$request->setParams($object->params);
		}
		
		// Interpret a file directory as a resource
		if (is_null($request->getResource())) {
			$uri = $_SERVER['REQUEST_URI'];
			if ($pos = strpos($uri, '?')) {
				$uri = substr($uri, 0, $pos);
			}
			$uri = trim($uri, '/');
			$request->setResource(str_replace('/', '_',$uri));
		} 
		return $request;
	}
	
	
	public function sendResponse(Lily_Rpc_Response& $response) {
		$output = $response->toJson();
		if (null !== $this->accept_encoding) {
			if ($this->compress_output && in_array('gzip', explode(',', $this->accept_encoding))) {
				header('Content-Encoding: gzip');
				$output = gzencode($output);	
			}
		}
		
		header('Content-Type: application/json; charset=utf-8');
		echo $output;
	}
}
