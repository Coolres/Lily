<?php

class Lily_View_Json extends Lily_View_Abstract
{
	
	/**
	 * render function.
	 * 
	 * @access public
	 * @return void
	 */
	public function render($template) {
        return json_encode($this->data);		
	}
}
