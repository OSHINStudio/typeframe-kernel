<?php
/**
 * The admin application handler for pages.
 */
class Typeframe_Application_Handler_Admin extends Typeframe_Application_Handler {
	public function allow() {
		if (!Typeframe::User()->loggedIn()) {
			return false;
		}
		if (Typeframe::User()->get('usergroupid') == TYPEF_ADMIN_USERGROUPID) {
			return true;
		}
		$relativeUri = substr($this->page()->uri(), strlen(TYPEF_WEB_DIR));
		if ($relativeUri == '/admin' || $relativeUri == '/admin/') {
			// Main admin page.  Just check to see if the user has access to any other applications.
			$rs = Typeframe::Database()->execute('SELECT * FROM #__usergroup_admin WHERE usergroupid = ?', Typeframe::User()->get('usergroupid'));
			return (count($rs) > 0);
		}
		$rs = Typeframe::Database()->execute('SELECT * FROM #__usergroup_admin WHERE usergroupid = ? AND application = ?', Typeframe::User()->get('usergroupid'), $this->page()->application()->name() . '\'');
		return ($rs->count() > 0);
	}
}
