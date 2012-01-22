<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class Template
{
	protected $path = null;
	protected $data = array();
	
	public function __construct($path)
	{
		$this->path = $path;
	}
	
	public function put($key, $value)
	{
		$this->data[$key] = $value;
	}
	
	public function putAll($values)
	{
		$this->data = array_merge($this->data, $values);
	}
	
	public function display()
	{
		if ($this->path != null && file_exists($this->path))
		{
			include $this->path;
		}
		else
		{
			$p = ($this->path != null) ? $this->path : '?';
			echo 'Template not found ('. $p .')';
		}
	}
}

?>