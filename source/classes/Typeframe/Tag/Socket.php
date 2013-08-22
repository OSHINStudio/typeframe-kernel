<?php
class Typeframe_Tag_Socket extends Pagemill_Tag_Inner {
	private static function _GetPluginsFor($for) {
		$locs = new Model_PlugLoc();
		$locs->where('skin = ?', Typeframe_Skin::Current());
		$locs->where('socket = ?', $for);
		$locs->order('sortnum');
		$result = array();
		foreach($locs->select() as $loc) {
			$show = true;
			if ($loc['rules']) $show = self::ProcessRules($loc['rules']);
			if ($show) {
				$plug = $loc['plugin'];
				$result[] = $plug;
			}
		}
		return $result;
	}
	public static function CountPluginsFor($for) {
		return count(self::_getPluginsFor($for));
	}
	private function _findEmptyPluginTags(Pagemill_Tag $parent, $found = array()) {
		foreach ($parent->children() as $child) {
			if (is_a($child, 'Pagemill_Tag_Plugin')) {
				if (!count($child->attributes())) {
					$found[] = $child;
				}
				$found = $this->_findPlugin($child, $found);
			}
		}
		return $found;
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$for = $data->parseVariables($this->getAttribute('for'));
		if (!$for) {
			throw new Exception("Socket tag requires 'for' attribute");
		}
		$plugin = null;
		$detachable = true;
		if (count($this->children())) {
			$detachable = false;
			$found = $this->_findEmptyPluginTags();
			if (count($found) == 0) {
				throw new Exception('Socket tag with content requires an empty plugin (no attributes)');
			} else if (count($found) > 1) {
				throw new Exception('Socket tag should not contain more than one empty plugin');
			}
			$plugin = $found[0];
		} else {
			$plugin = new Typeframe_Tag_Plugin('plugin', array(), $this);
			$this->appendChild($plugin);
		}
		$plugins = self::_GetPluginsFor($for);
		foreach ($plugins as $p) {
			$signature = Typeframe::Registry()->getPluginSignature($p['plug']);
			$plugin->className = $signature->className();
			$plugin->settings = $p['settings'];
			$plugin->attributes['plugid'] = $p['plugid'];
			parent::output($data, $stream);
		}
		if ($detachable) {
			$plugin->detach();
		}
	}
	public static function ProcessRules($string) {
		$show = false;
		$rules = explode(';', $string);
		$countrules = count($rules);
		// Now I can do the check to see if there are no rules.
		if ($countrules <= 0){
			$show = true;
		}
		foreach ($rules as $r) {
			list($type, $code) = explode(':', $r);
			$not = false;
			if (substr($type, 0, 1) == '!') {
				$type = substr($type, 1);
				$not = true;
			}
			switch($type) {
				case 'pid':
					if (Typeframe::CurrentPage()->page()->pageid() == $code) {
						if ($not) {
							$show = false;
							return false;
						} else {
							$show = true;
						}
					}
					break;
				case 'url':
					if ($code == '*') {
						if ($not) {
							$show = false;
							return false;
						} else {
							$show = true;
							continue;
						}
					}
					if (substr($code, 0, 2) == '//') {
						$code = TYPEF_WEB_DIR . substr($code, 1);
					}
					if ( ($pos = strpos($code, '*')) !== false) {
						if (substr(Typeframe::CurrentPage()->uri(), 0, $pos) == substr($code, 0, $pos)) {
							if ($not) {
								$show = false;
								return false;
							} else {
								$show = true;
							}
						}
					}
					if (Typeframe::CurrentPage()->uri() == $code) {
						if ($not) {
							$show = false;
							return false;
						} else {
							$show = true;
						}
					}
					break;

			} // switch($type)
		} // foreach ($rules as $r)
		return $show;
	}
}
