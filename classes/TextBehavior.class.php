<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class TextBehavior implements Behavior
{
	public function handle(Page $p, Controller $c, Request $r)
	{
		$c->p = $p;
		$c->p->formatted_date = date('d-m-Y', strtotime($p->timestamp));
		
		return 'text_behavior_view';
	}
}

?>