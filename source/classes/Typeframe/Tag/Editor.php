<?php
/*
	2-5-2009	Fixed $inner processing (no html encoding)
*/

class Typeframe_Tag_Editor extends Pagemill_Tag {
	private static $_id = 0;
	protected static function EditorId() {
		return 'typef_editor_' . self::$_id++;
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		//$inner = $data->parseVariables($this->rawOutput(), false);
		$tmp = new Pagemill_Stream(true);
		foreach ($this->children() as $child) {
			$child->process($data, $tmp);
		}
		$inner = html_entity_decode($tmp->peek(), ENT_COMPAT, 'UTF-8');
		$data->set('value', $inner);
		$use = $data->parseVariables($this->getAttribute('use'));
		if ($use) {
			// Use the requested editor if available
			if (class_exists($use)) {
				if (is_subclass_of($use, __CLASS__)) {
					$sub = new $use($this->name(), $this->attributes(), $this, $this->docType());
					$sub->output($data, $stream);
					$sub->detach();
					return;
				} else {
					trigger_error("Requested editor class '{$cls}' does not appear to be an editor subclass.");
				}
			} else {
				trigger_error("Requested editor class '{$cls}' does not exist.");
			}
		}
		if (TYPEF_DEFAULT_EDITOR != '') {
			// Use the requested editor if available
			$use = TYPEF_DEFAULT_EDITOR;
			if (class_exists($use)) {
				if (is_subclass_of($use, __CLASS__)) {
					$sub = new $use($this->name(), $this->attributes(), $this, $this->docType());
					$sub->output($data, $stream);
					$sub->detach();
					return;
				} else {
					trigger_error("Configured editor class '{$use}' does not appear to be an editor subclass.");
				}
			} else {
				trigger_error("Configured editor class '{$use}' does not exist.");
			}
		}
		// Use CKEditor if available
		if (class_exists('Typeframe_Tag_Editor_CkEditor')) {
			$sub = new Typeframe_Tag_Editor_CkEditor($this->name(), $this->attributes(), $this, $this->docType());
			$sub->process($data, $stream);
			$sub->detach();
			return;
		}
		// No editor available. Use a plain textarea.
		$attribs = '';
		foreach ($this->attributes() as $k => $v) {
			if ($k != 'use') {
				$attribs .= " {$k}=\"" . $data->parseVariables($v) . "\"";
			}
		}
		if (!$this->getAttribute('cols')) {
			$attribs .= ' cols="80"';
		}
		if (!$this->getAttribute('rows')) {
			$attribs .= ' rows="25"';
		}
		$stream->puts("<textarea{$attribs}>" . $inner . "</textarea>");
	}
}
