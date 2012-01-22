<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class NotEmptyValidator implements Validator {
	public static function validate($object, $field, $value, $params) {
		$valid = ($value != null && $value != '');
		if (!$valid) {
			$field_label = $field[2];
			return array($field[0], "{$field_label} må ikke være tom");
		}
		return null;
	}
}

?>