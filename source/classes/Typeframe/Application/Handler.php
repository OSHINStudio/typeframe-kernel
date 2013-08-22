<?php
/**
 * The default application handler for pages.
 */
class Typeframe_Application_Handler implements Typeframe_Application_HandlerInterface {
	private $_page;
	final public function __construct(Typeframe_Page $page) {
		$this->_page = $page;
	}
	final public function page() {
		return $this->_page;
	}
	public function allow() {
		// Users in the admin group always pass permission tests.
		if (Typeframe::User()->get('usergroupid') == TYPEF_ADMIN_USERGROUPID) return true;
		if ($this->_page->pageid()) {
			$pageperm = new Model_PagePerm();
			$pageperm->where('pageid = ?', $this->_page->pageid());
			if ($pageperm->count() == 0) return true;
			$pageperm->where('usergroupid = ? OR usergroupid = 0', Typeframe::User()->get('usergroupid'));
			return ($pageperm->count() > 0);
		}
		return true;
	}
	public function start() {

	}
	public function finish() {

	}
}
