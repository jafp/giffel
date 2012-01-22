<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class LessThanRule extends Rule
{
	public $limit = null;
	
	public function __construct($limit, $message = 'Feltet skal være mindre end {limit}')
	{
		$this->limit = $limit;
		$this->message = str_replace('{limit}', $limit, $message);
	}
	
	public function accept($value)
	{
		return ($value < $limit);
	}
}

?>