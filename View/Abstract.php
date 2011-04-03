<?php 

/**
 * LilypadMVC_View_Abstract class.
 * @author Matt Ward
 */
class LilypadMVC_View_Abstract
{
	private $template;
	protected $partials_dir;
	protected $file_extension;	// will want different file extension to distinguish between smarty and other template engines
	
	public function __construct($partials_dir)
	{
		$this->partials_dir	= $partials_dir;
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
        
		$log = LilypadMVC_Application::getLogger();
		$log->debug($template);
        		
		ob_start();
		require_once($template);
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
		if (substr($name, '.' . $this->file_extension) == 0) {
			$name .= '.' . $this->file_extension;
		}
		
		$view	 = new LilypadMVC_View_Abstract($this->partials_dir);
		$view->assignData($data)
			->setTemplate($this->partials_dir . '/' . $name);
		echo $view->render();
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
			$view	 = new LilypadMVC_View_Abstract($this->partials_dir);
			$view->assignData($d)
				->setTemplate($this->partials_dir . '/' . $name);
			echo $view->render();
		}
	}
}