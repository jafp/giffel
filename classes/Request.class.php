<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class Request
{
	public $get;
	public $post;
	public $server;
	
	public function __construct($get, $post, $server)
	{
		$this->get = $get;
		$this->post = $post;
		$this->server = $server;
	}
	
	public function isGet()
	{
		return ($this->server['REQUEST_METHOD'] == 'GET');
	}
	
	public function isPost()
	{
		return ($this->server['REQUEST_METHOD'] == 'POST');
	}
	
	public function getParameter($name)
	{
		if (isset($this->get[$name]))
		{
			return $this->get[$name];
		}
		if (isset($this->post[$name]))
		{
			return $this->post[$name];
		}
		return null;
	}
}

?>