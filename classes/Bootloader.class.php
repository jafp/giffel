<?php

/** 
 * Copyright 2011 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

class Bootloader
{
	const QUERY = 'q';
	
	private $_url = null;
	private $_mappings = null;
	private $_has_rendered = false;
	private $_get_data = null;
	
	public function __construct()
	{	
		$this->_mappings = array();
		$this->debugging = (defined('DEBUG') && DEBUG == true);
		
		$this->indexControllers(CONTROLLERS);
	}
	
	private function indexControllers($path)
	{
		$dir = dir($path);
		if ($dir != null)
		{
			while (($e = $dir->read()) != null)
			{
				if ($e != '.' && $e != '..')
				{
					$p = $path . $e . DS;
					if (is_dir($p))
					{
						$this->indexControllers($p);
					}
					else
					{
						// TODO Type check
						$clazz = substr($e, 0, strrpos($e, '.class.php'));
						if ($this->debugging || (!$this->debugging && !$clazz::$is_debug_only))
						{
							$urls = $clazz::$urls;
							foreach ($urls as $url)
							{
								$this->_mappings[$url[0]] = $url;
							}
						}
					}
				}
			}
		}
	}
	
	public function handleRequest()
	{	
		$query = INDEX;	
		if (isset($_GET[Bootloader::QUERY]))
		{
			$query = $_GET[Bootloader::QUERY]; 
		}
		
		$mapping = null;
		$url_data = null;
		
		//Profiler::start('request matching');
		foreach ($this->_mappings as $u => $m)
		{
			$matches = null;
			if (preg_match(self::compileUrlRegex($u), $query, $matches) > 0)
			{
				$mapping = $m;
				$url_data = $matches;
				break;
			}
		}
		//Profiler::stop('request matching');
		
		if ($mapping != null)
		{
			$controller = $this->getController($mapping[1]);
			
			$controller->initialize();
			
			// Merge get data with the parameters in the query
			$get = array_merge($url_data, $_GET);
			
			$retval = $controller->handle($mapping[2], new Request($get, $_POST, $_SERVER));
			
			$rettype = gettype($retval);
			if ($rettype == 'string')
			{
				if (preg_match('/redirect\:(?<url>[a-zA-Z0-9 -_\/]+)/', $retval, $matches)) 
				{
					if ($matches['url'] == 'INDEX')
					{
						$matches['url'] = '';
					}
					header('location: ' . Link::url($matches['url']));
				}
				else if (preg_match('/json\:(?<json>.*)/', $retval, $matches))
				{
					//header('Content-Type: application/json; charset=utf8');
					echo $matches['json'];
				}
				else
				{
					$this->renderSmarty($controller, $controller->getTemplatePath($retval));
				}
			}
			if ($rettype == 'array')
			{
				// Why?
			}
		}
		else
		{
			$this->render404();
		}
	}
	
	public function render404()
	{
		$c = new ControllerImpl();
		$this->renderSmarty($c, TEMPLATES . '404.tpl');
	}
	
	private function renderSmarty($controller, $template)
	{
		try
		{
			header('Content-Type: text/html; charset=utf-8');
			$controller->smarty->display($template);
		}
		catch (Exception $e)
		{
			die($e->getMessage());
		}
	}
	
	private function getController($name)
	{
		$clazz = ucfirst($name) . 'Controller';
		return new $clazz;
	}
	
	public static function compileUrlRegex($pattern) 
	{
		$escaped = str_replace('/', '\/', $pattern);

		$matches = array();
		preg_match_all("/\:(?P<name>[a-z]+)/", $escaped, $matches);

		for ($i = 0; $i < count($matches[0]); $i++) 
		{	
			$escaped = str_replace($matches[0][$i], "(?P<" . $matches['name'][$i] . ">[a-zA-Z0-9 -_]+)", $escaped);
		}

		return '/^\/*' . $escaped . '\/*$/';
	}
	
}

?>