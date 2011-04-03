<?php
/**
 * @author Matt Ward
 */
class LilypadMVC_Loader_Autoloader 
{
    private static $_instance;
    private $_autoloaders;
    
    
    private function __construct()
    {
        $this->_autoloaders = array();
        // @see http://php.net/manual/en/function.spl-autoload-register.php
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }
    
    public function addAutoloader(LilypadMVC_Loader_Autoloader_Interface& $interface)
    {
        $this->_autoloaders[] = $interface;    
    }
    
    public static function autoload($class) 
    {
        $instance =& self::getInstance();
        foreach ($instance->_autoloaders as $loader) {
            if ($loader->autoload($class)) return true;
        }
        return false;
    }


    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}

