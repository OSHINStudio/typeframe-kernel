<?php
/**
 * Abstract interface for plugins.
 * Typeframe Plugins are Pagemill Tags with additional functionality for
 * managing configurations through administrative control panels.
 */
abstract class PluginAbstract extends Pagemill_Tag {
	protected $pluginTemplate = null;
	protected $adminTemplate = null;
	abstract public function admin(Pagemill_Data $data, Pagemill_Stream $stream);
	abstract public function update(array $input);
}
