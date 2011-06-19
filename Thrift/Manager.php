<?php

class Lily_Thrift_Manager
{
	private static $instance;
	private $wrappers;
	private $roles;
	
	public function __construct(array $options) {
		if (null !== self::$instance) {
			throw new Exception("Cannot declare more instances of Thrift_Manager");
		}
		
		// Location of the thrift libraries needs to be specified
		if (!isset($options['root'])) {
			throw new Lily_Config_Exception('thrift.root');
		} else {
			$this->thrift_lib = $options['root'];
			$GLOBALS['THRIFT_ROOT'] = $options['root'];
			require_once( $options['root'] . '/Thrift.php' );
			require_once( $options['root'] . '/transport/TSocket.php');
			require_once( $options['root'] . '/transport/TBufferedTransport.php');
			require_once( $options['root'] . '/protocol/TBinaryProtocol.php');
		}
		
		if (isset($options['role'])) {
			$this->roles = $options['role'];
		}
			
		self::$instance = $this;
	}
	
	public static function get($role='default') {
		if (null === self::$instance) {
			throw new Exception('Thrift_Manager not initialized.');
		}
		if ( !isset(self::$instance->roles[$role]) ) {
			throw new Lily_Config_Exception("thrift.role.{$role}");
		}
		
		if ( !isset(self::$instance->wrappers[$role]) ) {
			if (!isset(self::$instance->roles[$role]['adapter'])) {
				throw new Lily_Config_Exception("thrift.role.{$role}.adapter");
			}
			$adapter = self::$instance->roles[$role]['adapter'];
			switch (strtolower($adapter)) {
				case 'hbase' :
					require_once(self::$instance->thrift_lib . '/packages/Hbase/Hbase.php');
					self::$instance->wrappers[$role] = new Lily_Thrift_Adapter_Hbase(
						self::$instance->roles[$role]);
					break;
					
				default:
					throw new Lily_Config_Exception("thrift.role.{$role}.adapter is not valid");
					break;
			}

		}
		return self::$instance->wrappers[$role];
	}
	
}
