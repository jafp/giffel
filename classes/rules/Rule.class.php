<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

abstract class Rule
{
	public $message = null;
	
	public function __construct($message = 'Feltet opfylder ikke de givne regler')
	{
		$this->message = $message;
	}
	
	abstract public function accept($value);
}

?>