<?php
class Typeframe_Page {
	private $_application;
	private $_uri;
	private $_settings;
	private $_extenders;
	private $_handler = null;
	public function __construct(Typeframe_Application $application, $uri, $settings = array()) {
		$this->_application = $application;
		$this->_uri = $uri;
		if (substr($this->_uri, -1) == '/') {
			$this->_uri = substr($this->_uri, 0, -1);
		}
		$this->_settings = $settings;
		$this->_extenders = array();
		foreach ($this->_application->extenders() as $extender) {
			$path = '';
			$redirect = '';
			if ($extender->path()) {
				$add = $extender->path();
				if (substr($add, 0, strlen($application->base())) == $application->base()) {
					$add = substr($add, strlen($application->base()));
				}
				$path = $this->_uri . ($add ? '/' . $add : '');
			}
			/*if ($extender->redirect()) {
				$add = $extender->redirect();
				if (substr($add, 0, strlen($application->base())) == $application->base()) {
					$add = substr($add, strlen($application->base()));
				}
				$redirect = $this->_uri . ($add ? '/' . $add : '');
			}*/
			$path = preg_replace('/\/+/', '/', $path);
			$redirect = preg_replace('/\/+/', '/', $redirect);
			$this->_extenders[] = new Typeframe_Extender($path, $extender->preg(), $extender->redirect());
		}
	}
	public function application() {
		return $this->_application;
	}
	public function uri() {
		return $this->_uri;
	}
	public function settings() {
		return $this->_settings;
	}
	public function extenders() {
		return $this->_extenders;
	}
	public function pageid() {
		return (isset($this->_settings['pageid']) ? $this->_settings['pageid'] : null);
	}
	public function siteid() {
		return (isset($this->_settings['siteid']) ? $this->_settings['siteid'] : null);
	}
	public function allow(Dbi_Record $user = null) {
		if ($this->application()->name() == '403') return false;
		return $this->handler()->allow();
	}
	public function handler() {
		if (is_null($this->_handler)) {
			$cls = $this->_application->handlerName();
			if (!is_a($cls, 'Typeframe_Application_Handler', true)) {
				var_dump($this);
				throw new Exception("'{$cls}' is not a Typeframe_Application_Handler class");
			}
			$this->_handler = new $cls($this);
		}
		return $this->_handler;
	}
	public function title() {
		if (!empty($this->_settings['nickname'])) {
			return $this->_settings['nickname'];
		}
		return $this->application()->title();
	}
	public function icon() {
		return $this->application()->icon();
	}
}
