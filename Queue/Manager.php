<?php

class Lily_Queue_Manager {
    private static $_instance;
    private $_role_config;
    private $_adapters = array();
    private $_workers = array();
    private $_jobs = array();
    
    /**
     * Construct
     * @param array $options 
     * @return null
     */
    public function __construct($options) {
        if (!is_null(self::$_instance)) {
            throw new Lily_Queue_Exception(__CLASS__ . ' already instantiated');
        }
        if (isset($options['role'])) {
            $this->_role_config = $options['role'];
        }
        self::$_instance = $this;
    }
    
    /**
     * Get a copy of the instance. This does not
     * create a new instance like a normal singleton.
     * @return Lily_Queue_Manager
     */
    public static function getInstance() {
        self::_checkInstance();
        return self::$_instance;
    }
    
    /**
     * Getter for role config.
     * @return array $this->_role_config
     */
    public function getRoleConfig() {
        self::_checkInstance();
        return $this->_role_config;
    }
    
    /**
     * toString
     * @return string
     */
    public function __toString() {
        return __CLASS__;
    }
    
    /**
     * Get an adapter. This is a singleton.
     * @param string $role
     * @return Lily_Queue_Adapter_Abstract
     */
    public static function getAdapter($role) {
        self::_checkInstance();
        
        // Return the adapter if it already exists
        if (isset(self::$_instance->_adapters[$role])) {
            return self::$_instance->_adapters[$role];
        }
        
		if (!isset(self::$_instance->_role_config[$role])) {
			throw new Lily_Config_Exception("queue.role.$role");
		}

		$options = self::$_instance->_role_config[$role];
		if (!isset($options['adapter'])) {
			throw new Lily_Config_Exception("queue.role.$role.adapter");
		}

		$adapter = null;
		$class = $options['adapter'];
		if (class_exists($class)) {
		    $adapter = new $class($options);
		} else {
		    $class = 'Lily_Queue_Adapter_' . $options['adapter'];
		    if (class_exists($class)) {
		        $adapter = new $class($options);
		    }
		}

		if (is_null($adapter)) {
			throw new Lily_Queue_Exception("Class not found for specified adapter, {$options['adapter']}");
		}

		self::$_instance->_adapters[$role] = $adapter;
	    return $adapter;
    }
    
    /**
     * Instantiate a new worker.
     * @param string $role
     * @return mixed
     */
    public static function getWorker($role) {
        self::_checkInstance();
                
		if (!isset(self::$_instance->_role_config[$role])) {
			throw new Lily_Config_Exception("queue.role.$role");
		}

		$options = self::$_instance->_role_config[$role];
		if (!isset($options['worker'])) {
			throw new Lily_Config_Exception("queue.role.$role.worker");
		}
		
		$worker = null;
        $class = $options['worker'];
        if (class_exists($class)) {
            $worker = new $class($options);
        } else {
            $class = 'Lily_Queue_Worker_' . $options['worker'];
            if (class_exists($class)) {
                $worker = new $class($options);
            }
        }

		if (is_null($worker)) {
			throw new Lily_Queue_Exception("Class not found for specified worker, {$options['worker']}");
		}

        self::$_instance->_workers[$role][] = $worker;
        return $worker;
    }
    
    /**
     * Instantiate a new job.
     * @param string $role
     * @param mixed $payload
     * @return mixed
     */
    public static function getJob($role, $payload=null) {
        self::_checkInstance();
                        
		if (!isset(self::$_instance->_role_config[$role])) {
			throw new Lily_Config_Exception("queue.role.$role");
		}

		$options = self::$_instance->_role_config[$role];
		if (!isset($options['job'])) {
			throw new Lily_Config_Exception("queue.role.$role.job");
		}
        
        $job = null;
        $class = $options['job'];
        if (class_exists($class)) {
            $job = new $class($options);
        } else {
            $class = 'Lily_Queue_Job_' . $options['job'];
            if (class_exists($class)) {
                $job = new $class($options);
            }
        }

		if (is_null($job)) {
			throw new Lily_Queue_Exception("Class not found for specified job, {$options['job']}");
		}

        self::$_instance->_jobs[$role][] = $job;
        return $job;
    }
    
    /**
     * Check if we have an self::$_instance
     * @return null
     */
    private static function _checkInstance() {
        if (is_null(self::$_instance)) {
            throw new Lily_Queue_Exception(__CLASS__ . ' manager not instantiated');
        }
        return true;
    }
}