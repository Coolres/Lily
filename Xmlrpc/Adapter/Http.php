<?php

class Lily_Xmlrpc_Adapter_Http extends Lily_Xmlrpc_Adapter_Abstract
{
	protected $timeout = 5;
	protected $connect_timeout = 5;
	protected $host = ''; //Base url
	protected $key = null;
	protected $port = 80;
	protected $profile = false;
	
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
	}
	

	public function sendRequest(Lily_Rpc_Request& $request) {
		$resource = $request->getResource();
		$method = $request->getMethod();
        $params = $request->getParams();
        $method = str_replace('_', '.', $method);
        $url = $this->host . $request->getPath();
        if ($this->key) {
          array_unshift($params, $this->key);
        }

        $request = xmlrpc_encode_request($method, $params);
        $ch = curl_init();
		
		$opts = array(
			CURLOPT_URL				=> $url,
			//CURLOPT_PORT			=> $this->port, 
			CURLOPT_POSTFIELDS		=> $request,
			CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_0,
			CURLOPT_RETURNTRANSFER	=> 1,
			CURLOPT_ENCODING		=> 'deflate, gzip',
			CURLOPT_HTTPHEADER		=> array(
	        	'Accept: */*',
	        	'Accept-Charset: UTF-8',
	        	'Accept-Encoding: deflate, gzip',
	        	'X-DB-BundleHash: '.md5(serialize($method).serialize($params)),
	        	'Content-Type: text/xml; charset=UTF-8',
	        	'Connection: close'
        	),
        	CURLOPT_TIMEOUT			=> $this->timeout,
        	CURLOPT_CONNECTTIMEOUT	=> $this->connect_timeout
        );
		
		if ($this->profile) {
    		Lily_Log::write($this->profile, " url: {$url} " . PHP_EOL . "method: {$method}" . PHP_EOL . "args: ", $params);
		}
		
		curl_setopt_array($ch, $opts);
        $return = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
        if ($this->profile) {
            $info = curl_getinfo($ch);
            $log_entry = Array(
                $_SERVER["HTTP_HOST"],
                $_SERVER["REQUEST_URI"],
                $url,
                curl_getinfo($ch, CURLINFO_HTTP_CODE),
                curl_getinfo($ch, CURLINFO_TOTAL_TIME),
                curl_getinfo($ch, CURLINFO_CONNECT_TIME),
                curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME),
                curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME),
                curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD),
                curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD),
            );
			Lily_Log::write($this->profile, '', $log_entry);
        }
        curl_close($ch);
        
        switch ($http_code) {
        	case 0:
      		case 200:
      			// All's good
      			break;
      			
      		case 404:
      			throw new Lily_Xmlrpc_Exception_Fault(
      				"Specified method could not be found." . PHP_EOL .
      				"url: {$handle['url']} " . PHP_EOL . "method: {$method}", 404);
      			break;
      	
      		default:
      			throw new Lily_Xmlrpc_Exception_Fault(
      				"XMLRPC Server responded with a non sucessful http code." . PHP_EOL .
      				"url: {$handle['url']} " . PHP_EOL . "method: {$method}", $http_code);
      			break;
      		
        }
		$response = new Lily_Rpc_Response();
		$response->setResult(xmlrpc_decode($return));
		return $response;

 		// //$str = mb_convert_encoding($return, "UTF-8", "UTF-8" );
        // $r = xmlrpc_decode($str);
        // if (!is_array($r)) {
        	// //throw new XMLRPC_Exception_Fault("XMLRPC did not return valid response. Does function exist?", 0);
        // }
// 
        // if (is_array($r) && xmlrpc_is_fault($r)) {
            // throw new Lily_Xmlrpc_Exception_Fault($r['faultString'], $r['faultCode']);
        // } 
		// return $r;
	}
	
	
	public function sendResponse(Lily_Rpc_Response& $response) {
		// TODO
	}
}
