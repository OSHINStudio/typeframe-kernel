<?php

class Typeframe_TagPreprocessor_Method extends Pagemill_TagPreprocessor {
	private $_method;
	public function __construct($method) {
		$this->_method = $method;
	}
	public function process(\Pagemill_Tag $tag, \Pagemill_Data $data, \Pagemill_Stream $stream) {
		//if ($this->_method == 'post') {
			// TODO: Add the postlink stuff
			$tag->setAttribute('rel', $tag->getAttribute('rel') ? $tag->getAttribute('rel') . ' ' . $this->_method : $this->_method);
		//}
	}
}
