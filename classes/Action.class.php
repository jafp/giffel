<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class Action
{
	public $params_naming = null;
	
	public $action = null;
	public $request = null;
	public $params = null;
	public $nparams = null;
	
	protected $tpl_data = array();
	protected $tpl_name = null;
	
	public function setup($action_info)
	{
		$this->request = $action_info;
		$this->action = $action_info['action'];
		$this->params = $action_info['params'];
		
		if ($this->params_naming != null)
		{
			$np = array();
			
			$pn = $this->params_naming;
			$pn_a = explode('/', $pn);
			
			for ($i = 0; $i < count($this->params); $i++)
			{
				if (isset($pn_a[$i]))
				{
					$np[$pn_a[$i]] = $this->params[$i];
				}
			}
			$this->nparams = $np;
		}
	}
	
	protected function template($name)
	{
		$this->tpl_name = $name;
	}
	
	protected function put($key, $value)
	{
		$this->tpl_data[$key] = $value;
	}
	
	public function response()
	{
		return array_merge(array('view' => $this->tpl_name), $this->tpl_data);
	}
	
	public function __set($k, $v)
	{
		if ($k == 'template')
		{
			$this->tpl_name = $v;
		}
		else
		{
			$this->tpl_data[$k] = $v;
		}
	}
	
	public function init() {}
 	public function get() {}
	public function post() {}
	
}

?>