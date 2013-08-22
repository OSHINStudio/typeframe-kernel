<?php
class Typeframe_Tag_Include extends Pagemill_Tag_Include {
	public function output(\Pagemill_Data $data, \Pagemill_Stream $stream) {
		if ($this->hasAttribute('template')) {
			$template = Typeframe_Skin::TemplatePath($data->parseVariables($this->getAttribute('template')));
			$this->setAttribute('file', $template);
		}
		if ($this->getAttribute('select')) {
			$select = $this->getAttribute('select');
			$temp = new Pagemill_Stream(true);
			parent::output($data, $temp);
			$xml = Pagemill_SimpleXmlElement::LoadString($temp->clean());
			$parts = $xml->select($select);
			foreach ($parts as $part) {
				$stream->puts($part->asXml());
			}
		} else {
			parent::output($data, $stream);
		}
	}
	/**
	 * A shortcut for instantiating Typeframe_Tag_Include with a template attribute.
	 * @param string $template The template attribute.
	 * @param Pagemill_Tag $parent The parent to apply.
	 * @return \Typeframe_Tag_Include
	 */
	public static function Generate($template, Pagemill_Tag $parent = null) {
		$tag = new Typeframe_Tag_Include('include', array('template' => $template), $parent);
		return $tag;
	}
}
