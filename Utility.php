<?php
/**
 * Copyright (c) 2010, 2011 All rights reserved, Matt Ward
 * This code is subject to the copyright agreement found in 
 * the project root's LICENSE file. 
 */
/**
 * LilypadMVC_Utility
 * @author matt
 *
 */
class LilypadMVC_Utility
{
	
	public static function fromCamelCase ($string, $leading_underscore = false)
	{
		$search = '/([A-Z])/';
		$callback = create_function('$matches', 
		'return "_".strtolower(current($matches));');
		$result = preg_replace_callback($search, $callback, $string);
		if (! $leading_underscore) {
			if (substr($result, 0, 1) == '_') {
				$result = substr($result, 1);
			}
		}
		return $result;
	}
	
	public static function toCamelCase ($string, $uc_first = false)
	{
		$parts = explode('_', $string);
		$final = strtolower(array_shift($parts));
		while ($word = array_shift($parts)) {
			$final .= ucfirst(strtolower($word));
		}
		if ($uc_first)
			$final = ucfirst($final);
		return $final;
	}
	
}