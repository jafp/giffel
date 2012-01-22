<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class EmailValidator implements Validator
{
	public static function validate($object, $field, $value, $params)
	{
		$pattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/';
		if (preg_match($pattern, $value) != 1)
		{
			return array($field[0], "{$field[2]} skal være en gyldig e-mail adresse");
		}
		return null;
	}
}

?>