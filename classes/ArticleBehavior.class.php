<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class ArticleBehavior implements Behavior
{
	function handle(Page $page, Controller $controller, Request $request)
	{
		$page->times_read = $page->times_read + 1;
		$page->save();

		$controller->assign('article', $page);
		
		return 'article_behavior_view';
	}
}

?>