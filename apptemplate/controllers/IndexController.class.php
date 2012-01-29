<?php 

class IndexController extends BaseController
{
	static $urls = array( 
		array('', 'index', 'index') 
	);
	
	function index(Request $r)
	{
		return 'index';
	}
}