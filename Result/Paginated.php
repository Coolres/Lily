<?php

/**
 * Result_Paginated class.
 * 
 * @extends Result_Abstract
 */
class Lily_Result_Paginated extends Lily_Result_Standard{


	public function __construct($result=NULL, $sucess=true) {
		parent::__construct($result, $sucess);
		$this->meta['pagination']	= array(
			'total'		=> NULL,
			'offset'	=> NULL,
			'limit'		=> NULL,
			'page'		=> NULL
		);
	}

	
	public function setTotal($total) {
		$this->meta['pagination']['total']	= (int) $total;
		return $this;
	}
	
	public function setOffset($offset) {
		$this->meta['pagination']['offset']	= (int) $offset;
		return $this;
	}
	
	public function setPage($offset) {
		$this->meta['pagination']['page']	= (int) $offset;
		return $this;
	}
	
	public function setLimit($limit) {
		$this->meta['pagination']['limit']	= (int) $limit;
		return $this;
	}
	
}