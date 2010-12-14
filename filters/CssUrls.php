<?php

namespace WebLoader\Filters;

use WebLoader\WebLoader;
use Nette\Environment;
use Nette\String;

/**
 * Absolutize urls in CSS
 *
 * @author Jan Marek
 * @license MIT
 */
class CssUrls extends \Nette\Object {
	
	/**
	 * Make relative url absolute
	 * @param string image url
	 * @param string single or double quote
	 * @param string absolute css file path
	 * @param string source path
	 * @return string
	 */
	public static function absolutizeUrl($url, $quote, $cssFile, $sourcePath) {
		// is already absolute
		if (preg_match("/^([a-z]+:\/)?\//", $url)) return $url;

		$docroot = realpath(WWW_DIR);
		$basePath = rtrim(Environment::getVariable("baseUri"), '/');

		// inside document root
		if (String::startsWith($cssFile, $docroot)) {
			$path = $basePath . substr(dirname($cssFile), strlen($docroot)) . DIRECTORY_SEPARATOR . $url;

		// outside document root
		} else {
			$path = $basePath . substr($sourcePath, strlen($docroot)) . DIRECTORY_SEPARATOR . $url;
		}

		return $quote === '"' ? addslashes($path) : $path;
	}


	/**
	 * Cannonicalize path
	 * @param string path
	 * @return path
	 */
	private static function cannonicalizePath($path) {
		foreach (explode(DIRECTORY_SEPARATOR, $path) as $i => $name) {
			if ($name === "." || ($name === "" && $i > 0)) continue;

			if ($name === "..") {
				array_pop($pathArr);
				continue;
			}

			$pathArr[] = $name;
		}

		return implode("/", $pathArr);
	}


	/**
	 * Replace urls in css code using callback
	 * @param string $code
	 * @param callback $callback
	 * @return string
	 */
	protected function replaceUrls($code, $callback)
	{
		// thanks to kravco
		$regexp = '~
			(?<![a-z])
			url\(                                     ## url(
				\s*                                   ##   optional whitespace
				([\'"])?                              ##   optional single/double quote
				(   (?: (?:\\\\.)+                    ##     escape sequences
					|   [^\'"\\\\,()\s]+              ##     safe characters
					|   (?(1)   (?!\1)[\'"\\\\,() \t] ##       allowed special characters
						|       ^                     ##       (none, if not quoted)
						)
					)*                                ##     (greedy match)
				)
				(?(1)\1)                              ##   optional single/double quote
				\s*                                   ##   optional whitespace
			\)                                        ## )
		~xs';

		return preg_replace_callback(
			$regexp,
			$callback,
			$code
		);

	}


	/**
	 * Invoke filter
	 * @param string code
	 * @param WebLoader loader
	 * @param string file
	 * @return string
	 */
	public function __invoke($code, WebLoader $loader, $file = null)
	{
		$self = \get_called_class();
		return $this->replaceUrls(
			$code,
			function ($matches) use ($loader, $file, $self) {
				return "url('" . $self::absolutizeUrl($matches[2], $matches[1], $file, $loader->sourcePath) . "')";
			},
			$loader, $file
		);
	}
	
}