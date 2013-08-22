<?php
/**
 * Tag for displaying an admin control panel for the specified plugin (plugid="#").
 */
class Typeframe_Tag_PluginAdmin extends Pagemill_Tag {
	public function output(\Pagemill_Data $data, \Pagemill_Stream $stream) {
		$plugid = $data->parseVariables($this->getAttribute('plugid'));
		$plug = Model_Plug::Get($plugid);
		if ($plug->exists()) {
			$name = $plug['plug'];
			$sig = Typeframe::Registry()->getPluginSignature($name);
			$cls = $sig->className();
			if (is_subclass_of($cls, 'Plugin')) {
				$plug['settings']['plugid'] = $plugid;
				$plug = new $cls('', $plug['settings'], $this);
				$plug->admin($data, $stream);
			} else {
				throw new Exception("Invalid plugin type specified");
			}
		} else {
			throw new Exception("Invalid plugin specified");
		}
	}
}
