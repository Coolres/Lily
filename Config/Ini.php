<?php

if (!function_exists ('array_replace_recursive')) {
	
	function array_replace_recursive($a, $b) {
		if (!is_array($a) || !is_array($b)) {
			return $b;
		}
		
		foreach ($b as $key => $value) {
			if (isset($a[$key])) {
				$a[$key] = array_replace_recursive($a[$key], $value);
			} else {
				$a[$key] = $value;
			}
		}
		return $a;
	}
}

class Lily_Config_Ini
{
    private $_result = array();
 	private $_section_name = null;
	
	/**
	 * Note: you should never call the constructor with null params
	 * unless it is from within this class. The null constructor was
	 * added for usability when needing to populate a new INI object
	 * with data 
	 *
	 * @param $filename
	 * @param $section_name
	 */
	public function __construct($filename=null, $section_name=null)
    {
    	$this->_section_name = $section_name;	
		if (null !== $filename) {
			$this->parseFile($filename, $this->_section_name);
		}
    }

	public function get($property=null, $section=null) {
		
		$section = $section ? $section : $this->_section_name;
		
		if ($property !== null) {
			return $this->_result[$section][$property];
		}
		return $this->_result[$section];
	}
	
	/**
	 * Merge (replace the values of a with the values of b) and return 
	 * ini object representing result.
	 */
	public function merge(Lily_Config_Ini $a)
	{
		$section_name = null;
		if ($this->_section_name == $a->_section_name){
			$section_name = $this->_section_name;
		}
		$temp = array_replace_recursive($this->_result, $a->_result);
		$ini = new Lily_Config_Ini();
		$ini->_result = $temp;
		$ini->_section_name = $section_name;
		return $ini;
	}
	
	
	private function parseFile($filename, $section_name)
	{
    	if (!file_exists($filename)) {
    		throw new Exception("Ini file, '$filename' does not exist or is not readable.");
    	}
		if (null === $section_name) {
			throw new Exception("Section of ini not specified");
		}
		
    	$ini = parse_ini_file($filename, true);
		if ($ini === false) {
			throw new Exception("Could not parse ini file");
		}
		
		// Calculate dependencies across sections.
		// We only want to pay the computation cost on the 
		// sections we really need to.
		$dep = array();
		foreach ($ini as $name => $payload) {
			if (strpos($name, ':') !== false) {
				list($child, $parent) = explode(':', $name);
				$dep[$child] = $parent;
			} else {
				$dep[$name] = null;
			}
		}
		$this->parseSection($section_name, $dep, $ini);	
	}
	
	private function parseSection($section_name, array& $dep, array& $ini) {
		if (!array_key_exists($section_name, $dep)) {
			return null; // Should throw exception?
		}
		if (isset($this->_result[$section_name])) {
			return $this->_result[$section_name];
		}
		
		$result = array();
		
		$section_key = $section_name;
		if (!empty($dep[$section_name])) {
			$parent = $dep[$section_name];
			if (!isset($this->_result[$parent])) {
				$result = $this->parseSection($parent, $dep, $ini);
			} else {
				$result = $this->_result[$parent];
			}
			
			if (null === $result) {
				throw new Exception("Could not resolve parent dependency of section '$section_name' to parent '$parent'");
			}
			$section_key .= ':' . $parent;
		}
		
		if (!isset($ini[$section_key])) {
			throw new Exception("Could not resolve section '$section_key' in ini");
		}
		
		$payload = $ini[$section_key];
		$temp = $this->parsePayload($payload);
		$result = array_replace_recursive($result, $temp);
		$this->_result[$section_name] = $result;
		return $result;
	}
	
	
	private function parsePayload(array $payload) {
		$result = array();
		foreach ($payload as $string => $value) {
			$parts = explode('.', $string);
			$place =& $result;
			$size = count($parts);
			for ($i=0; $i<$size; $i++) {
				$temp = $parts[$i];
				if ($i+1 == $size) {
					$place[$temp] = $value;
				}elseif (!isset($place[$temp])) {
					$place[$temp] = array();
				}
				$place =& $place[$temp];
			}
		}
		return $result;
	}
	
}