<?php
class Typeframe_TagPreprocessor_Thumb extends Pagemill_TagPreprocessor {
	private $_ratio;
	public function __construct($ratio = true) {
		$this->_ratio = $ratio;
	}
	public function process(Pagemill_Tag $tag, Pagemill_Data $data, Pagemill_Stream $stream) {
		$src = $data->parseVariables($tag->getAttribute('src'));
		$attr = 'src';
		if (!$src) {
			$src = $data->parseVariables($tag->getAttribute('href'));
			$attr = 'href';
		}
		$width = $data->parseVariables($tag->getAttribute('width'));
		$height = $data->parseVariables($tag->getAttribute('height'));
		// TODO: Ignore unspecified width and height?
		if (!$width && !$height) return;
		// TODO: Is this good enough?
		$file = TYPEF_DIR . substr($src, strlen(TYPEF_WEB_DIR));
		// TODO: Should this generate an error?
		if (!file_exists($file) || !is_file($file)) return;
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		if (strtolower($ext) == 'bmp') $ext = 'jpg';
		$md5 = md5("{$src}_{$width}_{$height}_{$this->_ratio}") . ".{$ext}";
		if (file_exists(TYPEF_DIR . "/files/public/timg/{$md5}")) {
			if (filemtime($file) < filemtime(TYPEF_DIR .'/files/public/timg/'.$md5)) {
				$tag->setAttribute($attr, TYPEF_WEB_DIR . "/files/public/timg/{$md5}");
				$size = getimagesize(TYPEF_DIR . "/files/public/timg/{$md5}");
				$tag->setAttribute('width', $size[0]);
				$tag->setAttribute('height', $size[1]);
				return;
			}
		}
		// Resize image now if the file is below a particular
		// size. We'll try it with 900kb for now.
		if (filesize($file) < 900000) {
			Gdi::Thumbnail($file, TYPEF_DIR .'/files/public/timg/'.$md5, $width, $height, $this->_ratio);
			if (file_exists(TYPEF_DIR.'/files/public/timg/'.$md5)) {
				$tag->setAttribute($attr, TYPEF_WEB_DIR . "/files/public/timg/{$md5}");
			}
			$tag->removeAttribute('width');
			$tag->removeAttribute('height');
		} else {
			// Schedule the resizing.
			$queue = new Model_TimgQueue();
			$queue->where('src = ?', $file);
			$queue->where('dst = ?', TYPEF_DIR . "/files/public/timg/{$md5}");
			if (!$queue->count()) {
				$timg = Model_TimgQueue::Create();
				$timg['src'] = $file;
				$timg['dst'] = TYPEF_DIR . "/files/public/timg/{$md5}";
				$timg['width'] = $width;
				$timg['height'] = $height;
				$timg['ratio'] = $this->_ratio;
				$timg->save();
			}
		}
	}
}
