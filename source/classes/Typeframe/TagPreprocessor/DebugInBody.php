<?php

class Typeframe_TagPreprocessor_DebugInBody extends Pagemill_TagPreprocessor {
	private $_runOnce = false;
	public function process(\Pagemill_Tag $tag, \Pagemill_Data $data, \Pagemill_Stream $stream) {
		//if (!$this->_runOnce) {
			$this->_runOnce = true;
			if (defined('TYPEF_DEBUG')) {
				if ( (TYPEF_DEBUG == 'all') || ( (TYPEF_DEBUG == 'admin') && (Typeframe::User()->get('usergroupid') == TYPEF_ADMIN_USERGROUPID) ) ) {
					// Don't include debug info in AJAX requests
					if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
						$debug = new Typeframe_Tag_Debug('debug', array(), $tag);
					}
				}
			}
		//}
	}
}
