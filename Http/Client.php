<?php

class Lily_Http_Client
{
	private static $instance;

	private function __construct() {}

	public function getInstance() {
		if (null === self::$instance) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	public function execute(& $request) {
		if (is_array($request)) {
			return $this->multiCurl($request);
		} elseif ($request instanceof Lily_Http_Request) {
			return $this->curl($request);
		}
		throw new Exception ("Http_Client->execute expects an array or instance of Http_Request");
	}


	private function multiCurl(array& $requests) {
		$handles = array();
		$multi_handle = curl_multi_init();
		foreach ($requests as $id => $r) {
			$handles[$id] = curl_init();
			$url = $r->getUrl();
			$opts = array(
				CURLOPT_URL => $r->getUrl(),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 3000
			);
			switch ($r->getMethod()) {
				case 'POST':
					$opts[CURLOPT_POST] = true;
					$opts[CURLOPT_POSTFIELDS] = $r->getParams();
				break;

				default:
					$params = $r->getParams();
					if (!empty($params)) {
						$url .= '?' . http_build_query($params);
					}
				break;
			}
			$opts[CURLOPT_URL] = $url;
			curl_setopt_array($handles[$id], $opts);
			curl_multi_add_handle($multi_handle, $handles[$id]);
		}

	 	if (count($handles) > 0) {
            $running = null;
            do {
                $mrc = curl_multi_exec($multi_handle, $running);
                usleep(1000);
            } while ($running);

            foreach ($handles as $id => $handle) {
                $data = curl_multi_getcontent($handle);
                $requests[$id]->setResult($data);
                $requests[$id]->setInfo(curl_getinfo($handle));
            }
        }
        curl_multi_close($multi_handle);
        return $requests;
	}

	private function curl(&$request) {
		$handle = curl_init();
		$url = $request->getUrl();
		$opts = array(
			CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_0,
			CURLOPT_RETURNTRANSFER	=> 1,
			CURLOPT_ENCODING		=> 'deflate, gzip',
			CURLOPT_HTTPHEADER		=> array(
	        	'Accept-Charset: UTF-8',
	        	'Accept-Encoding: deflate, gzip',
	        	'Connection: close'
        	),
        	CURLOPT_TIMEOUT			=> 2,
        	CURLOPT_CONNECTTIMEOUT	=> 2
        );

		switch ($request->getMethod()) {
			case 'POST':
				$opts[CURLOPT_POST] = true;
				$opts[CURLOPT_POSTFIELDS] = $request->getParams();
			break;

			default:
				$params = $request->getParams();
				if (!empty($params)) {
					$url .= '?' . http_build_query($params);
				}
			break;
		}
		$opts[CURLOPT_URL] = $url;
		curl_setopt_array($handle, $opts);
		$result = curl_exec($handle);
		$request->setResult($result);
		$request->setInfo(curl_getinfo($handle));
		return $request;
	}
}