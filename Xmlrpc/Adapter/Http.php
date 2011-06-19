<?php

class Lily_Xmlrpc_Adapter_Http extends Lily_Xmlrpc_Adapter_Abstract
{
	protected $timeout = 5;
	protected $connect_timeout = 5;
	protected $host = ''; //Base url
	protected $key = null;
	
	public function __construct(array $options) {
		if (isset($options['timeout'])) {
			$this->timeout = $options['timeout'];
		}
		
		if (isset($options['connect_timeout'])) {
			$this->connect_timeout = $options['connect_timeout'];
		}
		
		if (isset($options['host'])) {
			$this->host = $options['host'];
		}
		
		if (isset($options['key'])) {
			$this->key = $options['key'];
		}
	}
	

	public function sendRequest(Rpc_Request& $request) {
		$resource = $request->getResource();
		$meta = $resource->getMeta($request->getMethod());
		if (!isset($meta['role'])) {
			
		}
		$role_info = $this->getRole($meta['role']);
		

        $method = str_replace('_', '.', $method);
        if ($handle['key']) {
          array_unshift($params, $handle['key']);
        }

        $request = xmlrpc_encode_request($method, $params);
        $ch = curl_init();
		
		$opts = array(
			CURLOPT_URL				=> $server,
			CURLOPT_POSTFIELDS		=> $request->toJson(),
			CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_0,
			CURLOPT_RETURNTRANSFER	=> 1,
			CURLOPT_ENCODING		=> 'deflate, gzip',
			CURLOPT_HTTPHEADER		=> array(
	        	'X-DB-BundleHash: '.md5(serialize($request)),
	        	'Accept: */*',
	        	'Accept-Charset: UTF-8',
	        	'Accept-Encoding: deflate, gzip',
	        	'Content-Type: text/xml; charset=UTF-8',
	        	'Connection: close'
        	),
        	CURLOPT_TIMEOUT			=> $this->timeout,
        	CURLOPT_CONNECTTIMEOUT	=> $this->connect_timeout
        );
		

    	Log::write('xmlrpc', " url: {$handle['url']} " . PHP_EOL . "method: {$method}" . PHP_EOL . "args: ", $params);
        
        curl_setopt($ch, CURLOPT_URL, $handle['url']);
        if (array_key_exists('port', $handle)) {
          curl_setopt($ch, CURLOPT_PORT, $handle['port']);
        }
		
        $return = curl_exec($ch);
        $url_parsed = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (constant('XMLRPC_PROFILE')) {
            $info = curl_getinfo($ch);
            $log_entry = Array(
                $_SERVER["HTTP_HOST"],
                $_SERVER["REQUEST_URI"],
                $url_parsed["path"],
                curl_getinfo($ch, CURLINFO_HTTP_CODE),
                curl_getinfo($ch, CURLINFO_TOTAL_TIME),
                curl_getinfo($ch, CURLINFO_CONNECT_TIME),
                curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME),
                curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME),
                curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD),
                curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD),
            );
            $this->profileLog(join(" ",$log_entry));
        }
        curl_close($ch);
        
        switch ($http_code) {
        	case 0:
      		case 200:
      			// All's good
      			break;
      			
      		case 404:
      			throw new XMLRPC_Exception_Fault(
      				"Specified method could not be found." . PHP_EOL .
      				"url: {$handle['url']} " . PHP_EOL . "method: {$method}", 404);
      			break;
      	
      		default:
      			throw new XMLRPC_Exception_Fault(
      				"XMLRPC Server responded with a non sucessful http code." . PHP_EOL .
      				"url: {$handle['url']} " . PHP_EOL . "method: {$method}", $http_code);
      			break;
      		
        }
 		$this->setRawOutput($return);
 		$str = mb_convert_encoding($return, "UTF-8", "UTF-8" );
        $r = xmlrpc_decode($str);
        if (!is_array($r)) {
        	//throw new XMLRPC_Exception_Fault("XMLRPC did not return valid response. Does function exist?", 0);
        }

        if (is_array($r) && xmlrpc_is_fault($r)) {
            throw new XMLRPC_Exception_Fault($r['faultString'], $r['faultCode']);
        } 
		return $r;
	}
	
	public function readRequest() {
		// TODO
	}
	
	public function sendResponse(XMLRPC_Response& $response) {
		// TODO
	}
	
	public function readResponse();
	
}
