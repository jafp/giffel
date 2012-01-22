<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class UniqueValidator implements Validator
{
	public static function validate($object, $field, $value, $params)
	{
		if ($object->id != null)
		{
			return null;
		}
		
		$name = $field[0];
		$unique_field = $params[0];
		$clazz = get_class($object);

		$res = $clazz::findOne("where `$unique_field` = ?", array($value));
		if ($res != null)
		{
			return array($name, "{$field[2]} er allerede i brug");
		}
		
		return null;
	}
}

?>