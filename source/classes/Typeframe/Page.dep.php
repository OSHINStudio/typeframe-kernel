<?php
class Typeframe_Page {
	private $_application;
	private $_uri;
	private $_controller;
	private $_stopped = false;
	private $_template = null;
	private $_executedTemplate = null;
	private $_redirectLocation = null;
	private $_redirectResponseCode = null;
	private $_redirectHeader = false;
	public function __construct(Typeframe_Application_Signature $application, $uri, $controller) {
		$this->_application = $application;
		$this->_uri = $uri;
		$this->_controller = $controller;
	}
	/**
	* Get the application associated with the page.
	* @return Typeframe_Application_Signature
	*/
	public function application() {
		return $this->_application;
	}
	/**
	* Get an associative array of the page's application settings
	* @return assoc
	*/
	public function settings() {
		return $this->_application->settings();
	}
	/**
	* Get the URI for the page (including the root directory and trailing slash)
	* @return string
	*/
	public function uri() {
		return $this->_uri;
	}
	/**
	* Get the URI for the page (including the root directory, but without a trailing slash)
	* @return string
	*/
	public function webdir() {
		if ($this->_uri == '/') return '';
		return $this->_uri;
	}
	/**
	 * Get the full path to the application's controller script.
	 * @return string
	 */
	public function controller() {
		return $this->_controller;
	}
	/**
	 * Get the mappable path to the controller (e.g., /foo/bar for foo/bar.php)
	 * @return string
	 */
	public function path() {
		static $path = null;
		if (is_null($path)) {
			$path = substr($this->_controller, strlen(TYPEF_SOURCE_DIR . '/controllers'));
			$path = preg_replace('/([a-z0-9\-\_\.\/]*?)(\/index)?(.php)/i', '$1', $path);
		}
		return $path;
	}
	public function relativeUri() {
		return substr($this->_uri, strlen(TYPEF_WEB_DIR));
	}
	/**
	 * Get the additional path info passed to the controller.
	 * @return string
	 */
	public function pathInfo() {
		//return substr($_SERVER['PATH_INFO'], strlen($this->_uri) + 1);
		global $_extendedPathUsed;
		$qm = strpos($_SERVER['REQUEST_URI'], '?');
		$uri = ($qm === false ? $_SERVER['REQUEST_URI'] : substr($_SERVER['REQUEST_URI'], 0, $qm));
		$uri = urldecode($uri);
		if (substr($uri, 0, strlen(TYPEF_WEB_DIR)) != TYPEF_WEB_DIR) {
			trigger_error('The TYPEF_WEB_DIR constant (usu. defined in typeframe.config.php) appears to be incorrect.');
			return '';
		}
		if (substr($uri, strlen($uri) - 1, 1) == '/') {
			$uri = substr($uri, 0, strlen($uri) - 1);
		}
		if ($uri == '') $uri = '/';
		//die($_extendedPathUsed->path());
		$uri = substr($uri, strlen(TYPEF_WEB_DIR));
		if ($_extendedPathUsed) {
			//print_r($_extendedPathUsed);
			//exit;
			//$uri = substr($uri, strlen($_extendedPathUsed->path()));
			$extraPath = substr($_extendedPathUsed->path(), strlen($this->application()->base()));
			$uri = substr($uri, strlen($extraPath));
			$settings = $this->application()->settings();
			if (!empty($settings['uri'])) {
				$uri = substr($uri, strlen($settings['uri']) + 1);
			} else {
				$uri = substr($uri, strlen($this->application()->relativeUri()));
			}
		} elseif(strpos($uri, $this->relativeUri()) !== false) {
			$uri = substr($uri, strlen($this->relativeUri()));
		}
		// Trim off the beginning '/'
		if ($uri{0} == '/') $uri = substr($uri, 1);
		return $uri;
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
		Typeframe::Pagemill()->setVariable("message", $message);
		Typeframe::Pagemill()->setVariable("url", $url);
		Typeframe::Pagemill()->setVariable("time", $sec);
		Typeframe::SetPageTemplate('/redirect.html');
		// TODO: This might be a bad place for this line.  Technically, I'm not sure that
		// HTMLTag should be a required part of the kernel.
		//Pagemill_Tag_Stylesheets::AddStylesheet('/redirect.css');
	}
    /**
     * Check if this page has been redirected.
     * @return bool
     */
     public function redirected() {
         return (!empty($this->_redirectLocation));
     }
	/**
	 * Get status of execution (true for stopped).
	 * @return bool
	 */
	public function stopped() {
            return $this->_stopped;
	}
	/**
	 * Stop execution.  Any pending triggers will not be executed.  This function will also block execution of the page's controller if it has not been executed yet.
	 */
	public function stop() {
		$this->_stopped = true;
	}
	private static function _IncludeController($filename) {
		// TODO: Get rid of these variables.  They should be declared explicitly in the controller if necessary.
		$pm = Typeframe::Pagemill();
		$db = Typeframe::Database();
		include($filename);
	}
	public function execute() {
		static $executed = false;
		if (!$executed) {
			$executed = true;
			$this->_executeApplicationCode();
			Typeframe::Timestamp('Controller and triggers executed');
			$selectedTemplate = $this->_template;
			if ( (!$selectedTemplate) && ($this->_application->name()) ) {
				$pathinfo = pathinfo($this->_controller);
				$selectedTemplate = (substr($pathinfo['dirname'], strlen(TYPEF_SOURCE_DIR . '/controllers')) . '/' . $pathinfo['filename'] . '.html');
				$finalTemplate = Typeframe::FindTemplate($selectedTemplate);
				if (is_null($finalTemplate)) {
					$selectedTemplate = null;
				}
			} else {
				$finalTemplate = Typeframe::FindTemplate($selectedTemplate);
			}
			// $selectedTemplate tells us if a template was selected (either explicitly or automatically).
			// $finalTemplate tells us if the template is valid.
			if ($selectedTemplate) {
				if ($finalTemplate) {
					Typeframe::Timestamp('Starting page render');
					if ($this->_redirectLocation) {
						// If there were errors sent to the browser (i.e., output has already started), don't use meta redirect
						//if ( (headers_sent()) || (ob_get_length()) ) {
						//	Typeframe::Pagemill()->setVariable("time", 0);
						//}
						if ( (Typeframe::Pagemill()->getVariable('time') == 0) && ($_SERVER['REQUEST_METHOD'] == 'POST') && (!headers_sent()) && (!requestIsAjax()) ) {
							$_SESSION['typef_redirect_message'] = Typeframe::Pagemill()->getVariable('message');
						}
					}
					
					$this->_executedTemplate = $finalTemplate;
					if ($this->_redirectHeader) {
						if ( (!headers_sent()) && (!requestIsAjax()) ) {
							header('Location: ' . $this->_redirectLocation, true, $this->_redirectResponseCode);
						}
					}
					Typeframe::Pagemill()->writeFile($finalTemplate);
					// TODO: Another quick and dirty hack to make JavaScript templates work.
					/*$pathinfo = pathinfo($selectedTemplate);
					switch ($pathinfo['extension']) {
						case 'js':
							$output = str_replace('&lt;', '<', $output);
							$output = str_replace('&gt;', '>', $output);
							$output = str_replace('&amp;', '&', $output);
					}
					echo $output;*/
				} else {
					trigger_error("Template {$selectedTemplate} not found");
				}
			}
		} else {
			trigger_error("Page controller was already executed");
		}
		if ( (!$this->_redirectLocation) && (!requestIsAjax()) ) {
			unset($_SESSION['typef_redirect_message']);
		}
		session_write_close();
	}
	/**
	 * Set the page template
	 * @param string $template The relative path to the template in the source/templates directory or '.' for the template whose path matches the page's controller.
	 */
	public function setPageTemplate($template) {
		if ($template == '.') {
			$pathinfo = pathinfo($this->_controller);
			$this->_template = (substr($pathinfo['dirname'], strlen(TYPEF_SOURCE_DIR . '/controllers')) . '/' . $pathinfo['filename'] . '.html');
		} else {
			$this->_template = $template;
		}
	}
	/**
	 * Get the page template.
	 * @return string
	 */
	public function getPageTemplate() {
		return $this->_template;
	}
	private function _executeTriggers($when) {
		if ($this->stopped()) return;
		$currentPath = $this->path();
		foreach (Typeframe::Registry()->getTriggersWhen($when) as $trigger) {
			$triggerOnPath = false;
			if ($trigger->path() == $currentPath) {
				$triggerOnPath = true;
			} else if (substr($trigger->path(), 0, 1) == '*') {
				$triggerOnPath = true;
			} else {
				$asterisk = strpos($trigger->path(), '*');
				if ($asterisk !== false) {
					$path = substr($trigger->path(), 0, $asterisk);
					if ($currentPath == $path) {
						$triggerOnPath = true;
					} else {
						if (substr($currentPath, 0, strlen($path)) == $path) {
							if (substr($trigger->path(), $asterisk - 1, 1) == '/') {
								$triggerOnPath = true;
							} else {
								if (substr($currentPath, $asterisk, 1) == '/') {
									$triggerOnPath = true;
								}
							}
						}
					}
				}
			}
			if ($triggerOnPath) {
				Typeframe::IncludeScript($trigger->script());
			}
			if (Typeframe::CurrentPage()->stopped()) return;
		}
	}
	private function _executeApplicationCode() {
		static $executed = false;
		if (!$executed) {
			$executed = true;
			// Execute triggers
			$currentPath = $this->path();
			$this->_executeTriggers('before');
			if (Typeframe::CurrentPage()->stopped()) return;
			Typeframe_Page::_IncludeController(Typeframe::CurrentPage()->controller());
			if (Typeframe::CurrentPage()->stopped()) return;
			$this->_executeTriggers('after');
		} else {
			trigger_error('Applications can only be executed once');
		}
	}
}
