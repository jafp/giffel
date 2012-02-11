<?php

/** 
 * Copyright 2011-12 by Jacob Pedersen <jacob@zafri.dk>
 * All rights reserved.
**/

/**
 * Bootloader.
 */
class Bootloader
{
	const QUERY = 'q';
	
	/**
	 * The current url.
	 */
	private $_url = null;

	/**
	 * Mapped controllers by url pattern.
	 */
	private $_mappings = null;

	/**
	 * Indicated if something has been rendered.
	 */
	private $_has_rendered = false;

	/**
	 * Array of the HTTP GET data.
	 */
	private $_get_data = null;

	//private $_classes_and_urls = array();
	
	/**
	 * Constructor. Indexes all controllers.
	 */
	public function __construct()
	{	
		$this->_mappings = array();
		$this->debugging = (defined('DEBUG') && DEBUG == true);
		
		$this->indexControllers(CONTROLLERS);
		
		//$this->cacheMappings();
	}

	/**
	 * Stores the mappings in text file with
	 * urls already compiled  as regular expressions.
	 * The form is like:
	 *
	 *	[controllerName]
	 *	link_to_something/foo/bar /^\/*link_to_something\/foo\/bar\/*$/ $s foo_bar_action
	 *	...
	 *	... 
	 *
	 */
	private function cacheMappings()
	{
		$f = fopen(TEMPORARY . 'mappings.txt', 'w');

		foreach ($this->_classes_and_urls as $clazz => $urls)
		{
			fputs($f, '[' . $clazz . ']\n');
			foreach ($urls as $u)
			{
				fprintf($f, '%s %s $s\n', $u[0], self::compileUrlRegex($u[0]), $u[2]);
			}
		}
		fclose($f);
	}
	
	/**
	 * Recursive indexing of controllers in the given folder.
	 * 
	 * @param path Path to folder with controller implementations.
	 */
	private function indexControllers($path)
	{
		$dir = dir($path);
		if ($dir != null)
		{
			while (($e = $dir->read()) != null)
			{
				if ($e != '.' && $e != '..' && $e != '.gitignore')
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

							//$this->_classes_and_urls[$clazz] = $urls;
						}
					}
				}
			}
		}
	}
	
	/**
	 * Handles the current request, and renders the output.
	 */
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

			/**
			 * Output cache if the controller allows it, and caching
			 * is globally activated.
			 */
			if ($controller::$use_cache && Util::useCache() && Cache::hasCached($query))
			{
				Cache::outputCached($query);
				return;
			}
			
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

			if (Util::useCache() && $controller::$use_cache)
			{	
				Cache::cacheOutput($query, ob_get_contents());
			}	
		}
		else
		{
			$this->render404();
		}
	}
	
	/**
	 * Render a 404 message page.
	 */
	public function render404()
	{
		$c = new ControllerImpl();
		$this->renderSmarty($c, TEMPLATES . '404.tpl');
	}
	
	/**
	 * Render the given template.
	 *
	 * @param controller The controller that handles this request
	 * @param template The name of the template
	 */
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
	
	/**
	 * @param name Name of the controller
	 * @return An instance of the controller with the given name
	 */
	private function getController($name)
	{
		$clazz = ucfirst($name) . 'Controller';
		return new $clazz;
	}
	
	/**
	 * Converts the url pattern to a regular expression.
	 * 
	 * @param pattern String url pattern
	 * @return Regular expression
	 */
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