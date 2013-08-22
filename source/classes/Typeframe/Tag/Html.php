<?php
/**
 * The Typeframe/Pagemill html tag (e.g., pm:html) that enables skins.
 */
class Typeframe_Tag_Html extends Pagemill_Tag {
	public function __construct($name, array $attributes = array(), Pagemill_Tag $parent = null, Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
	}
	public function output(\Pagemill_Data $data, \Pagemill_Stream $stream) {
		$this->name = 'html';
		$pm = new Pagemill($data);
		$skin = $data->parseVariables($this->getAttribute('skin'));
		$oldskin = null;
		if ($skin) {
			$oldskin = Typeframe_Skin::Current();
			Typeframe_Skin::Set($skin);
		} else {
			$skin = Typeframe_Skin::Current();
		}
		if (file_exists(TYPEF_DIR . '/skins/' . $skin . '/skin.html')) {
			$skinTree = $pm->parseFile(TYPEF_DIR . '/skins/' . $skin . '/skin.html');
		} else {
			$skinTree = $pm->parseFile(TYPEF_DIR . '/skins/default/skin.html');
		}
		$skinTree->process($data, $stream);
		if (!is_null($oldskin)) {
			Typeframe_Skin::Set($oldskin);
		}
	}
}
