<?php

/**
 * A simple caching system, makes use of either memcache or
 * flat .ser files if memcache is unavailable.
 */
class Cache{
	private static $_Instance;

	private $_backend;

	private function __construct(){
		$this->_backend = false;

		if(class_exists('Memcache')){
			$this->_backend = new Memcache();
			if(!@$this->_backend->pconnect('localhost', 11211)){
				// Guess I can't use memcache anyways...
				$this->_backend = new _SerializedFileCache();
			}
		}
		else{
			$this->_backend = new _SerializedFileCache();
		}
	}

	public static function Singleton(){
		if(is_null(Cache::$_Instance)){
			Cache::$_Instance = new Cache();
		}

		return Cache::$_Instance;
	}

	private function _get($key){
		return $this->_backend->get($key);
	}

	public static function Get($key){
		return Cache::Singleton()->_get($key);
	}

	public function _set($key, $value, $ttl = 3600){
		return $this->_backend->set($key, $value, false, $ttl);
	}
	public static function Set($key, $value, $ttl = 3600){
		return Cache::Singleton()->_set($key, $value, $ttl);
	}
}


class _SerializedFileCache{
	private $_filecontents;

	/**
	 * Stores variable var with key only if such key doesn't exist at the server yet
	 *
	 * @param $key string The key that will be associated with the item.
	 * @param $var mixed The variable to store. Strings and integers are stored as is, other types are stored serialized.
	 * @param $flag boolean ** UNUSED **
	 * @param $expire int The number of seconds to expire.  If set to zero, the item will never expire.
	 * @return boolean Returns TRUE on success or FALSE on failure. Returns FALSE if such key already exist.
	 */
	public function add ($key, $var, $flag = false, $expire = 3600) {
		$cache = $this->_getFileContents();

		// Do not overwrite.
		if(is_array($this->_filecontents) && isset($cache[$key])) return false;

		return $this->set($key, $var, $flag, $expire);
	}

	/**
	 * Store data at the server
	 *
	 * @param $key string The key that will be associated with the item.
	 * @param $var mixed The variable to store. Strings and integers are stored as is, other types are stored serialized.
	 * @param $flag ** UNUSED **
	 * @param $expire Expiration time of the item. If it's equal to zero, the
	 *        item will never expire. You can also use Unix timestamp or a number
	 *        of seconds starting from current time, but in the latter case the
	 *        number of seconds may not exceed 2592000 (30 days).
	 * @return boolean Returns TRUE on success or FALSE on failure.
	 */
	public function set ($key, $var, $flag = false, $expire = 3600) {
		// Ensure the file has been loaded.
		$this->_getFileContents();

		// It needs to at least be an array if it's not already.
		if(!is_array($this->_filecontents)) $this->_filecontents = array();

		// Create/set the expire time.
		$e = ($expire == 0)? 0 : time() + $expire;

		// Access the array directly.
		$this->_filecontents[$key] = array(
			'value' => $var,
			'expires' => $e,
		);

		// Write the file back to the filesystem for persistence.
		$this->_save();
	}

	/**
	 * Replace value of the existing item
	 *
	 * @param $key string The key that will be associated with the item.
	 * @param $var mixed The variable to store. Strings and integers are stored as is, other types are stored serialized.
	 * @param $flag ** UNUSED **
	 * @param $expire Expiration time of the item. If it's equal to zero, the
	 *        item will never expire. You can also use Unix timestamp or a number
	 *        of seconds starting from current time, but in the latter case the
	 *        number of seconds may not exceed 2592000 (30 days).
	 * @return boolean Returns TRUE on success or FALSE on failure.
	 */
	public function replace ($key, $var, $flag = false, $expire = 3600) {
		$cache = $this->_getFileContents();

		// Only overwrite existing.
		if(!is_array($this->_filecontents)) return false;
		if(!isset($cache[$key])) return false;

		return $this->set($key, $var, $flag, $expire);
	}

	/**
	 * Retrieve item from the server
	 *
	 * @param $key mixed The key or array of keys to fetch.
	 * @param $flags ** UNUSED ** (kept for compatibility)
	 * @return mixed Returns the string associated with the key or FALSE on failure or if such key was not found.
	 */
	public function get ($key, $flags = false) {
		$cache = $this->_getFileContents();

		// Doesn't exist?
		if(!is_array($this->_filecontents)) return false;
		if(!isset($cache[$key])) return false;

		// Too old?
		if($cache[$key]['expires'] != 0 && $cache[$key]['expires'] < time()) return false;

		return $cache[$key]['value'];
	}

	/**
	 * Delete item from the server
	 *
	 * @param $key string The key associated with the item to delete.
	 * @param $timeout int Execution time of the item. If it's equal to zero,
	 *        the item will be deleted right away whereas if you set it to 30, the
	 *        item will be deleted in 30 seconds.
	 * @return boolean Returns TRUE on success or FALSE on failure.
	 */
	public function delete ($key, $timeout = 0) {
		$cache = $this->_getFileContents();

		// Doesn't exist?
		if(!is_array($this->_filecontents)) return true;
		if(!isset($cache[$key])) return true;

		unset($this->_filecontents[$key]);
		$this->_save();
	}

	public function getExtendedStats () {
		// Not supported in this API.
		return false;
	}

	public function getServerStatus(){
		// Not supported in this API.
		return 0;
	}

	/**
	 * Flush all existing items at the server
	 *
	 * @return boolean Returns TRUE on success or FALSE on failure.
	 */
	public function flush () {
		$this->_filecontents = false;
		$this->_save();
	}

	private function _getFileContents(){
		if(is_null($this->_filecontents)){
			$f = $this->_getFilename();
			if(file_exists($f)){
				$c = file_get_contents($f);
				$this->_filecontents = unserialize($c);

				// Unset any that are too old.
				$t = time();
				foreach($this->_filecontents as $k => $d){
					if($d['expires'] != 0 && $d['expires'] < $t) unset($this->_filecontents[$k]);
					// Note, don't save because that'll take extra resources to do so and hopefully it'll get saved externally somewhere.
				}
			}
			else{
				$this->_filecontents = false;
			}
		}

		return $this->_filecontents;
	}

	private function _save(){
		$f = $this->_getFilename();
		$c = $this->_getFileContents();
		if($c === false){
			file_put_contents($f, '');
		}
		else{
			file_put_contents($f, serialize($c));
		}
	}

	private function _getFilename(){
		return TYPEF_DIR . '/files/cache/systemcacher.ser';
	}
}