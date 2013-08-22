<?php

class Typeframe_Attribute_Url extends Pagemill_Attribute {
	public function __construct($name, $value, \Pagemill_Tag $tag) {
		parent::__construct($name, $value, $tag);
		$tag->setAttribute($name, self::ConvertShortUrlToExpression($value));
	}
	private static function _EscapeString($string) {
		return "'" . str_replace('@{', "' . (", str_replace('}@', ") . '", $string)) . "'";
	}
	public static function ConvertShortUrlToExpression($value) {
		if (substr($value, 0, 2) == '~/') {
			$value = TYPEF_WEB_DIR . substr($value, 1);
		} elseif (substr($value, 0, 3) == '~s/') {
			$value = '@{skin_path(' . self::_EscapeString(substr($value, 2)) . ')}@';
		} elseif (substr($value, 0, 3) == '~a/') {
			$value = '@{typef_app_dir . ' . self::_EscapeString(substr($value, 2)) . '}@';
		} elseif (substr($value, 0, 3) == '~f/') {
			$value = '@{typef_web_dir . ' . "'/files' . " . self::_EscapeString(substr($value, 2)) . '}@';
		} elseif (substr($value, 0, 4) == '~fs/') {
			$value = '@{typef_web_dir . ' . "'/files/static' . " . self::_EscapeString(substr($value, 3)) . '}@';
		} elseif (substr($value, 0, 4) == '~fp/') {
			$value = '@{typef_web_dir . ' . "'/files/public' . " . self::_EscapeString(substr($value, 3)) . '}@';
		}
		return $value;
	}
}
