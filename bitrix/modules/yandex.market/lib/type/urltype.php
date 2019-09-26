<?php

namespace Yandex\Market\Type;

use Bitrix\Main;
use Yandex\Market;

class UrlType extends AbstractType
{
	protected $idnCache = [];

	public function format($value, array $context = [], Market\Export\Xml\Reference\Node $node = null, Market\Result\XmlNode $nodeResult = null)
	{
		$result = $value;

		if (strpos($result, '://') === false) // is not absolute url
		{
			$parsedPath = $this->splitPath($result);

			if (strpos($parsedPath['PATH'], '/') !== 0)
			{
				$parsedPath['PATH'] = '/' . $parsedPath['PATH'];
			}

			$result =
				$this->idnDomain($context['DOMAIN_URL'])
				. $this->encodeUrlPath($parsedPath['PATH'])
				. $parsedPath['QUERY'];
		}
		else if ($parsedUrl = $this->parseUrl($result))
		{
			$result =
				$this->idnDomain($parsedUrl['DOMAIN'])
				. $this->encodeUrlPath($parsedUrl['PATH'])
				. $parsedUrl['QUERY'];
		}

		$result = str_replace('&', '&amp;', $result); // escape xml entities

		return $result;
	}

	protected function idnDomain($domain)
	{
		if (isset($this->idnCache[$domain]))
		{
			$result = $this->idnCache[$domain];
		}
		else
		{
			$errorList = [];
			$idnDomain = \CBXPunycode::ToASCII($domain, $errorList);
			$result = ($idnDomain !== false ? $idnDomain : $domain);

			$this->idnCache[$domain] = $result;
		}

		return $result;
	}

	protected function encodeUrlPath($path)
	{
		$result = $path;

		if (preg_match('#[^A-Za-z0-9-_.~/?=&]#', $path)) // has invalid chars
		{
			$charset = $this->getCharset();

			$result = \CHTTP::urnEncode($path, $charset);
		}

		return $result;
	}

	protected function parseUrl($url)
	{
		$result = null;

		if (preg_match('#^(https?://[^/?\#]+)([^?\#]*)(.*)?$#i', $url, $matches))
		{
			$result = [
				'DOMAIN' => $matches[1],
				'PATH' => $matches[2],
				'QUERY' => $matches[3]
			];
		}

		return $result;
	}

	protected function splitPath($path)
	{
		$questionPosition = strpos($path, '?');
		$result = [
			'PATH' => $path,
			'QUERY' => ''
		];

		if ($questionPosition !== false)
		{
			$result['PATH'] = substr($path, 0, $questionPosition);
			$result['QUERY'] = substr($path, $questionPosition);
		}

		return $result;
	}

	protected function getCharset()
	{
		$result = false;

		if (!Main\Application::isUtfMode())
		{
			$result = 'UTF-8';
		}

		return $result;
	}
}