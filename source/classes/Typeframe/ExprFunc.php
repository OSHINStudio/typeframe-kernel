<?php
class Typeframe_ExprFunc {
	public static function default_date($date = null) {

		// Don't ask me why NULL evaluates to this, but it does...
		if($date == '-0001-11-30') return '';

		// NULL could also be evaluated to 0000-00-00.
		if($date == '0000-00-00') return '';

		if (is_null($date)) $date = time();
		return Pagemill_ExprFunc::format_date(TYPEF_DEFAULT_DATE_FORMAT, $date);
	}
	public static function default_date_time($date = null) {
		if (is_null($date)) $date = time();
		return Pagemill_ExprFunc::format_date(TYPEF_DEFAULT_DATE_TIME_FORMAT, $date);
	}
	public static function default_date_time_w_seconds($date = null) {
		if (is_null($date)) $date = time();
		return Pagemill_ExprFunc::format_date(TYPEF_DEFAULT_DATE_TIME_FORMAT_WITH_SECONDS, $date);
	}
	public static function resize_image($src, $width, $height, $ratio) {
		//return Pagemill_Tag_Timg::_ResizeImage($src, $width, $height, $ratio);
		// TODO: Is this good enough?
		$file = TYPEF_DIR . substr($src, strlen(TYPEF_WEB_DIR));
		// TODO: Should this generate an error?
		if (!file_exists($file) || !is_file($file)) return $src;
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		if (strtolower($ext) == 'bmp') $ext = 'jpg';
		$md5 = md5("{$src}_{$width}_{$height}_{$ratio}") . ".{$ext}";
		if (file_exists(TYPEF_DIR . "/files/public/timg/{$md5}")) {
			if (filemtime($file) < filemtime(TYPEF_DIR .'/files/public/timg/'.$md5)) {
				return TYPEF_WEB_DIR . "/files/public/timg/{$md5}";
			}
		}
		// Resize image now if the file is below a particular
		// size. We'll try it with 900kb for now.
		if (filesize($file) < 900000) {
			Gdi::Thumbnail($file, TYPEF_DIR .'/files/public/timg/'.$md5, $width, $height, $ratio);
			if (file_exists(TYPEF_DIR.'/files/public/timg/'.$md5)) {
				return TYPEF_WEB_DIR.'/files/public/timg/'.$md5;
			}
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
				$timg['ratio'] = $ratio;
				$timg->save();
			}
		}
		return $src;
	}
}
