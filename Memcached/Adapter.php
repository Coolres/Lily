<?php


class Lily_Memcached_Adapter
{
	private $mc;
	
	
	public function __construct($options=array()) {
		if (!isset($options['host'])) {
			throw new Lily_Config_Exception('memcached.$role.host');
		}
		$hosts = $this->balanceWeight($options['host']);
		
		$this->mc = new Memcache();
		foreach ($hosts as $host) {
			list($host, $port, $weight) = explode(':', $host);
			$this->mc->addServer(
				$host,
				$port,
				FALSE ,
				$weight ,
				4 ,
				10,
				TRUE,
				array($this, 'connection_failure')
				);
		}
		
	}
	
	private function balanceWeight($pool) {
		if (!is_array($pool)) {
			$pool = explode(',', $pool);
		}
		$remainder = 100;
		$result = array();
		foreach ($pool as $i => $item) {
			$parts = explode(':', $item);
			// Host
			$host = $parts[0];
			// Port
			$port = isset($parts[1]) ? $parts[1] : '11211';
			// Weight
			if (isset($parts[2])) {
				$weight = $parts[2];
				$remainder -= $weight;
				$result[] = "{$host}:{$port}:{$weight}";
				unset($pool[$i]);
			}
		}
		
		$count = count($pool);
		if ($count > 0) {
			$i = 0;
			$mod = $remainder % $count;
			foreach ($pool as $item) {
				$value = floor($remainder / ($count-$i));
				$i++;
				$remainder -= $value;
				$result[] = $item . ':' . $value;
			}
		}
		return $result;
	}
	
	public function connection_failure($host, $port)
    {
        Lily_Log::error("[ MEMCACHE_FAIL ] {$host} {$port} " );
        return;
    }
	
	/**
	 * get function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function get($key) {
		$temp = $key;
		if (defined('MEMCACHE_MD5_KEYS') && constant('MEMCACHE_MD5_KEYS')) {
			$key = md5($key);
		}
		$result = @$this->mc->get($key);
		Lily_Log::write('memcached', $temp . ' ' . ($result ? 'hit' : 'miss'), null);
		return $result;
	}

	/**
	 * set function.
	 *
	 * @access public
	 * @param mixed $key
	 * @param mixed $value
	 * @param int $TTL. (default: 0)
	 * @return void
	 */
	public function set($key, $value, $TTL=0) {
		$temp = $key;
		if (defined('MEMCACHE_MD5_KEYS') && constant('MEMCACHE_MD5_KEYS')) {
			$key = md5($key);
		}
		if (is_object($key)) Log::error('', debug_backtrace(false));
		$result = @$this->mc->set($key, $value, MEMCACHE_COMPRESSED, $TTL);
		Lily_Log::write('memcached', $temp, $result);
		return $result;
	}

	/**
	 * increment function.
	 *
	 * @access public
	 * @param mixed $key
	 * @param int $count. (default: 1)
	 * @return void
	 */
	public function increment($key, $count=1){
		if (defined('MEMCACHE_MD5_KEYS') && constant('MEMCACHE_MD5_KEYS')) {
			$key = md5($key);
		}
		return $this->mc->increment($key, $count);
	}

	/**
	 * decrement function.
	 *
	 * @access public
	 * @param mixed $key
	 * @param int $count. (default: 1)
	 * @return void
	 */
	public function decrement($key, $count=1){
		if (defined('MEMCACHE_MD5_KEYS') && constant('MEMCACHE_MD5_KEYS')) {
			$key = md5($key);
		}
		return $this->mc->decrement($key, $count);
	}

	/**
	 * __get function.
	 *
	 * @access public
	 * @param mixed $name
	 * @return void
	 */
	public function __get($name) {
		$result = @$this->mc->get($name);
		Lily_Log::write('lilypad', "$name " . ($result ? 'hit' : 'miss'));
		return $result;
	}

	/**
	 * __set function.
	 *
	 * @access public
	 * @param mixed $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value) {
		$result = @$this->mc->set($name, $value, MEMCACHED_COMPRESSED);
		Lily_Log::write('memcached', $name, $result);
		return $result;
	}

	/**
	 * __call function.
	 *
	 * @access public
	 * @param mixed $name
	 * @param mixed $arguments
	 * @return void
	 */
	public function __call($name, $arguments) {
		Lily_Log::write('memcached', $name, $arguments);
		return @call_user_func_array(array($this->mc, $name), $arguments);
	}
}
