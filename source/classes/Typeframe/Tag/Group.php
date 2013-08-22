<?php

class Typeframe_Tag_Group extends Typeframe_Tag_ContentAbstract {
	private static $_subPageSelected = false;
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$members = array();
		foreach ($this->children() as $child) {
			if (is_a($child, 'Pagemill_Tag') && $child->name() == 'pm:member') {
				$members[] = $child->attributes();
			}
		}
		if ($this->insidePlugin()) {
			// We are inside a plugin.  Content should have been passed into
			// the data node through the plugin's settings.
			$row = $data->get($this->getAttribute('name'));
			foreach ($members as $member) {
				if ($member['type'] == 'html') {
					// Parse short URL attributes
					$urled = @$row[$member['name']];
					$urled = preg_replace('/(<[^>]*? href=")~\//', '$1' . TYPEF_WEB_DIR . '/', $urled);
					$urled = preg_replace('/(<[^>]*? src=")~\//', '$1' . TYPEF_WEB_DIR . '/', $urled);
					$row[$member['name']] = $urled;
				}
			}
			$data->set($this->getAttribute('name'), $row);
		} else {
			$content = self::Cache();
			$rows = @$content[$this->getAttribute('name')];
			$index = 0;
			if (is_array($rows)) {
				$data[$this->getAttribute('name')] = array();
				foreach ($rows as $row) {
					foreach ($members as $member) {
						// Templates can leave the type attribute undefined and
						// assume a text field. Check here if it's set to avoid
						// error notices.
						if (isset($member['type'])) {
							if ($member['type'] == 'html') {
								// Parse short URL attributes
								$urled = @$row[$member['name']];
								$urled = preg_replace('/(<[^>]*? href=")~\//', '$1' . TYPEF_WEB_DIR . '/', $urled);
								$urled = preg_replace('/(<[^>]*? src=")~\//', '$1' . TYPEF_WEB_DIR . '/', $urled);
								$row[$member['name']] = $urled;
							}
							if ($member['type'] == 'model'){
								$model = $row[$member['name']];
								if((string)$model != ''){
									$model = explode(':', $model);
									if(class_exists($model[0])){
										$record = $model[0]::Get($model[1]);
										$row[$member['name']] = $record;
									}
								}
							}
						}
					}
					if ($this->getAttribute('subpage')) {
						$uri = makeFriendlyUrlText($row[$this->getAttribute('subpage')]);
						//if ('list' == $this->getAttribute('firstsubpage')) {
						if ($this->getAttribute('noindex')) {
							$row['uri'] = $uri;
							$pathinfo = Typeframe::CurrentPage()->pathInfo();
							if (($pathinfo == $uri) && !self::$_subPageSelected) {
								$row['content_selected'] = true;
								self::$_subPageSelected = true;
							}
						} else {
							//if (($index > 0) || self::$_mainPageSet) {
								$row['uri'] = $uri;
							//} else {
							//	$row['uri'] = '';
							//	self::$_mainPageSet = true;
							//}
							$pathinfo = Typeframe::CurrentPage()->pathInfo();
							//if ((($pathinfo == $uri) || (!$pathinfo && (0 == $index))) && !self::$_subPageSelected) {
							if ( ($pathinfo == $uri) || ($this->getAttribute('noindex') && ($index == 0)) ) {
								$row['content_selected'] = true;
								self::$_subPageSelected = true;
							}
						}
					}
					//$data->addChild(array($this->getAttribute('name'), $row));
					//$data['name'][] = $row;
					$data[$this->getAttribute('name')][] = $row;
					$index++;
				}
			}
		}
	}
}
