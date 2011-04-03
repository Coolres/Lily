<?php

require_once(dirname(__FILE__) . '/Interface.php');

/**
 * Autoloader for class in agreeance with the following naming convention:
 * /
 *  Lilypad
 *   SomeClass.php
 * 
 * class LilypadMVC_SomeClass
 * 
 * @author Matt Ward
 */
class LilypadMVC_Loader_Autoloader_Class 
    implements LilypadMVC_Loader_Autoloader_Interface
{
    private $_namespace;
    private $_basepath;
    
    
    public function __construct($options) 
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }
    
    public function setOptions($options) {
        $functions = get_class_methods($this);
        foreach($options as $key => $value) {
            $function = 'set';
            $parts = explode('_', $key);
            foreach($parts as $part) {
                $function .= ucfirst($part);
            }
            if (in_array($function, $functions)) {
                $this->$function($value);
            }
        }
    }
    
    public function autoload($class) 
    {
        $classPath = $this->getClassPath($class);
        if (false !== $classPath) {
            return include $classPath;
        }
        return false;
    }
    
    public function setNamespace($arg){
        $this->_namespace = $arg;
    }
    
    public function getNamespace() {

        return $this->_namespace;
    }
    
    public function setBasepath($arg) {
        $this->_basepath = $arg;
    }
    
    public function getBasepath() {

        return $this->_basepath;
    }
    
    /**
     * Helper method to calculate the correct class path
     *
     * @param string $class
     * @return False if not matched other wise the correct path
     */
    public function getClassPath($class)
    {
        $segments   = explode('_', $class);
        $namespace  = $this->getNamespace();
        
        if (!is_null($namespace) && $namespace != '') {
            if($namespace != array_shift($segments)) {
                return false; // Enforcing namespace and not in namespace.
            }
        }
        
        $classPath = $this->getBasepath() . '/' . implode('/', $segments) . '.php';
        if (is_file($classPath)) {
            return $classPath;
        }
        return false;
    }
} 
