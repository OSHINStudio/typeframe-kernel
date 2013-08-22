<?php

/**
 * This tag creates an upload field for files.
 *
 * Usage:
 * <pm:fileupload name="" value="" id="" class=""/>
 *
 * Name is mandatory, it's the input field's name.
 * Value is optional, but the thumbnail/already uploaded option will only display if this set.
 * Dir is optional, it's the basedirectory the image is uploaded to, and will be used in the thumbnail generation.
 */
class Typeframe_Tag_FileUpload extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {

		// A list of attributes and settings to send to the template.
		$attribs = array();
		$settings = array();
		foreach ($this->attributes() as $k => $v) {
			switch($k){
				// The 'extra' attributes, these are settings that control something.
				case 'value':
				case 'name':
				case 'dir':
				case 'id':
					$settings[$k] = $data->parseVariables($v);
					break;
				// Any additional attribute, will be passed to the div containing the elemnts.
				default:
					$attribs[] = array('name' => $k, 'value' => $data->parseVariables($v));
					break;
			}
		}

		if(!isset($settings['name'])) return '';

		// I need to ensure some exist.
		if(!(isset($settings['id']) && $settings['id'])) $settings['id'] = 'fileuploadtag-' . preg_replace('/[^a-zA-Z0-9]/', '_', $settings['name']) . '-' . rand(0, 9) . rand(0, 9) . rand(0, 9);
		
		$data = $data->fork();
		$max_upload = (int) ini_get('upload_max_filesize');
		$max_post = (int) ini_get('post_max_size');
		$memory_limit = (int) ini_get('memory_limit');
		$upload_mb = min($max_upload, $max_post, $memory_limit);
		$data->set('maxfilesize', $upload_mb);
		$data->setArray($settings);
		$data->set('attributes', $attribs);
		Typeframe_Tag_Include::Generate('/pagemill/tag/fileupload.html', $this)->process($data, $stream);
	}
}
