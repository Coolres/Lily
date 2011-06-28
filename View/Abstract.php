<?php
/**
 * Copyright (c) 2010, 2011 All rights reserved, Matt Ward
 * This code is subject to the copyright agreement found in
 * the project root's LICENSE file.
 */
/**
 * Lily_View_Abstract class.
 * @author Matt Ward
 */
class Lily_View_Abstract
{
	private $template;
	protected $partials_dir;
	protected $file_extension;	// will want different file extension to distinguish between smarty and other template engines

	public function __construct($options=array())
	{
		if (isset($options['partials'])) {
			$this->partials_dir = $options['partials'];
		}
		$this->file_extension	= "phtml";
	}
	/**
	 * assignData function.
	 *
	 * @access public
	 * @param mixed array& $data
	 * @return void
	 */
	public function assignData(array& $data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
		return $this;
	}

	/**
	 * render function.
	 *
	 * @access public
	 * @return void
	 */
	public function render($template) {
        if (strpos($template, '.' . $this->file_extension) == 0) {
			$template .= '.' . $this->file_extension;
		}
		Lily_Log::write('lily', $template);
		ob_start();
		require($template);
		return ob_get_clean();
	}

	/**
	 * partial function.
	 *
	 * @access public
	 * @param mixed $name
	 * @param mixed $data. (default: NULL)
	 * @return void
	 */
	public function partial($name, $data=NULL) {
		if (strpos($name, '.' . $this->file_extension) == 0) {
			$name .= '.' . $this->file_extension;
		}

		$view	 = new Lily_View_Abstract(array(
			'partials'	=> $this->partials_dir
		));
		$view->assignData($data);
		echo $view->render($this->partials_dir . '/' . $name);
	}

	/**
	 * partialLoop function.
	 *
	 * @access public
	 * @param mixed $name
	 * @param mixed $data
	 * @return void
	 */
	public function partialLoop($name, $data) {
		if (substr($name, '.' . $this->file_extension) == 0) {
			$name .= '.' . $this->file_extension;
		}

		foreach ($data as $i => $d) {
			$view	 = new Lily_View_Abstract(array(
				'partials'	=> $this->partials_dir
			));
			$view->assignData($d);
			echo $view->render($this->partials_dir . '/' . $name);
		}
	}
}