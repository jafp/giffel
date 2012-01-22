<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

interface Validator 
{
	public static function validate($object, $field, $value, $params);
}

?>