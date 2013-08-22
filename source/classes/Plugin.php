<?php

class Plugin extends PluginAbstract {
	public function admin(Pagemill_Data $data, Pagemill_Stream $stream) {
		$include = null;
		if (!$this->children()) {
			if ($this->adminTemplate) {
				$include = new Typeframe_Tag_Include('include', array('template' => $this->adminTemplate), $this);
			}
		}
		$this->processInner($data, $stream);
		if ($include) {
			$include->detach();
		}
	}
	/**
	 * Update the plugin settings.
	 * @param array $input An associative array of settings.
	 */
	public function update(array $input) {
		$plugin = Model_Plug::Get($input['plugid']);
		if ($plugin->exists()) {
			$plugin['name'] = (isset($input['name']) ? $input['name'] : ((isset($input['settings']) && isset($input['settings']['name'])) ? $input['settings']['name'] : ''));
			$plugin['settings'] = $input['settings'];
			$plugin->save();
		} else {
			throw new Exception("Plugin not found");
		}		
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$include = null;
		if (!$this->children()) {
			if ($this->pluginTemplate) {
				$include = new Typeframe_Tag_Include('include', array('template' => $this->pluginTemplate), $this);
			}
		}
		$this->processInner($data, $stream);
		if ($include) {
			$include->detach();
		}
	}
}

/*class Plugin implements PluginInterface {
	protected $settings;
	protected $pluginTemplate = '';
	protected $adminTemplate = '';
	public function __construct($settings) {
		$this->settings = $settings;
		$this->adminTemplate = '/admin/plugins/no-settings.inc.html';
	}
	public function admin(Pagemill_Data $data, Pagemill_Stream $stream, Pagemill_Tag $tag) {
		$data['settings'] = $this->settings;
		$include = null;
		if (!$tag->children()) {
			if ($this->adminTemplate) {
				$include = new Typeframe_Tag_Include('include', array('template' => $this->adminTemplate), $tag);
			}
		}
		$tag->process($data, $stream);
		if ($include) {
			$include->detach();
		}
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream, Pagemill_Tag $tag) {
		$include = null;
		if (!$tag->children()) {
			if ($this->pluginTemplate) {
				$include = new Typeframe_Tag_Include('include', array('template' => $this->pluginTemplate), $tag);
			}
		}
		foreach ($tag->children() as $child) {
			$child->process($data, $stream);
		}
		if ($include) {
			$include->detach();
		}
	}
	public function update(array $input = null) {
		if (is_null($input)) {
			if (!isset($_POST)) {
				throw new Exception('Plugin update requires input (default POST array not found)');
			}
			$input = $_POST;
		}
		$plugin = Model_Plug::Get($input['plugid']);
		if ($plugin->exists()) {
			$plugin['name'] = (isset($input['name']) ? $input['name'] : ((isset($input['settings']) && isset($input['settings']['name'])) ? $input['settings']['name'] : ''));
			$plugin['settings'] = $input['settings'];
			$plugin->save();
		} else {
			throw new Exception("Plugin not found");
		}
	}
}*/
