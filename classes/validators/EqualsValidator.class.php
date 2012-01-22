<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

/*
validates that the given field has the same value as the
field given in the params
*/

class EqualsValidator implements Validator {
	public static function validate($object, $field, $value, $params) {
		$field_name = $field[0];
		$other_field = $params[0];
		if ($value != $object->$other_field) {
			return array($field_name, "{$field[2]} er ikke gentaget korrekt");
		}
		return null;
	}
}

?>