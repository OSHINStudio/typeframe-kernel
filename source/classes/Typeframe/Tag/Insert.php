<?php
class Typeframe_Tag_Insert extends Typeframe_Tag_ContentAbstract {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream, $content = null) {
		if (!$this->insidePlugin()) {
			$content = self::Cache();
			$name = $this->getAttribute('name');
			$type = $this->getAttribute('type');
			if ($type == 'html') {
				// Parse short URL attributes
				$urled = @$content[$name];
				$urled = preg_replace('/(<[^>]*? href=")~\//', '$1' . TYPEF_WEB_DIR . '/', $urled);
				$urled = preg_replace('/(<[^>]*? src=")~\//', '$1' . TYPEF_WEB_DIR . '/', $urled);
				@$content[$name] = $urled;
			}
			if ($type == 'model') {
				if (!empty($content[$name])) {
					$model = $content[$name];
					if((string)$model != ''){
						$model = explode(':', $model);
						if(class_exists($model[0])){
							$record = $model[0]::Get($model[1]);
							$content[$name] = $record;
						}
					}
				} else {
					$content[$name] = null;
				}
			}
			$data->set($name, @$content[$name]);
		}
		//$inner = $this->inner($data);
		if (count($this->children()) > 0) {
			foreach($this->children() as $child) {
				$child->process($data, $stream);
			}
		} else {
			$output = '';
			if ($this->getAttribute('noop')) {
				$output = '';
			} else if ($type == 'html') {
				$pm = new Pagemill($data);
				$output = $pm->writeString('<pm:codeblock elements="*" attributes="*">@{' . $name . '}@</pm:codeblock>', true);
			} else if ($type == 'image') {
				// Only display something if something is filled in, ie: no broken images.
				if(@$content[$name] && is_readable(TYPEF_DIR . '/files/public/content/' . @$content[$name])){
					// Allow any other attribute to transparently pass to the image.
					$atts = '';
					foreach($this->attributes() as $k => $v){
						switch($k){
							case 'name':
							case 'label':
							case 'type':
							case 'src':
								break;
							default:
								$atts .= " $k=\"$v\"";
						}
					}
					$output = '<img src="' . TYPEF_WEB_DIR . '/files/public/content/' . $content[$name] . '"' . $atts . '/>';
				}
				else{
					$output = '';
				}
			} else if ($type == 'link') {
				if(!$content[$name] && @$this->getAttribute('ignoreblank') == true) return '';
				$linktitle = (@$this->getAttribute('linktitle'))? $this->getAttribute('linktitle') : 'Click Here';
				$linkstyle = (@$this->getAttribute('linkstyle'))? $this->getAttribute('linkstyle') : '';
				$output = '<a href="' . $content[$name] . '" style="' . $linkstyle . '">' . $linktitle . '</a>';
			} else if ( ($type == 'checkbox') || ($type == 'select') ) {
				// Checkbox and select types are primarily for configurable template logic and do not have default output.
				$output = '';
			} else {
				$output = $content[$name];
			}
			$stream->puts($output);
		}
	}
}
