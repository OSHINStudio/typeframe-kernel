<?php

class Plugin_Driver extends Plugin {
	public function admin(PMDataNode $node) {
		$pm = new Pagemill($node->fork());
		$this->settings = array_merge(array(
			'driver' => ''
		), $this->settings);
		$pm->setVariableArray($this->settings);
		$db = Typeframe::Database();
		
		// @todo Should this be in a central location somewhere?
		// Assemble a list of "Driverable" pages for the driver option.
		//$driverables = array();
		$rs = $db->prepare('SELECT pageid, nickname FROM #__page WHERE driver != "" ORDER BY nickname');
		$rs->execute();
		// Add the "default/none" option.
		$pm->addLoop('driverables', array('pageid' => '', 'nickname' => 'None/Blank'));
		while($data = $rs->fetch_array()){
		//	$driverables[] = $data;
			$pm->addLoop('driverables', $data);
		}
		
		return $pm->writeText('<pm:include template="/admin/pluginforms/driver.html" />');
	}
	
	public function update() {
		$db = Typeframe::Database();
		
		$settings = json_encode($_POST['settings'] ? $_POST['settings'] : array());
		
		// Make the name something meaningful for the admin listing table.
		if($_POST['settings']['driver']){
			$rs = $db->prepare('SELECT IF(nickname != "", nickname, uri) title FROM `typef_page` WHERE pageid = ?');
			$rs->execute($_POST['settings']['driver']);
			$data = $rs->fetch_array();
			$title = $data['title'];
		}
		else{
			$title = '';
		}
		
		$rs = $db->prepare('UPDATE #__plug SET settings = ?, name = ? WHERE plugid = ?');
		
		$rs->execute($settings, $title, $_POST['plugid']);
	}
	
	public function output(PMDataNode $data) {
		$pm = new Pagemill($data->fork());
		//$pm->setVariableArray($this->settings);
		$db = Typeframe::Database();
		
		if(!isset($this->settings['driver'])){
			//return '<div class="error"><p>Please provide a "driver" attribute on the pm:driver plugin.</p></div>';
			return '';
		}
		
		// Blank driver attributes count as nothing too.
		if(!$this->settings['driver']){
			return '';
		}
		
		// I need to lookup this driver as a page and ensure it's a valid driver.
		$driver = (int) $this->settings['driver'];
		$rs = $db->prepare('SELECT uri, nickname, driver FROM #__page WHERE driver != "" AND pageid = ?');
		$rs->execute($driver);
		if(!$rs->recordcount()){
			// No records found... just silently fail.
			return '';
		}
		
		$data = $rs->fetch_array();
		$pm->setVariable('link', TYPEF_WEB_DIR . $data['uri']);
		$pm->setVariable('title', $data['nickname']);
		$pm->setVariable('driver', $data['driver']);
		
		return $pm->writeText('<pm:include template="/plugins/driver.html" />');
	}
}
?>