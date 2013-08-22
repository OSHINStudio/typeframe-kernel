<?php

class Typeframe_Doctype extends Pagemill_Doctype_Template {
	public function __construct($nsPrefix) {
		$this->nsUri = 'http://typeframe.com/pagemill';
		parent::__construct($nsPrefix);
		
		$this->registerTag('socket', 'Typeframe_Tag_Socket');
		$this->registerTag('plugin', 'Typeframe_Tag_Plugin');
		$this->registerTag('pluginadmin', 'Typeframe_Tag_PluginAdmin');
		$this->registerTag('html', 'Typeframe_Tag_Html');
		$this->registerTag('head', 'Typeframe_Tag_Head');
		$this->registerTag('body', 'Typeframe_Tag_Body');
		$this->registerTag('import', 'Typeframe_Tag_Import');
		$this->registerTag('include', 'Typeframe_Tag_Include');
		$this->registerTag('editor', 'Typeframe_Tag_Editor');
		$this->registerTag('calendar', 'Typeframe_Tag_Calendar');
		$this->registerTag('select', 'Typeframe_Tag_Select'); // TODO: Deprecate
		$this->registerTag('insert', 'Typeframe_Tag_Insert');
		$this->registerTag('group', 'Typeframe_Tag_Group');
		$this->registerTag('scriptonce', 'Typeframe_Tag_Scriptonce');
		$this->registerTag('codeblock', 'Typeframe_Tag_Codeblock');
		$this->registerTag('debug', 'Typeframe_Tag_Debug');
		$this->registerTag('timg', 'Typeframe_Tag_Timg'); // TODO: Deprecate
		$this->registerTag('/body', 'Typeframe_Tag_Html_Body');
		$this->registerTag('fileupload', 'Typeframe_Tag_FileUpload');
		$this->registerTag('imageupload', 'Typeframe_Tag_ImageUpload');
		$this->registerTag('postlink', 'Typeframe_Tag_Postlink');
		$this->registerTag('checkbox', 'Typeframe_Tag_Checkbox');

		foreach (Typeframe::Registry()->tags() as $tag) {
			$this->registerTag($tag['name'], $tag['class']);
		}
		
		$this->registerAttribute('/href', 'Typeframe_Attribute_Url');
		$this->registerAttribute('/src', 'Typeframe_Attribute_Url');
		$this->registerAttribute('/action', 'Typeframe_Attribute_Url');
		$this->registerAttribute('method', 'Typeframe_Attribute_Method');
		$this->registerAttribute('confirm', 'Typeframe_Attribute_Confirm');
	}
}
