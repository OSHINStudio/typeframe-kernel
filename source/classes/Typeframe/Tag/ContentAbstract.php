<?php
abstract class Typeframe_Tag_ContentAbstract extends Pagemill_Tag {
	/**
	 * Detect whether the tag is inside a Content plugin. This is useful for
	 * determining whether a content tag needs to load page content.
	 * @return boolean
	 */
	protected function insidePlugin() {
		$parent = $this->parent();
		while (!is_null($parent)) {
			if ($parent instanceof Plugin_Content) {
				return true;
			}
			$parent = $parent->parent();
		}
		return false;
	}
	/**
	 * Get the cached page content.
	 * @return array
	 */
	public static function Cache() {
		static $cached_content = null;
		if (is_null($cached_content)) {
			if (Typeframe::CurrentPage()->application()->name() == 'Content Admin') {
				$cached_content = $_POST;
			} else {
				if (Typeframe::CurrentPage()->page()->pageid()) {
					$page = Model_Content_Page::Get(Typeframe::CurrentPage()->page()->pageid());
					$cached_content = $page['content'];
				}
			}
		}
		return $cached_content;
	}
}
