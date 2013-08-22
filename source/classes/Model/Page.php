<?php
function _pageMeta($record) {
	// Do not try to load the admin path from the registry until the registry
	// has been loaded. This is necessary to avoid causing infinite loops when
	// the registry uses Model_Page to load page data.
	if (Typeframe::RegistryLoaded()) {
		$response = Typeframe::Registry()->applicationAt(TYPEF_WEB_DIR . $record['uri']);
		if ($response) {
			if ($response->application()->admin()) {
				$record['admin'] = TYPEF_WEB_DIR . $response->application()->admin();
			} else {
				$record['admin'] = '';
			}
		}
	}
}

class Model_Page extends BaseModel_Page {
	public function __construct() {
		parent::__construct();
		$this->attach(Dbi_Model::EVENT_AFTERSELECT, new ModelEvent_JsonDecode('settings'));
		$this->attach(Dbi_Model::EVENT_BEFORECREATE, new ModelEvent_Timestamp('datecreated'));
		$this->attach(Dbi_Model::EVENT_BEFORESAVE, new ModelEvent_Timestamp('datemodified'));
		$this->attach(Dbi_Model::EVENT_AFTERSELECT, new ModelEvent_Callback('_pageMeta'));
		$this->publicArrayWhitelist = array('uri', 'nickname', 'application', 'skin', 'settings', 'rules');
		// This is a simple but hacky way to avoid trying to load the site table
		// when it doesn't exist. It assumes that a site without a TYPEF_HOST
		// constant doesn't have multiple sites.
		if (defined('TYPEF_HOST')) {
			$this->leftJoin('site', 'Model_Site', 'site.id = siteid');
		}
	}
}
