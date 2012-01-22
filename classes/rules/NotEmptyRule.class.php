<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class NotEmptyRule extends Rule
{
	public function __construct($message = 'Feltet må ikke være tomt')
	{
		$this->message = $message;
	}
	
	public function accept($value)
	{
		if ($value == null || $value == '')
		{
			return false;
		}
		return true;
	}
	
	public function bum()
	{
		echo 'BUM!';
	}
}

?>