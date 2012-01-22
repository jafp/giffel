<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

interface Behavior
{
	public function handle(Page $p, Controller $c, Request $r);
}

?>