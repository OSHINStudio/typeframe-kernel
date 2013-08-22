<?php

class Typeframe_Skin {
	private static $_skin = null;
	public static function Set($name) {
		self::$_skin = $name;
	}
	private static function _IsMobile() {
		static $detect = null;
		if (is_null($detect)) {
			$detect = new Mobile_Detect();
		}
		return $detect->isMobile() && !$detect->isTablet();
	}
	public static function Current() {
		if (is_null(self::$_skin)) {
			if (Typeframe::CurrentPage()) {
				return self::_AtResponse(Typeframe::CurrentPage());
			} else {
				if (TYPEF_USE_MOBILE_SKINS && self::_IsMobile()) {
					self::$_skin = TYPEF_MOBILE_SITE_SKIN;
				} else if (TYPEF_SITE_SKIN) {
					self::$_skin = TYPEF_SITE_SKIN;
				} else {
					self::$_skin = 'default';
				}
			}
		}
		return self::$_skin;
	}
	private static function _AtResponse(Typeframe_Response $response) {
		$settings = $response->page()->settings();
		if (!empty($settings['skin'])) {
			return $settings['skin'];
		}
		$uri = $response->applicationUri();
		$siteid = $response->page()->siteid();
		$sitesubdir = '';
		if ($siteid != 0) {
			$site = Model_Site::Get($siteid);
			$sitesubdir = "/{$site['directory']}";
		}
		if ( ($uri == TYPEF_WEB_DIR . $sitesubdir . '/admin') || (substr($uri, 0, strlen(TYPEF_WEB_DIR . $sitesubdir . '/admin/')) == TYPEF_WEB_DIR . $sitesubdir . '/admin/') ) {
			if (TYPEF_USE_MOBILE_SKINS && self::_IsMobile()) {
				return TYPEF_MOBILE_ADMIN_SKIN;
			}
			if (TYPEF_ADMIN_SKIN) {
				return TYPEF_ADMIN_SKIN;
			}
		}
		if (TYPEF_USE_MOBILE_SKINS && self::_IsMobile()) {
			return TYPEF_MOBILE_SITE_SKIN;
		}
		return (TYPEF_SITE_SKIN ? TYPEF_SITE_SKIN : 'default');
	}
	public static function At($uri) {
		$response = Typeframe::Registry()->responseAt($uri);
		if ($response) {
			return self::_AtResponse($response);
		}
		return (TYPEF_SITE_SKIN ? TYPEF_SITE_SKIN : 'default');
	}
	public static function BodySelectorFor($skin = null) {
		if (is_null($skin)) {
			$skin = self::Current();
		}
		if (file_exists(TYPEF_DIR . "/skins/{$skin}/skin.html")) {
			$skinfile = TYPEF_DIR . "/skins/{$skin}/skin.html";
		} else {
			$skinfile = TYPEF_DIR . "/skins/default/skin.html";
		}
		$source = file_get_contents($skinfile);
		$xml = Pagemill_SimpleXmlElement::LoadString($source);
		$body = $xml->xpath('//pm:import[@name="pm:body"]');
		$parent = $body[0]->xpath('parent::*');
		$selector = '';
		while (isset($parent[0])) {
			if ($parent[0]->getName() == 'body') break;
			$ns = $parent[0]->getNamespaces();
			if (key($ns) == '') {
				$here = $parent[0]->getName();
				if ($parent[0]['id']) {
					$here .= '#' . $parent[0]['id'];
				}
				if ($parent[0]['class']) {
					$here .= '.' . str_replace(' ', '.', $parent[0]['class']);
				}
				$selector = $here . ' ' . $selector;
			}
			$parent = $parent[0]->xpath('parent::*');
		}
		$selector = trim($selector);
		return $selector;
	}
	public static function TemplatePath($relativePath, $targetUri = null) {
		$relativePath = (string)$relativePath;
		if (substr($relativePath, 0, 1) != '/') $relativePath = '/' . $relativePath;
		$relativePath = '/templates' . $relativePath;
		$result = self::SkinPath($relativePath, $targetUri);
		if ($result === false && file_exists(TYPEF_SOURCE_DIR . $relativePath)) {
			$result = TYPEF_SOURCE_DIR . $relativePath;
		} else if ($result !== false) {
			$result = TYPEF_DIR . substr($result, strlen(TYPEF_WEB_DIR));
		} else {
			$result = TYPEF_SOURCE_DIR . $relativePath;
		}
		return $result;
	}
	public static function SkinPath($relativePath, $targetUri = null) {
		if (substr($relativePath, 0, 1) != '/') $relativePath = '/' . $relativePath;
		if (is_null($targetUri)) {
			$skin = self::Current();
		} else {
			$skin = self::At($targetUri);
		}
		if (file_exists(TYPEF_DIR . '/skins/' . $skin . $relativePath)) {
			return TYPEF_WEB_DIR . '/skins/' . $skin . $relativePath;
		}
		if (file_exists(TYPEF_DIR . '/skins/' . TYPEF_SITE_SKIN . $relativePath)) {
			return TYPEF_WEB_DIR . '/skins/' . TYPEF_SITE_SKIN . $relativePath;
		}
		if (file_exists(TYPEF_DIR . '/skins/default' . $relativePath)) {
			return TYPEF_WEB_DIR . '/skins/default' . $relativePath;
		}
		if (file_exists(TYPEF_DIR . '/files/static' . $relativePath)) {
			return TYPEF_WEB_DIR . '/files/static' . $relativePath;
		}
		return false;
	}
	private static function _GetStylesheetsFromElement(SimpleXMLElement $xml, array &$stylesheets) {
		$els = $xml->xpath('//link[@rel="stylesheet"]|//pm:include');
		foreach ($els as $e) {
			if ($e->getName() == 'link') {
				// stylesheet
				$converted = Typeframe::Pagemill()->data()->parseVariables(Typeframe_Attribute_Url::ConvertShortUrlToExpression((string)$e['href']));
				if ($converted) {
					$stylesheets[] = $converted;
				}
			} else if ($e->getName() == 'include') {
				// include
				$inc = Pagemill_SimpleXmlElement::LoadFile(Typeframe_Skin::TemplatePath($e['template']));
				self::_GetStylesheetsFromElement($inc, $stylesheets);
			}
		}
	}
	public static function StylesheetsFor($template, $uri = null) {
		$stylesheets = array();
		$tmplXml = Pagemill_SimpleXmlElement::LoadFile(Typeframe_Skin::TemplatePath($template, $uri));
		if (strpos($tmplXml->asXml(), '<pm:html') !== false) {
			if (is_null($uri)) {
				$skin = self::Current();
			} else {
				$skin = self::At($uri);
			}
			$skinXml = Pagemill_SimpleXmlElement::LoadFile(TYPEF_DIR . '/skins/' . $skin . '/skin.html');
			self::_GetStylesheetsFromElement($skinXml, $stylesheets);
		}
		self::_GetStylesheetsFromElement($tmplXml, $stylesheets);
		return $stylesheets;
	}
}
