<?php
namespace Bitrix\Rest\Marketplace\Urls
{
	class Templates
	{
		protected $directory = "marketplace/";
		protected $pages = [
			"index" => "",
			"list" => "list/",
			"detail" => "detail/#ID#/",
			"edit" => "edit/#ID#/"];

		final public static function getInstance()
		{
			static $instance = null;

			if (null === $instance)
			{
				$instance = new static();
			}
			return $instance;
		}

		public function getIndexUrl()
		{
			return $this->getDir().$this->pages["index"];
		}

		public function getDetailUrl($id = null)
		{
			return $this->getReplacedId($this->pages["detail"], $id);
		}

		public function getEditUrl($id = null)
		{
			return $this->getReplacedId($this->pages["edit"], $id);
		}

		public function getDir()
		{
			return SITE_DIR.$this->directory;
		}

		protected function getReplacedId(string $url, $id = null)
		{
			$url = $this->getDir().$url;
			if (!is_null($id))
				$url = str_replace("#ID#", $id, $url);
			return $url;
		}
	}

	class Marketplace extends Templates
	{
		protected $directory = "marketplace/";
		protected $pages = [
			"index" => "",
			"list" => "installed/",
			"detail" => "detail/#ID#/",
			"category" => "category/#ID#/",
			"edit" => "edit/#ID#/"];

		public function getCategoryUrl($id = null)
		{
			return $this->getReplacedId($this->pages["edit"], $id ?: "all");
		}
	}
	class Application extends Templates
	{
		protected $directory = "marketplace/app/";
		protected $pages = [
			"index" => "",
			"list" => "",
			"detail" => "#ID#/",
			"edit" => "edit/#ID#/"];
	}

	class LocalApplication extends Templates
	{
		protected $directory = "marketplace/local/";
		protected $pages = [
			"index" => "",
			"list" => "list/",
			"detail" => "detail/#ID#/",
			"edit" => "edit/#ID#/"];
	}
}
namespace Bitrix\Rest\Marketplace
{
	use Bitrix\Rest\Marketplace\Urls\Marketplace as MarketplaceUrls;
	use Bitrix\Rest\Marketplace\Urls\Application as ApplicationUrls;
	use Bitrix\Rest\Marketplace\Urls\LocalApplication as LocalApplicationUrls;
	class Url
	{
		public static function getCategoryUrl($id = null)
		{
			return MarketplaceUrls::getInstance()->getCategoryUrl($id);
		}

		public static function getApplicationDetailUrl($id = null)
		{
			return MarketplaceUrls::getInstance()->getDetailUrl($id);
		}
		public static function getApplicationUrl($id = null)
		{
			return ApplicationUrls::getInstance()->getDetailUrl($id);
		}
		public static function getApplicationAddUrl()
		{
			return LocalApplicationUrls::getInstance()->getIndexUrl();
		}
		public static function getWidgetAddUrl()
		{
			return "";
		}
	}
}

