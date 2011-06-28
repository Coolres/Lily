<?php

abstract class Lily_Data_Mapper_Abstract
{
	protected $table;
	protected $model;
	protected $cache_ttl = 3600;
	protected $cache_enabled = false;
	protected $cache_format = 'serialize';

	protected function __construct($args) {
		if ( isset($args['model'])) {
			$this->model = $args['model'];
		} elseif ( isset($args['model_class'])) {
			$this->model = $args['model_class'];
		} else {
			throw new Lily_Data_Mapper_Exception("No model specified (should be string)");
		}

		if ( isset($args['table']) )
			$this->table = $args['table'];

		if ( isset($args['cache_ttl']) )
			$this->cache_ttl = $args['cache_ttl'];

		if ( isset($args['cache_enabled']) )
			$this->cache_enabled = $args['cache_enabled'];

		if ( isset($args['cache_format']) )
			$this->cache_format = $args['cache_format'];
	}

	/**
	 * Build cache id for cache enabled mappers
	 * @param Model_Abstract $model
	 */
	protected function _buildCacheId($id, $columns=null){
		if (null === $this->table) {
			throw new Mapper_Exception("mapper->table must be set in order to utilize cacheing");
		}

		$temp = $this->table . '|' . $id;
		if (!empty($columns)) {
			asort($columns);
			$temp .= '|' . implode('|', $columns);
		}
		return $temp;
	}

	protected function cacheGet($id, $columns=null)
	{
		if ( !$this->cache_enabled ) return false;
		$model = null;
 		$row_id = null;
		if ( $id instanceof Model_Abstract ) {
			$row_id = $this->_buildId($id);
 		} else {
 			$row_id = $id;
 		}

 		$cache_id = $this->_buildCacheId($row_id, $columns);
 		$mc = Lily_Memcached_Manager::get();
 		if ( $result = $mc->get($cache_id) ) {
 			switch ($this->cache_format) {
 				case 'serialize' :
 					$model = unserialize($result);
 					break;

 				case 'json':
 				default:
 					$array = json_decode($result, true);
					$model = new $this->model($array);
 					break;
 			}
 		}
		return $model;
	}

	protected function cacheSet( Model_Abstract& $model, $columns=null, $time=null)
	{
		if ( !$this->cache_enabled ) return false;
		$row_id = $this->_buildId($model);
		$cache_id = $this->_buildCacheId($row_id, $columns);
		$result = null;
		switch ($this->cache_format) {
 			case 'serialize' :
 				$result = serialize($model);
 				break;

 			case 'json':
 			default:
 				$result = json_encode($model->__toArray());
 				break;
 		}


		$mc = Lily_Memcached_Manager::get();
		$ttl = null !== $time ? $time : $this->cache_ttl;
		return $mc->set($cache_id, $result, $ttl);
	}

	protected function cacheDelete(Model_Abstract& $model, $columns=null)
	{
		if ( !$this->cache_enabled ) return false;
		$mc = Lily_Memcached_Manager::get();
		$cache_key = $this->_buildCacheId($this->_buildId($model), $columns);
		return $mc->delete($cache_key);
	}

	public function invalidate(Model_Abstract& $model, $columns=null)
	{
		$this->cacheDelete($model, $columns);
	}

	abstract public function get($id);

	abstract protected function _buildId(Model_Abstract& $model);
}