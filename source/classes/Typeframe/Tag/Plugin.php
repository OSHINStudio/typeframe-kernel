<?php
class Typeframe_Tag_Plugin extends Pagemill_Tag {
	public $className;
	public $settings;
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$db = Typeframe::Database();
		if ( ($this->hasAttribute('rules')) && (!Typeframe_Tag_Socket::ProcessRules($this->getAttribute('rules'))) ) {
			return '';
		}
		/*
		 * Rules for loading the plugin:
		 * 1. The plugid overrides other load settings.
		 * 2. Load a plugin from the table if the name attribute matches an
		 *    an admin-specified name.
		 * 3. Create a generic plugin from a signature.
		 * 4. If the plugin was loaded from the database, attribute settings
		 *    override database settings.
		 */
		$p = null;
		if ($this->getAttribute('plugid')) {
			$plugin = Model_Plug::Get($data->parseVariables($this->getAttribute('plugid')));
			if ($plugin->exists()) {
				$p = Typeframe::Registry()->getPluginSignature($plugin['plug']);
			}
		} else {
			if ($this->getAttribute('name')) {
				$plugins = new Model_Plug();
				$plugins->where('name = ?', $data->parseVariables($this->getAttribute('name')));
				$plugin = $plugins->getFirst();
				if ($plugin->exists()) {
					$p = Typeframe::Registry()->getPluginSignature($plugin['plug']);
				} else {
					$p = Typeframe::Registry()->getPluginSignature($this->getAttribute('name'));
				}
			}
		}
		if ($p) {
			$cls = $p->className();
			if (class_exists($cls)) {
				$settings = $this->settings;
				foreach ($this->attributes() as $k => $v) {
					$settings[$k] = $v; //$data->parseVariables($v);
				}
				//$obj = new $cls($settings);
				$obj = new $cls('plugin', $settings, null);
				foreach ($this->children() as $child) {
					$obj->appendChild($child);
				}
				$obj->process($data, $stream);
				foreach ($obj->children() as $child) {
					$this->appendChild($child);
				}
				$obj->detach();
			} else {
				throw new Exception("Class '{$cls}' does not exist");
			}
		} else {
			throw new Exception("Plugin does not have a signature");
		}
	}
}
