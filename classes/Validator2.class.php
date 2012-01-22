<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class Validator
{
	/*
	* $object = array('field1' => 'value1', 'field2' => 'value2',...)
	* $rules = array('field1' => new NotEmptyRule('Field 1 må ikke være tom')) 
	*
	* Rule.accept($value)
	*
	* Return array like this 
	* array('field1' => array('Feltet må ikke være tomt'), 'field2' => array('Fejl1', 'Fejl2'))
	*/
	public static function validate($object, $rules)
	{
		$result = array();
		foreach ($rules as $field => $rule)
		{
			$value = isset($object[$field]) ? $object[$field] : null;
			if (is_object($rule))
			{
				if (!$rule->accept($value))
				{
					self::addRuleViolation($result, $field, $rule);
				}
			}
			else if (is_array($rule))
			{
				// TODO
			}
		}
		return empty($result) ? null : $result;
	}
	
	private static function addRuleViolation(&$result, $field, $rule)
	{
		if (!isset($result[$field]))
		{
			$result[$field] = array();
		}
		array_push($result[$field], $rule->message);
	}
}


?>