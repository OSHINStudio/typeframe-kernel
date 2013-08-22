<?php
class Typeframe {
	private static $_sockets = null;
	private static $_timestamps = array();
	private static $_skin = null;
	private static $_templatePath = null;
	private static $_registry = null;
	private static $_registryLoaded = false;
	/**
	 * Log an action with a timestamp.
	 * @param string $action
	 */
	public static function Timestamp($action) {
		if (count(Typeframe::$_timestamps) == 0) {
			if (defined('TYPEF_START_TIME')) {
				array_push(Typeframe::$_timestamps, new Typeframe_Timestamp('Typeframe started', TYPEF_START_TIME));
			}
		}
		array_push(Typeframe::$_timestamps, new Typeframe_Timestamp($action));
	}
	/**
	 * Get an array of timestamped log entries.
	 * @return array An array of timestamped log entries.
	 */
	public static function GetTimestamps() {
		if (count(Typeframe::$_timestamps) == 0) {
			if (defined('TYPEF_START_TIME')) {
				array_push(Typeframe::$_timestamps, new Typeframe_Timestamp('Typeframe started', TYPEF_START_TIME));
			}
		}
		return Typeframe::$_timestamps;
	}
	/**
	 * Get a reference to the global database interface.
	 * @return Dbi_Source
	 */
	public static function Database() {
		return Dbi_Source::GetGlobalSource();
	}
	/**
	 * Get the global Pagemill object
	 * @return Pagemill
	 */
	public static function Pagemill() {
		return self::CurrentPage()->pagemill();
	}
	/**
	 * Get a reference to the current user.
	 * @return Typeframe_User
	 */
	public static function User() {
		static $user = null;
		if (is_null($user)) {
			$user = new Typeframe_User();
		}
		return $user;
	}
	/**
	 * Get the Typeframe Registry object.
	 * @return Typeframe_Registry
	 */
	public static function Registry() {
		if (is_null(self::$_registry)) {
			self::$_registry = new Typeframe_Registry(!isset($_SERVER['SHELL']));
		}
		self::$_registryLoaded = true;
		return self::$_registry;
	}
	/**
	 * @deprecated
	 * @return boolean
	 */
	public static function RegistryLoaded() {
		return Typeframe::$_registryLoaded;
	}
	/**
	 * Get a reference to the current page (response).
	 * @return Typeframe_Response
	 */
	public static function CurrentPage() {
		return Typeframe_Response::Current();
	}
	/**
	 * Redirect to a new page.
	 * @param string $message The redirect message.
	 * @param string $url The location of the redirection.
	 * @param int $sec The number of seconds to display the redirect page.
	 * @param bool $addHeader Whether to add a Location header to the response.
	 * @param int $responseCode The HTTP response code.
	 * @param string $postOverride If the request was a POST, the name of the POST variable that should override the $url value.
	 */
	public static function Redirect($message, $url, $sec = TYPEF_DEFAULT_REDIRECT_TIME, $addHeader = false, $responseCode = 0, $postOverride = 'post_redir') {
		// Just pass arguments to current page
		Typeframe::CurrentPage()->redirect($message, $url, $sec, $addHeader, $responseCode);
	}
	/**
	 * Get the current page template.
	 * @return string The relative path to the template.
	 */
	public static function GetPageTemplate() {
		return Typeframe::CurrentPage()->getPageTemplate();
	}
	/**
	 * Set the current page template.  (A helper function for Typeframe::CurrentPage()->setPageTemplate().)
	 * @param string $tmpl The relative path to the template.
	 */
	public static function SetPageTemplate($tmpl) {
		Typeframe::CurrentPage()->setPageTemplate($tmpl);
	}
	public static function GetSkins() {
		static $skins = null;
		if (is_null($skins)) {
			$skins = array();
			$h = opendir(TYPEF_DIR . '/skins');
			while (false !== ($dir = readdir($h))) {
				//if ( ($dir != ".") && ($dir != "..") ) {
				if (substr($dir, 0, 1) != ".") {
					if (is_dir(TYPEF_DIR . "/skins/{$dir}")) {
						array_push($skins, $dir);
					}
				}
			}
			sort($skins);
		}
		return $skins;
	}
	/**
	 * Return true if the current user is allowed to access the specified URI.
	 * @param string $uri The complete path to the page (e.g., "/foo/bar").
	 * @return bool
	 */
	public static function Allow($uri) {
		if (substr($uri, 0, 2) == '~/') {
			$uri = TYPEF_WEB_DIR . substr($uri, 1);
		}
		$response = Typeframe::Registry()->responseAt($uri);
		if ($response) {
			return ($response->page()->allow());
		}
		trigger_error("{$uri} is not part of a registered application.");
		return null;
	}
	/**
	 * Get a MySQL-friendly representation of the current date and time.
	 * @return string The date and time.
	 */
	public static function Now() {
		return date('Y-m-d H:i:s');
	}
	/**
	 * Get a MySQL-friendly representation of the current date.
	 * @return string The date.
	 */
	public static function Today() {
		return date('Y-m-d');
	}
	/**
	 * Log a user action to the database.
	 * @param string $action.
	 * @param string $fulldesc The full description of the event [optional].
	 */
	public static function Log($action, $fulldesc = false) {
		if(!$fulldesc) $fulldesc = $action;
		// Also addin the source URL, as that may change and reveal important information.
		if (isset($_SERVER['HTTP_HOST'])) {
			$fulldesc .= "\n" . 'Source URL: ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			if(isset($_SERVER['HTTP_REFERER'])) $fulldesc .= "\n" . 'Referrer URL: ' . $_SERVER['HTTP_REFERER'];
		} else {
			$fulldesc .= "\nN/a";
		}
		$log = Model_Log::Create();
		$log['userid'] = Typeframe::User()->get('userid');
		$log['ipaddress'] = @$_SERVER['REMOTED_ADDR'];
		$log['package'] = (Typeframe::CurrentPage() ? Typeframe::CurrentPage()->application()->package() : '');
		$log['application'] = (Typeframe::CurrentPage() ? Typeframe::CurrentPage()->application()->name() : '');
		$log['action'] = $action;
		$log['logdate'] = Typeframe::Now();
		$log['fulldesc'] = $fulldesc;
		$log->save();
	}
	/**
	 * Include (evaluate) a file in the source/scripts directory.
	 * @param string $script The relative path to the file.
	 */
	public static function IncludeScript($script) {
		// TODO: Get rid of these variables.  They should be declared explicitly in the controller if necessary.
 		$pm = Typeframe::Pagemill();
		$db = Typeframe::Database();
		include_once(TYPEF_SOURCE_DIR . '/scripts' . $script);
	}
	public static function SetTemplatePath($path) {
		Typeframe::$_templatePath = $path;
	}
	public static function GetTemplatePath() {
		return Typeframe::$_templatePath;
	}
	public static function FindTemplate($template) {
		return Typeframe_Skin::TemplatePath($template);
	}
	/**
	 * Request a URL by proxy.
	 */
	public static function GetByProxy($url) {
		$url = 'http://' . (TYPEF_HOST ? TYPEF_HOST : 'localhost') . $url;
		$cookie = '';
		if (Typeframe::User()->get('username') && Typeframe::User()->get('passhash')) {
			$cookie = 'typef_username=' . Typeframe::User()->get('username') . '; typef_passhash=' . Typeframe::User()->get('passhash');
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_COOKIE, $cookie);
		$buffer = curl_exec($curl);
		$response = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		if ($response == 200) {
			return $buffer;
		} else {
			return '';
		}
	}
}
