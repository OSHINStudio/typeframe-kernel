<?php
class Typeframe_Tag_Codeblock extends Pagemill_Tag {
	const ELEMENTS = 'a,abbr,acronym,b,big,blockquote,br,caption,cite,code,dd,div,dl,dt,em,hr,i,img,kbd,li,ol,p,q,small,span,strong,sub,sup,u,ul';
	const ATTRIBUTES = 'a.href,a.target,img.src,img.alt';
	private function _recurseInput(Pagemill_Node $node, Pagemill_Stream $stream, array $elements, array $attributes, Pagemill_Data $data) {
		if (is_a($node, 'Pagemill_Tag_Template')) {
			foreach ($node->children() as $c) {
				$this->_recurseInput($c, $stream, $elements, $attributes, $data);
			}
		} else {
			if (is_a($node, 'Pagemill_Tag')) {
				if ( (in_array('*', $elements)) || (in_array($node->name(), $elements)) ) {
					if (is_a($node, 'Pagemill_Tag_Comment')) {
						/*$stream->puts('<!--');
						foreach ($node->children() as $child) {
							$child->rawOutput($stream, false);
						}
						$stream->puts('-->');*/
					} else {
						$stream->puts('<' . $node->name());
						foreach ($node->attributes() as $k => $v) {
							if ( (in_array('*', $attributes)) || (in_array($node->name() . '.*', $attributes)) || (in_array($node->name() . ".{$k}", $attributes)) ) {
								$stream->puts(' ' . $k . '="' . htmlentities($v) . '"');
							}
						}
						if (count($node->children()) == 0) {
							if ($node->collapse) {
								$stream->puts('/>');
							} else {
								$stream->puts('></' . $node->name() . '>');
							}
						} else {
							$stream->puts('>');
							foreach ($node->children() as $c) {
								$this->_recurseInput($c, $stream, $elements, $attributes, $data);
							}
							$stream->puts('</' . $node->name() . '>');
						}
					}
				} else {
					// Tag is not in element whitelist
					// Recurse through its children and return content without unpermitted tags
					foreach ($node->children() as $c) {
						$this->_recurseInput($c, $stream, $elements, $attributes, $data);
					}
				}
			} else {
				$node->rawOutput($stream);
			}
		}
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		// Create the element and attribute arrays
		if ($this->hasAttribute('elements')) {
			$elements = $data->parseVariables($this->getAttribute('elements'));
		} else {
			$elements = self::ELEMENTS;
		}
		if ($this->hasAttribute('attributes')) {
			$attributes = $data->parseVariables($this->getAttribute('attributes'));
		} else {
			$attributes = self::ATTRIBUTES;
		}
		if ($this->hasAttribute('xelements')) {
			$elements .= ($elements ? ',' : '') . $this->getAttribute('xelements');
		}
		if ($this->hasAttribute('xattributes')) {
			$attributes .= ($attributes ? ',' : '') . $this->getAttribute('xattributes');
		}
		$elementArray = array_unique(preg_split('/ *, */', $elements));
		$attributeArray = array_unique(preg_split('/ *, */', $attributes));
		if (in_array('*', $elementArray)) {
			$elementArray = array('*');
		}
		if ( (in_array('*', $attributeArray)) || (in_array('*.*', $attributeArray)) ) {
			$attributeArray = array('*');
		}
		$buffer = new Pagemill_Stream(true);
		foreach	($this->children() as $child) {
			$child->rawOutput($buffer, false);
		}
		$source = $buffer->peek();
		$source = $data->parseVariables($source);
		if (!trim($source)) return;
		if ($this->getAttribute('use') == 'markdown') {
			require_once(TYPEF_SOURCE_DIR . '/libraries/markdown.php');
			$source = Markdown($source);
		}
		try {
			$parser = new Pagemill_Parser();
			$doctype = new Pagemill_Doctype_Html('');
			// Register the href and src attribute processors for URL shortcuts
			$doctype->registerAttribute('/href', 'Typeframe_Attribute_Url');
			$doctype->registerAttribute('/src', 'Typeframe_Attribute_Url');
			$tree = $parser->parse($source, $doctype);
		} catch (Exception $e) {
			trigger_error($e->getMessage());
			$stream->puts(htmlentities($source));
			return;
		}
		$this->_recurseInput($tree, $stream, $elementArray, $attributeArray, $data);
	}
}
