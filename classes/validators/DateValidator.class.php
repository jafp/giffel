<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class DateValidator extends Validator
{
	public static function validate($object, $field, $value, $params)
	{
		$pattern = '';
		if (preg_match($pattern, $value) != 1)
		{
			return array($field[0], "{$field[2]} er ikke et gyldigt format (dd-mm-åååå)");
		}
		return null;
	}
}

?>
