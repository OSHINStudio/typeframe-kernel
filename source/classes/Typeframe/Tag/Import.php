<?php
class Typeframe_Tag_Import extends Pagemill_Tag {
	private $_imported = array();
	private function _getImported($name) {
		if (!isset($this->_imported[$name])) {
			$this->_imported[$name] = Typeframe_TagPreprocessor_Export::Pop($name);
			//if (!$this->_imported[$name]) {
			//	throw new Exception("Exported tag '{$name}' not found");			
			//}
		}
		return $this->_imported[$name];
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$name = $this->getAttribute('name');
		//$import = Typeframe_TagPreprocessor_Export::Export($name);
		$import = $this->_getImported($name);
		if ($import) {
			//foreach ($imports as $import) {
				if ($import->parent() !== $this) {
					$this->appendChild($import);
				}
				//foreach ($import->children() as $child) {
				//	$child->process($data, $stream);
				//}
				$import->process($data, $stream);
			//}
		} else {
			throw new Exception("Exported tag '{$name}' not found for output");
		}
	}
	public function children() {
		$name = $this->getAttribute('name');
		//$import = Typeframe_TagPreprocessor_Export::Export($name);
		$import = $this->_getImported($name);
		$children = array();
		if ($import) {
			/*foreach ($imports as $import) {
				$children = array_merge($children, $import->children());
			}*/
			return $import->children();
		} //else {
		//	throw new Exception("Exported tag '{$name}' not found");			
		//}
		//return $children;
	}
}
