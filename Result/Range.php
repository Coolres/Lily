<?php


/**
 * Result_Range class.
 * 
 * @extends Lily_Result_Abstract
 */
class Lily_Result_Range extends Lily_Result_Standard {

	public function __construct($result=NULL, $success=true) {
		parent::__construct($result, $success);
		$this->meta['range']	= array(
			'floor'	=> NULL,
			'ceil'	=> NULL,
		);
	
	}

	public function setCeil($ceil) {
		$this->meta['range']['ceil']	 = $ceil;
		return $this;
	}
	
	public function setFloor($floor) {
		$this->meta['range']['floor']	= $floor;
		return $this;
	}
}