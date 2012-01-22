<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

abstract class BaseController
{
	public static $roles = array();
	public static $is_debug_only = false;
	public static $urls = array();

	public $smarty;
	public $template;

	public function initialize() {}	
	public function before() {}
	
	public function __construct()
	{
		$this->smarty = new Smarty();
	
		if (Util::isDebug())
		{
			$this->smarty->force_compile = true;
			$this->smarty->caching = 0;
		}
	
		$this->smarty->template_dir = TEMPLATES;
		$this->smarty->compile_dir = TEMPORARY . 'templates_compiled';
		$this->smarty->cache_dir = TEMPORARY . 'templates_cache';
		
		$clazz = get_called_class();
		$methods = get_class_methods($clazz);
		foreach ($methods as $method)
		{
			if (preg_match('/smarty_function_plugin_(.*)/', $method, $matches))
			{
				$this->smarty->registerPlugin('function', $matches[1], $clazz . '::' . $matches[0]);
			}
		}
	}
	
	public function handle($action, Request $data)
	{		
		$retval = $this->before();
		
		if (method_exists($this, $action))
		{
			return $this->$action($data);
		}
		
		$name = 'action' . ucfirst($action);
		if (method_exists($this, $name))
		{
			return $this->$name($data);
		}
	}
	
	public function getSmarty()
	{
		return $this->smarty;
	}
	
	public function getTemplatePath($template = null)
	{
		$t = $template != null ? $template : $this->template;
		return TEMPLATES . $t . '.tpl';
	}
	
	public function __set($k, $v)
	{
		$this->$k = $v;
		$this->assign($k, $v);
	}
	
	public function assign($k, $v)
	{
		$this->smarty->assign($k, $v);
	}
	
	public static function smarty_function_plugin_linkOld($params, $template)
	{
		return Link::to($params['caption'], $params['url'], $params['class']);
	}
	
	public static function smarty_function_plugin_url($params, $template)
	{
		$url = $params['url'];
		foreach ($params as $k => $v)
		{
			$url = str_replace(':' . $k, $v, $url);
		}
		return Link::url($url);
	}
	
	public static function smarty_function_plugin_res($params, $template)
	{
		return RESOURCE_BASE . $params['url'];
	}
	
	public static function smarty_function_plugin_link($params, $template)
	{
		$url = $params['url'];
		foreach ($params as $k => $v)
		{
			$url = str_replace(':' . $k, $v, $url);
		}
		
		// avoid trouble with missing class parameter in.
		// Consider using same solution as in img plugin with 
		// merging an array with default parameters
		if (!isset($params['class']))
		{
			$params['class'] = '';		
		}
		
		return Link::to($params['caption'], $url, $params['class']);
	}
	
	public static function smarty_function_plugin_flash($params, $template)
	{
		if (Util::hasFlash())
		{
			$flash = Util::getFlash();
			return '<div class="x-info clsQuickNotice">' . $flash . '</div>';
		}
		return '';
	}
	
	/**
	 * Forces the output buffer to flush, and there by sending the current output
	 * back to the browser. Good practice is to use this after the head-element.
	 *
	 * @param $parms
	 *		The parameters given to this function
	 * 
	 * @param $tpl
	 *		The template (smarty) object
	 */
	static function smarty_function_plugin_flush_buffer($parms, $tpl)
	{
		flush();
	}
	
	static function smarty_function_plugin_img($parms, $tpl)
	{
		// merge with default parameters
		$parms = array_merge( array('class' => '', 'style' => '', 'title' => ''), $parms);
		
		return "<img src='" . Link::base($parms['src']) . "' style='".$parms['style']."' class='".$parms['class']."' title='".$parms['title']."'/>";
	}
	
	static function smarty_function_plugin_breadcrumps($parms, $tpl)
	{
		$clz = get_called_class();
		$bc = $clz::$breadcrumps;		
		
		if (is_array($bc))
		{
			
			$html = '<div class="breadcrumps"><span class="breadcrumps-inner"><small><span class="breadcrumps-whereami">Du er her: </span>';

			$html .= Link::to('Forside', '', '');

			$length = count($bc);
			
			if ($length > 0)
			{
				$html .= ' > ';
			}
			
			for ($i = 0; $i < $length; $i++)
			{
				$l = $bc[$i];
				$u = $bc[++$i];
			
				$html .= '<span class="breadcrump-link">' . Link::to($l, $u, '') . '</span>';
				if ($i < $length - 1)
				{
					$html .= ' > ';
				}
			}
		
			$html .= '</small></span></div>';
			return $html;
		}
	}
	
	function json($o)
	{
		return 'json:' . json_encode($o);
	}
	
	/**
	 * abc => append bread crump 
	*/
	static function abc()
	{
		$clazz = get_called_class();
		if ($clazz::$breadcrumps == null)
		{
			$clazz::$breadcrumps = array();
		}
	
		foreach (func_get_args() as $arg)
		{
 			$clazz::$breadcrumps[] = $arg;
		}
	}
}

?>