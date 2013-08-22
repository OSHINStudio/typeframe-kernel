<?php

class Typeframe_Registry {
	private $_applications = array();
	/**
	 * @var Typeframe_Page[]
	 */
	private $_pages = array();
	private $_plugins = array();
	private $_configs = array();
	private $_configDefaults = array();
	private $_configSettings = array();
	private $_extenders = array();
	private $_handlers = array();
	private $_tags = array();
	
	public function applications() {
		return $this->_applications;
	}
	public function application($name) {
		if (isset($this->_applications[$name])) {
			return $this->_applications[$name];
		}
		return null;
	}
	/**
	 * @return Typeframe_Page[]
	 */
	public function pages() {
		return $this->_pages;
	}
	/**
	 * @deprecated
	 */
	public function applicationAt($uri) {
		// TODO: What? Shouldn't this be responseAt($uri)?
		return $this->responseAt($uri);
	}
	/**
	 * Get the Typeframe response for the specified path.
	 * @param string $uri
	 * @return \Typeframe_Response
	 */
	public function responseAt($uri) {
		return Typeframe_Response::At($uri);
	}
	/*
	 * @return Typeframe_Application_Config[]
	 */
	public function configs() {
		return $this->_configs;
	}
	public function getConfig($name) {
		if (isset($this->_configs[$name])) {
			return $this->_configs[$name];
		}
		return null;
	}
	public function getConfigValue($name) {
		if (isset($this->_configSettings[$name])) {
			return $this->_configSettings[$name];
		} else if (isset($this->_configDefaults[$name])) {
			return $this->_configDefaults[$name];
		}
		return null;
	}
	public function plugins() {
		return $this->_plugins;
	}
	public function __construct($useCache = true) {
		// TODO: Temporarily disabling cache
		$this->_initializeRegistry();
		return;
		if ($useCache) {
			$useCache = $this->_validateCache();
		}
		if (!$useCache) {
			Typeframe::Timestamp('Loading registry from XML files');
			$this->_initializeRegistry();
			file_put_contents(TYPEF_DIR . '/files/cache/registry.ser', serialize($this));
			@chmod(TYPEF_DIR . '/files/cache/registry.ser', 0666);
		} else {
			Typeframe::Timestamp('Loading registry from cache');
			$this->_loadCachedRegistry();
		}
		Typeframe::Timestamp('Registry loaded');
	}
	private function _validateCache() {
		if (!file_exists(TYPEF_DIR . '/files/cache/registry.ser')) {
			return false;
		}
		$cacheTime = filemtime(TYPEF_DIR . '/files/cache/registry.ser');
		$files = scandir(TYPEF_SOURCE_DIR . '/registry');
		foreach ($files as $f) {
			if (strpos($f, ".reg.xml") !== false) {
				$thisTime = filemtime(TYPEF_SOURCE_DIR . '/registry/' . $f);
				if ($thisTime > $cacheTime) {
					return false;
				}
			}
		}
		return true;
	}
	private function _loadCachedRegistry() {
		$cache = unserialize(file_get_contents(TYPEF_DIR . '/files/cache/registry.ser'));
		$this->_applications = $cache->_applications;
		$this->_pages = $cache->_pages;
		$this->_plugins = $cache->_plugins;
		$this->_configs = $cache->_configs;
		$this->_configDefaults = $cache->_configDefaults;
		$this->_configSettings = $cache->_configSettings;
		$this->_extenders = $cache->_extenders;
		$this->_handlers = $cache->_handlers;
		$this->_tags = $cache->_tags;
	}
	private function _initializeRegistry() {
		$dir = scandir(TYPEF_SOURCE_DIR . '/registry');
		$registry = simplexml_load_string('<registry/>');
		foreach ($dir as $file) {
			if (is_file(TYPEF_SOURCE_DIR . "/registry/{$file}")) {
				if (substr($file, -8) == '.reg.xml') {
					$package = substr($file, 0, -8);
					$xml = simplexml_load_file(TYPEF_SOURCE_DIR . "/registry/{$file}");
					foreach ($xml->application as $app) {
						$app['package'] = $package;
						if ((string)$app['handler']) {
							$this->_handlers[(string)$app['base']] = (string)$app['handler'];
						}
					}
					simplexml_merge($registry, $xml);
				}
			}
		}
		$this->_parseRegistry($registry);
		$this->_loadUserPages();
		$configs = new BaseModel_Config();
		foreach ($configs->select() as $c) {
			$this->_configSettings[$c['configname']] = $c['configvalue'];
		}
	}
	private function _parseRegistry($xml) {
		// TODO: This is a clearly inefficient way of registering site pages
		// for hard-mapped applications. On the other hand, it might be efficient
		// enough after the registry is cached.
		$sites = null;
		if (class_exists('Model_Site')) {
			$sites = new Model_Site();
		}
		foreach ($xml->application as $app) {
			if ($app['map'] == 'hard') {
				// Load application as page
				$tmpApp = $this->_loadApplication($app);
				$this->_pages[TYPEF_WEB_DIR . $app['base']] = new Typeframe_Page($tmpApp, TYPEF_WEB_DIR . $app['base'], array('siteid' => 0));
				if (!is_null($sites)) {
					foreach ($sites->select() as $site) {
						$this->_pages[$site['domain'] . TYPEF_WEB_DIR . ($site['directory'] ? "/{$site['directory']}" : '') . $app['base']] = new Typeframe_Page($tmpApp, TYPEF_WEB_DIR . ($site['directory'] ? "/{$site['directory']}" : '') . $app['base'], array('siteid' => $site['id']));
					}
				}
			} else {
				// Load soft application
				$this->_applications["{$app['name']}"] = $this->_loadApplication($app);
			}
		}
	}
	private function _loadUserPages() {
		$pages = new Model_Page();
		foreach ($pages->select() as $page) {
			$application = $this->application($page['application']);
			if ($application) {
				$settings = $page['settings'];
				$settings['pageid'] = $page['pageid'];
				$settings['siteid'] = ($page['siteid'] ? $page['siteid'] : 0);
				$settings['nickname'] = $page['nickname'];
				$settings['skin'] = $page['skin'];
				$site = $page['site']['domain'];
				if (!$site) $site = ( defined('TYPEF_HOST') ? TYPEF_HOST : (isset($SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') );
				$directory = $page['directory'];
				if ($directory == '/') $directory = '';
				$this->_pages[$site . TYPEF_WEB_DIR . $directory . $page['uri']] = new Typeframe_Page($application, TYPEF_WEB_DIR . $directory . $page['uri'], $settings);
				//$this->_pages[TYPEF_WEB_DIR . $page['uri']] = new Typeframe_Page($application, TYPEF_WEB_DIR . $page['uri'], $settings);
			} else {
				trigger_error("Application '{$page['application']}' is not registered");
			}
		}
	}
	/**
	 * Load an application from XML
	 * @param SimpleXMLElement $xml The application element
	 * @param string $package The package name
	 * @return Typeframe_Application
	 */
	private function _loadApplication(SimpleXMLElement $xml) {
		$name = (string)$xml['name'];
		$package = (string)$xml['package'];
		$base = (string)$xml['base'];
		$title = (string)$xml['title'];
		$icon = (string)$xml['icon'];
		$handler = (string)$xml['handler'];
		$extenders = array();
		$category = (string)$xml['category'];
		$admin = (string)$xml['admin'];
		if (!$handler) {
			$parts = explode('/', $base);
			while (count($parts) > 0) {
				$handlerBase = implode('/', $parts);
				if (isset($this->_handlers[$handlerBase])) {
					$handler = $this->_handlers[$handlerBase];
					break;
				}
				array_pop($parts);
			}
			if (!$handler) $handler = 'Typeframe_Application_Handler';
		}
		foreach ($xml->extend as $extend) {
			$extenders[] = new Typeframe_Extender((string)$extend['path'], (string)$extend['preg'], (string)$extend['redirect']);
		}
		$app = new Typeframe_Application($name, $base, $title, $icon, $handler, $extenders, $package, $category, $admin);
		foreach ($xml->config as $config) {
			$this->_configs["{$config['name']}"] = $this->_loadConfig($config, $name);
		}
		foreach ($xml->plugin as $plugin) {
			$this->_plugins[(string)$plugin['name']] = $this->_loadPlugin($plugin, $name);
		}
		foreach ($xml->tag as $tag) {
			$this->_tags[] = array('name' => (string)$tag['name'], 'class' => (string)$tag['class']);
		}
		return $app;
	}
	private function _loadConfig(SimpleXMLElement $xml, $application) {
		$name = (string)$xml['name'];
		$items = array();
		foreach ($xml->item as $item) {
			$options = array();
			foreach ($item->option as $option) {
				// TODO: Option arguments
				$options[] = new Typeframe_Application_Config_Item_Option((string)$option['value'], (string)$option['caption']);
			}
			$items[] = new Typeframe_Application_Config_Item((string)$item['name'], (string)$item['caption'], (string)$item['type'], (string)$item['default'], $options);
			$this->_configDefaults[(string)$item['name']] = (string)$item['default'];
		}
		$redirect = (string)$xml['redirect'];
		$config = new Typeframe_Application_Config($name, $items, $application, $redirect ? TYPEF_WEB_DIR . $redirect : '');
		return $config;
	}
	private function _loadConfigScripts() {
		$dir = scandir(TYPEF_SOURCE_DIR . '/scripts/define.d');
		foreach ($dir as $file) {
			if (substr($file, 0, 1) != '.' && is_file(TYPEF_SOURCE_DIR . '/scripts/define.d/' . $file)) {
				require_once(TYPEF_SOURCE_DIR . '/scripts/define.d/' . $file);
			}
		}
	}
	private function _loadPlugin(SimpleXMLElement $xml, $application) {
		$name = (string)$xml['name'];
		$cls = (string)$xml['class'];
		$plugin = new Typeframe_Application_Plugin($name, $cls, $application);
		return $plugin;
	}
	public function getPluginSignature($name) {
		if (isset($this->_plugins[$name])) {
			return $this->_plugins[$name];
		}
		return null;
	}
	public function purgeRegistryCache() {
		if (file_exists(TYPEF_DIR . '/files/cache/registry.ser')) {
			unlink(TYPEF_DIR . '/files/cache/registry.ser');
		}
	}
	/**
	 * Get an array of URLs for all pages using an application.
	 * @param string $appName The name of the application
	 * @return array
	 */
	public function applicationUrls($appName) {
		$array = array();
		foreach ($this->_pages as $url => $page) {
			if ($page->application()->name() == $appName) {
				$array[] = $url;
			}
		}
		return $array;
	}
	public function tags() {
		return $this->_tags;
	}
}
