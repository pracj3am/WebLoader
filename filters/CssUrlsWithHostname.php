<?php
namespace WebLoader\Filters;

use WebLoader\WebLoader;
use Nette\Environment;
use Nette\String;

/**
 * Absolutize urls in CSS and adds hostnames to parallelize downloads
 * @see http://code.google.com/speed/page-speed/docs/rtt.html#ParallelizeDownloads
 * @author Jan Prachař
 */
class CssUrlsWithHostname extends CssUrls {

	private static $urls = array();


	/**
	 * Make relative url absolute with hostname
	 * @param string image url
	 * @param string single or double quote
	 * @param string absolute css file path
	 * @param string source path
	 * @return string
	 */
	public static function absolutizeUrl($url, $quote, $cssFile, $sourcePath) {
		$path = parent::absolutizeUrl($url, $quote, $cssFile, $sourcePath);
		$host = Environment::getHttpRequest()->getUri()->getHost();
		$hostUri = \str_replace(
				$host,
				\substr_replace($host, 's' . self::$urls[$url], 0, \strcspn($host, '.')),
				Environment::getHttpRequest()->getUri()->getHostUri()
		);

		return ($quote === '"' ? addslashes($hostUri) : $hostUri) . $path;
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
		$urls = &self::$urls;
		$this->replaceUrls($code, function($matches) use (&$urls) {$urls[$matches[2]] = TRUE; return '';});

		$i = $j = 1;
		foreach ($urls as $url=>$x) {
			$urls[$url] = $j;
			if (++$i % 10 == 1) {
				$j++;
			}
		}
		return parent::__invoke($code, $loader, $file);
	}
	
}