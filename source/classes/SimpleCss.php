<?php
/**
 * A simple CSS parser.
 */
class SimpleCss {
	/**
	 * Load a CSS document from a file.
	 * @param string $file The filename.
	 * @param string $basedir The base directory for relative paths (e.g., in url attributes).
	 * @return SimpleCss_Document
	 */
	public static function LoadFile($file, $basedir = null) {
		return self::_Parse(file_get_contents($file), $basedir);
	}
	/**
	 * Load a CSS document from a string.
	 * @param string $css The CSS code.
	 * @param string $basedir The base directory for relative paths (e.g., in url attributes).
	 * @return SimpleCss_Document
	 */
	public static function LoadString($css, $basedir = null) {
		return self::_Parse($css, $basedir);
	}
	private static function _Pathify($path) {
		$parts = explode('/', $path);
		$result = array();
		foreach ($parts as $part) {
			if ($part == '..') {
				array_pop($result);
			} else if ( ($part == '.') || ($part == '') ) {
				// Nothing
			} else {
				$result[] = $part;
			}
		}
		return '/' . implode('/', $result);
	}
	private static function _Parse($css, $basedir = null) {
		// Strip comments
		$css = preg_replace('/\/\*[\w\W\s\S]*?\*\//', '', $css);
		$inRules = false;
		$inAtRule = false;
		$inComment = false;
		$inUrl = false;
		$selectors = '';
		$rules = '';
		$url = '';
		$document = new SimpleCss_Document();
		for ($i = 0; $i < strlen($css); $i++) {
			$char = substr($css, $i, 1);
			if ($inComment) {
				if ( ($char == '*') && (substr($css, $i + 1, 1) == '/') ) {
					$inComment = false;
					$i++;
				}
			} else {
				if ( ($char == '/') && (substr($css, $i + 1, 1) == '*') ) {
					$inComment = true;
					$i++;
				} else {
					if (!$inAtRule) {
						if ($char == '@') {
							$inAtRule = true;
							$selectors .= $char;
						} else {
							if (!$inRules) {
								if ($char == '{') {
									$inRules = true;
								} else {
									$selectors .= $char;
								}
							} else {
								if (!$inUrl) {
									if ($char == '(') {
										if (substr(trim($rules), strlen(trim($rules)) - 3) == strtolower('url')) {
											$inUrl = true;
											continue;
										}
									}
									if ($char == '}') {
										$inRules = false;
										$document->addRuleset(new SimpleCss_Ruleset($selectors, $rules));
										$selectors = '';
										$rules = '';
									} else {
										$rules .= $char;
									}
								} else {
									if ($char == ')') {
										$inUrl = false;
										if (substr($url, 0, 1) == "'" && substr($url, -1) == "'") {
											$url = substr($url, 1, -1);
										}
										if ($basedir) {
											if (substr($url, 0, 1) != '/') {
												$url = self::_Pathify($basedir . '/' . $url);
											}
										}
										$rules .= '(' . $url . ')';
										$url = '';
									} else {
										$url .= $char;
									}
								}
							}
						}
					} else {
						// In an at rule
						if ($char == '{') {
								$inAtRule = false;
								$inRules = true;
						} else if ($char == ';') {
								$inAtRule = false;
								$inRules = false;
								$document->addRuleset(new SimpleCss_Ruleset($selectors . ';', ''));
								$selectors = '';
								$rules = '';
						} else {
							$selectors .= $char;
						}
					}
				}
			}
		}
		return $document;
	}
}
