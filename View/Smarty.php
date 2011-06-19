<?php
/**
 * Copyright (c) 2010, 2011 All rights reserved, Matt Ward
 * This code is subject to the copyright agreement found in
 * the project root's LICENSE file.
 */
/**
 * Lily_View_Smarty class.
 * @author Matt Ward
 * @extends Lily_View_Abstract
 */
class Lily_View_Smarty extends Lily_View_Abstract
{
	protected $smarty;


	public function __construct($options=array())
	{
		if (is_array($options)) {
			if (isset($options['smarty'])) {
				require_once($options['smarty'] . '/libs/Smarty.class.php');
			} else {
				throw new Lilypad_Configuration_Exception('lilypad.dispatcher.modules.$module.view.smarty');
			}
		}
		parent::__construct($options);
		$this->file_extension	= 'tpl';
		$this->smarty	= new Smarty();
		$this->smarty->language = 'en';
		$this->smarty->assign('PARTIAL_DIR', $this->partials_dir);        
	}

	public function render($template) {
		if (substr($template, '.' . $this->file_extension) == 0) {
			$template .= '.' . $this->file_extension;
		}

		$log = Lily_Application::getLogger();
		$log->debug($template);
		ob_start();
		$this->smarty->display($template);
		return ob_get_clean();
	}

	public function __set($key, $value) {
		$this->smarty->assign_by_ref($key, $value);
	}

	public function __get($key) {
		return null;
	}

}