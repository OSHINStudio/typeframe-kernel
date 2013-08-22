<?php

class Typeframe_Response {
	/**
	 * @var Typeframe_Page
	 */
	private $_page = null;
	private $_controller = null;
	private $_pathInfo = '';
	private $_template = null;
	private $_url = '';
	private $_pagemill = null;
	private $_redirectHeader = null;
	private $_redirectLocation = null;
	private $_redirectResponseCode = null;
	private static $_current = array();
	private $_errors = array();
	private $_callbacks = array();
	private $_superglobals = array();
	public static function Current() {
		if (!count(self::$_current)) {
			self::$_current[] = Typeframe_Response::Detect();
		}
		return self::$_current[count(self::$_current) - 1];
	}
	/**
	 * Get the Typeframe_Response object for the specified URL.
	 * @param string $url
	 * @return \Typeframe_Response
	 */
	public static function At($url) {
		$response = new Typeframe_Response();
		if ($url == '') $url = '/';
		if (substr($url, 0, 1) == '/') {
			if (isset($_SERVER['HTTP_HOST'])) {
				$url = $_SERVER['HTTP_HOST'] . $url;
			} else {
				$url = (defined('TYPEF_HOST') ? TYPEF_HOST : '') . $url;
			}
		} else if (substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://') {
			$parts = parse_url($url);
			$url = $parts['domain'] . $parts['path'] . (!empty($parts['query']) ? '?' . $parts['query'] : '');
		}
		if ($url != '/' && substr($url, -1, 1) == '/') {
			$url = substr($url, 0, -1);
		}
		$response->_url = $url;
		if (substr($response->_url, -1) == '/') {
			$response->_url = substr($response->_url, 0, -1);
		}
		$pages = Typeframe::Registry()->pages();
		if (substr($url, 0, 1) != '/') {
			$host = substr($url, 0, strpos($url, '/'));
		} else {
			$host = '';
		}
		$uri = (strpos($url, '?') !== false ? substr($url, 0, strlen(strpos($url, '?'))) : $url);
		$uri = preg_replace('/\/+/', '/', $uri);
		//if (substr($uri, 0, 1) == '/') $uri = substr($uri, 1);
		if (substr($uri, 0, -1) == '/') $uri = substr($uri, 0, -1);
		$relativeUri = substr($uri, strpos($uri, '/') + strlen(TYPEF_WEB_DIR));
		$dirs = explode('/', $relativeUri);
		while (count($dirs)) {
			$uri = (strpos($url, '?') !== false ? substr($url, 0, strlen(strpos($url, '?'))) : $url);
			$uri = preg_replace('/\/+/', '/', $uri);
			//if (substr($uri, 0, 1) == '/') $uri = substr($uri, 1);
			if (substr($uri, 0, -1) == '/') $uri = substr($uri, 0, -1);
			$relativeUri = substr($uri, strpos($uri, '/') + strlen(TYPEF_WEB_DIR));
			$currentPath = $host . TYPEF_WEB_DIR . '/' . implode('/', $dirs);
			$currentPath = preg_replace('/\/+/', '/', $currentPath);
			if (!isset($pages[$currentPath])) {
				$currentPath = substr($currentPath, strlen($host));
				$uri = substr($uri, strlen($host));
			}
			if (isset($pages[$currentPath])) {
				$currentPage = $pages[$currentPath];
				$uriWithoutDomain = substr($uri, strlen($host));
				$controllerPath = null;
				if (strlen($uri) > strlen($currentPath) && strpos($uri, $currentPath) === 0) {
					$controllerPath = TYPEF_SOURCE_DIR . '/controllers' . $currentPage->application()->base() . substr($uri, strlen($currentPage->uri()));
					if (file_exists($controllerPath . '.php') && is_file($controllerPath . '.php')) {
						$response->_controller = $controllerPath . '.php';
						$response->_page = $currentPage;
						break;
					} else if (file_exists($controllerPath . '/index.php') && is_file($controllerPath . '/index.php')) {
						$response->_controller = $controllerPath . '/index.php';
						$response->_page = $currentPage;
						break;
					} else {
						$controllerPath = null;
						foreach ($currentPage->extenders() as $extender) {
							if (substr($uri, 0, strlen($host)) == $host) {
								$pathinfo = substr($uri, strlen($host) + strlen($extender->path()));
							} else {
								$pathinfo = substr($uri, strlen($extender->path()));
							}
							//echo "<h1>Gonna try to find {$pathinfo}...</h1>";
							//if (substr($uri, strlen($host), strlen($extender->path())) == $extender->path()) {
								//$extended = substr($uri, strlen($host) + strlen($extender->path()));
								//if (preg_match($extender->preg(), $extended)) {
								if (preg_match($extender->preg(), $pathinfo)) {
									$response->_pathInfo = substr($pathinfo, 1);
									if ($extender->redirect()) {
										$controllerPath = TYPEF_SOURCE_DIR . '/controllers' . $extender->redirect();
									} else {
										$controllerPath = TYPEF_SOURCE_DIR . '/controllers' . $currentPage->application()->base();
									}
									break;
								}
							//}
						}
					}
				} else {
					if ($currentPath == $uri) {
						$controllerPath = TYPEF_SOURCE_DIR . '/controllers' . $currentPage->application()->base();
						if (file_exists($controllerPath . '.php') && is_file($controllerPath . '.php')) {
							$response->_controller = $controllerPath . '.php';
							$response->_page = $currentPage;
							break;
						} else if (file_exists($controllerPath . '/index.php') && is_file($controllerPath . '/index.php')) {
							$response->_controller = $controllerPath . '/index.php';
							$response->_page = $currentPage;
							break;
						}
					} else {
						$controllerPath = TYPEF_SOURCE_DIR . '/controllers' . $currentPage->application()->base();
					}
				}
				if (!is_null($controllerPath)) {
					if (file_exists($controllerPath . '.php') && is_file($controllerPath . '.php')) {
						$response->_controller = $controllerPath . '.php';
					} else if (file_exists($controllerPath . '/index.php') && is_file($controllerPath . '/index.php')) {
						$response->_controller = $controllerPath . '/index.php';
					}
				}
				if ($response->_controller) {
					$response->_page = $currentPage;
					break;
				}
			}
			array_pop($dirs);
		}
		if ($response->_controller && $response->_page) {
			$response->_controller = preg_replace('/\/+/', '/', $response->_controller);
			if (!$response->_page->allow()) {
				$response->_return403();
			}
		} else {
			$response->_return404();
		}
		return $response;
	}
	public static function Configure(Typeframe_Page $page, $url = null, $controller = null) {
		$response = new Typeframe_Response();
		$response->_page = $page;
		if (is_null($url)) {
			$url = $page->uri();
		} else {
			$response->_url = $url;
		}
		if (is_null($controller)) {
			// TODO: Detecting the controller path needs to be more robust.
			$response->_controller = TYPEF_SOURCE_DIR . '/controllers' . $page->application()->base() . '/index.php';
		} else {
			$response->_controller = $controller;
		}
		$response->_pathInfo = substr($url, strlen($page->uri()));
		return $response;
	}
	public static function Detect() {
		$url = (isset($_SERVER['PATH_INFO']) ?
				$_SERVER['PATH_INFO'] :
				(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '')
		);
		return self::At($url);
	}
	private function __construct($url = null) {
		$this->_pagemill = new Pagemill();
	}
	private function _return403() {
		$this->_page = new Typeframe_Page(new Typeframe_Application('403'), $this->_url);
		$this->_controller = TYPEF_SOURCE_DIR . '/controllers/403.php';
	}
	private function _return404() {
		$this->_page = new Typeframe_Page(new Typeframe_Application('404'), $this->_url);
		$this->_controller = TYPEF_SOURCE_DIR . '/controllers/404.php';
	}
	/**
	 * 
	 * @return Typeframe_Page
	 */
	public function page() {
		return $this->_page;
	}
	public function application() {
		return $this->_page->application();
	}
	public function applicationUri() {
		return $this->_page->uri();
	}
	public function controller() {
		return $this->_controller;
	}
	public function controllerPath() {
		$parts = pathinfo($this->controller());
		$path = substr($parts['dirname'], strlen(TYPEF_SOURCE_DIR . '/controllers'));
		if ($parts['filename'] != 'index') {
			$path = $path . "/{$parts['filename']}";
		}
		return $path;
	}
	public function pathInfo() {
		return $this->_pathInfo;
	}
	public function settings() {
		return $this->_page->settings();
	}
	public function pageid() {
		return $this->_page->pageid();
	}
	public function siteid() {
		if (!is_null($this->_page->siteid())) {
			return $this->_page->siteid();
		} else {
			if (!defined('TYPEF_HOST')) {
				return 0;
			}
			if  ($_SERVER['HTTP_HOST'] == TYPEF_HOST) {
				return 0;
			}
			$sites = new Model_Site();
			$sites->where('domain = ?', $_SERVER['HTTP_HOST']);
			foreach($sites->select() as $site) {
				if ($site['directory'] == '' || strpos($this->uri(), $site['directory']) === 0) {
					return $site['id'];
				}
			}
		}
	}
	public function uri() {
		return substr($this->_url, strpos($this->_url, '/'));
	}
	public function setSuperglobal($name, $array) {
		static $whitelist = array('GET', 'POST', 'SESSION', 'COOKIE', 'SERVER');
		$name = strtoupper($name);
		if (in_array($name, $whitelist)) {
			$this->_superglobals[$name] = $array;
		} else {
			throw new Exception("Invalid superglobal {$name} specified in Typeframe_Response->execute()");
		}
	}
	public function execute($return = false) {
		set_error_handler(array($this, '_errorHandler'));
		if ($return) {
			ob_start();
		}
		$backups = array();
		foreach($this->_superglobals as $key => $value) {
			eval('$backups[\'' . $key . '\'] = $_' . $key . ';');
			// The $_SERVER superglobal gets merged instead of replaced.
			if ($key == 'SERVER') {
				eval('$value = array_merge($_SERVER, $value);');
			}
			eval('$_' . $key . ' = $value;');
		}
		if (isset($this->_superglobals['GET']) || isset($this->_superglobals['POST'])) {
			$backups['REQUEST'] = $_REQUEST;
			$_REQUEST = array();
			$order = array('_GET', '_POST');
			foreach ($order as $var) {
				eval('$tmp = $' . $var . ';');
				foreach ($tmp as $key => $value) {
					$_REQUEST[$key] = $value;
				}
			}
		}
		if (!isset($backups['SESSION']) && session_id() == '' && !headers_sent()) {
			session_start();
		}
		self::$_current[] = $this;
		$this->_initialize();
		self::_Include($this->_controller, $this->_pagemill);
		if (isset($this->_callbacks[$this->controllerPath()])) {
			foreach ($this->_callbacks[$this->controllerPath()] as $callback) {
				$callback();
			}
		}
		Typeframe::Timestamp('Scripts and controller executed');
		// TODO: Process output (Pagemill template)
		$selectedTemplate = null;
		if (is_null($this->_redirectLocation)) {
			if (is_null($this->_template)) {
				$selectedTemplate = $this->_resolveTemplate($this->_getDefaultTemplate());
			} else {
				$selectedTemplate = $this->_resolveTemplate($this->_template);
			}
		} else {
			$selectedTemplate = $this->_resolveTemplate('/redirect.html');
			if (!$return && !requestIsAjax()) {
				if ($this->_redirectHeader) {
					header('Location: ' . $this->_redirectLocation);
				}
				if (isset($this->_redirectResponseCode) && (!requestIsAjax())) {
					http_response_code($this->_redirectResponseCode);
				}
			}
		}
		if ($selectedTemplate) {
			$pm = Typeframe::Pagemill();
			$pm->writeFile($selectedTemplate, false, !isset($_SERVER['SHELL']));
		}
		array_pop(self::$_current);
		if ($this->_errors) {
			echo "\n<!--[errors]\n";
			foreach ($this->_errors as $error) {
				echo "{$error}\n";
			}
			echo "[/errors]-->\n";
		}
		foreach ($backups as $key => $value) {
			eval('$_' . $key . ' = $value;');
		}
		if (!isset($backups['SESSION']) && session_id() != '') {
			if (TYPEF_WEB_DIR != '') session_set_cookie_params(ini_get('session.cookie_lifetime'), TYPEF_WEB_DIR);
			session_write_close();
		}
		restore_error_handler();
		if ($return) {
			return ob_get_clean();
		}
	}
	private function _initialize() {
		$this->_callbacks = array();
		self::_Include(TYPEF_SOURCE_DIR . '/scripts/globals.php', $this->_pagemill);
	}
	public function ping() {
		self::$_current[] = $this;
		$this->_initialize();
		array_pop(self::$_current);
	}
	private function _resolveTemplate($path) {
		$skin = Typeframe_Skin::Current();
		if (file_exists(TYPEF_DIR . '/skins/' . $skin . '/templates' . $path) && is_file(TYPEF_DIR . '/skins/' . $skin . '/templates' . $path)) {
			return TYPEF_DIR . '/skins/' . $skin . '/templates' . $path;
		}
		if (file_exists(TYPEF_DIR . '/skins/default/templates' . $path) && is_file(TYPEF_DIR . '/skins/default/templates' . $path)) {
			return TYPEF_DIR . '/skins/default/templates' . $path;
		}
		if (file_exists(TYPEF_SOURCE_DIR . '/templates' . $path) && is_file(TYPEF_SOURCE_DIR . '/templates' . $path)) {
			return TYPEF_SOURCE_DIR . '/templates' . $path;
		}
		return false;
	}
	/**
	 * Redirect the request to a new URL.
	 * @param string $message Message to display.
	 * @param string $url The URL where the request will be redirected.
	 * @param int $sec Seconds to pause (0 for instant, -1 to wait for user interaction)
	 * @param bool $addHeader True to add a Location header to the response
	 * @param int $responseCode The HTTP response code (0 to use existing code, usu. 200)
	 * @param string $postOverride The name of a POST variable that can override the $url parameter
	 */
	public function redirect($message, $url, $sec = TYPEF_DEFAULT_REDIRECT_TIME, $addHeader = false, $responseCode = 0, $postOverride = 'post_redir') {
		if ( ($postOverride) && (!empty($_POST[$postOverride])) ) {
			// TODO: This is weird.  Why isn't this value decoded already?
			$url = urldecode($_POST[$postOverride]);
		}
		if ( (substr($url, 0, 7) != "http://") && (substr($url, 0, 8) != "https://") ) {
			if ($_SERVER['SERVER_PORT'] != '443') {
				$url = "http://" . $_SERVER["SERVER_NAME"] . $url;
			} else {
				$url = "https://" . $_SERVER["SERVER_NAME"] . $url;
			}
		}
		if ( ($sec == 0) || ($addHeader == true) ) {
			// Include a Location redirect in the HTML headers
			$this->_redirectHeader = true;
		} else {
			$this->_redirectHeader = false;
		}
		$this->_redirectLocation = $url;
		$this->_redirectResponseCode = ($responseCode ? $responseCode : 302);
		$this->_pagemill->setVariable("message", $message);
		$this->_pagemill->setVariable("url", $url);
		$this->_pagemill->setVariable("time", $sec);
		// TODO: This might be a bad place for this line.  Technically, I'm not sure that
		// HTMLTag should be a required part of the kernel.
		//Typeframe_Tag_Stylesheets::AddStylesheet('/redirect.css');
	}
	public function redirected() {
		return (!empty($this->_redirectLocation));
	}
	private static function _Include($file, Pagemill $pm) {
		$db = Typeframe::Database();
		include($file);
	}
	public function setPageTemplate($file) {
		$this->_template = $file;
	}
	public function getPageTemplate() {
		if (is_null($this->_template)) {
			return $this->_getDefaultTemplate();
		}
		return $this->_template;
	}
	private function _getDefaultTemplate() {
		return substr($this->_controller, strlen(TYPEF_SOURCE_DIR . '/controllers'), -4) . '.html';
	}
	/**
	 * @return Pagemill
	 */
	public function pagemill() {
		return $this->_pagemill;
	}
	public function _errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
		if (ini_get('error_reporting') != 0) {
			$this->_errors[] = "Error #{$errno}: {$errstr} in {$errfile}, line {$errline}";
		}
	}
	/**
	 * Register a function to execute on the specified application path
	 * after the controller has been executed.
	 * @param type $path The application path (e.g.: /application/base)
	 * @param type $callback The function to execute
	 */
	public function registerCallback($path, $callback) {
		if (substr($path, -1) == '/') {
			$path = substr($path, 0, -1);
		}
		if (substr($path, 0, 1) != '/') {
			$path = "/{$path}";
		}
		if (!isset($this->_callbacks[$path])) {
			$this->_callbacks[$path] = array();
		}
		$this->_callbacks[$path][] = $callback;
	}
}
