<?php
if (!function_exists('imagecreatefrombmp')) {
	function ConvertBMP2GD($src, $dest = false) {
		if(!($src_f = fopen($src, "rb"))) {
			return false;
		}
		if(!($dest_f = fopen($dest, "wb"))) {
			fclose($src_f);
			return false;
		}
		$header = unpack("vtype/Vsize/v2reserved/Voffset", fread($src_f,
		14));
		$info = unpack("Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant",
		fread($src_f, 40));

		extract($info);
		extract($header);

		if($type != 0x4D42) {           // signature "BM"
			fclose($src_f);
			fclose($dst_f);
			return false;
		}

		$palette_size = $offset - 54;
		$ncolor = $palette_size / 4;
		$gd_header = "";
		// true-color vs. palette
		$gd_header .= ($palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF";
		$gd_header .= pack("n2", $width, $height);
		$gd_header .= ($palette_size == 0) ? "\x01" : "\x00";
		if($palette_size) {
			$gd_header .= pack("n", $ncolor);
		}
		// no transparency
		$gd_header .= "\xFF\xFF\xFF\xFF";

		fwrite($dest_f, $gd_header);

		if($palette_size) {
			$palette = fread($src_f, $palette_size);
			$gd_palette = "";
			$j = 0;
			while($j < $palette_size) {
				$b = $palette{$j++};
				$g = $palette{$j++};
				$r = $palette{$j++};
				$a = $palette{$j++};
				$gd_palette .= "$r$g$b$a";
			}
			$gd_palette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);
			fwrite($dest_f, $gd_palette);
		}

		$scan_line_size = (($bits * $width) + 7) >> 3;
		$scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size &
		0x03) : 0;

		for($i = 0, $l = $height - 1; $i < $height; $i++, $l--) {
			// BMP stores scan lines starting from bottom
			fseek($src_f, $offset + (($scan_line_size + $scan_line_align) *
			$l));
			$scan_line = fread($src_f, $scan_line_size);
			if($bits == 24) {
				$gd_scan_line = "";
				$j = 0;
				while($j < $scan_line_size) {
					$b = $scan_line{$j++};
					$g = $scan_line{$j++};
					$r = $scan_line{$j++};
					$gd_scan_line .= "\x00$r$g$b";
				}
			}
			else if($bits == 8) {
				$gd_scan_line = $scan_line;
			}
			else if($bits == 4) {
				$gd_scan_line = "";
				$j = 0;
				while($j < $scan_line_size) {
					$byte = ord($scan_line{$j++});
					$p1 = chr($byte >> 4);
					$p2 = chr($byte & 0x0F);
					$gd_scan_line .= "$p1$p2";
				}
				$gd_scan_line = substr($gd_scan_line, 0, $width);
			}
			else if($bits == 1) {
				$gd_scan_line = "";
				$j = 0;
				while($j < $scan_line_size) {
					$byte = ord($scan_line{$j++});
					$p1 = chr((int) (($byte & 0x80) != 0));
					$p2 = chr((int) (($byte & 0x40) != 0));
					$p3 = chr((int) (($byte & 0x20) != 0));
					$p4 = chr((int) (($byte & 0x10) != 0));
					$p5 = chr((int) (($byte & 0x08) != 0));
					$p6 = chr((int) (($byte & 0x04) != 0));
					$p7 = chr((int) (($byte & 0x02) != 0));
					$p8 = chr((int) (($byte & 0x01) != 0));
					$gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";
				}
				$gd_scan_line = substr($gd_scan_line, 0, $width);
			}
			fwrite($dest_f, $gd_scan_line);
		}
		fclose($src_f);
		fclose($dest_f);
		return true;

	}
	function imagecreatefrombmp($filename) {
		global $uploadpath;
		$tmp_name = tempnam($uploadpath, "GD");
		if(ConvertBMP2GD($filename, $tmp_name)) {
			$img = imagecreatefromgd($tmp_name);
			unlink($tmp_name);
			return $img;
		}
		return false;
	}
}

class Gdi {
	private static function _GetFileData($filename, $size = null) {
		$returnsize = is_null($size);
		if ($returnsize) $size = getimagesize($filename);
		$width  = $size[0];
		$height = $size[1];
		$type   = $size[2];
		switch ($type) {
			case 1:
				$data = imagecreatefromgif($filename);
			break;
			case 2:
				$data = imagecreatefromjpeg($filename);
			break;
			case 3:
				$data = imagecreatetruecolor($width, $height);
				imagealphablending($data, true);
				imagesavealpha($data, true);
				$transparent = imagecolorallocatealpha($data, 0, 0, 0, 127);
				imagefill($data, 0, 0, $transparent);
				$temp = imagecreatefrompng($filename);
				imagealphablending($temp, true);
				imagesavealpha($temp, true);
				imagecopyresampled($data, $temp, 0, 0, 0, 0, $width, $height, $width, $height);
			break;
			case 6:
				$data = imagecreatefrombmp($filename);
			break;
			default:
				$data = null;
			break;
		}
		return ($returnsize ? array($data, $size) : $data);
	}
	/**
	 * Generate a thumbnail of an image. If the specified width and height are
	 * smaller than the source, the destination file will have the source's
	 * dimensions.
	 * @param string $srcfile The source image.
	 * @param string $dstfile The destination file.
	 * @param int $width The (maximum) width of the thumbnail.
	 * @param int $height The (maximum) height of the thumbnail.
	 * @param boolean $ratio If true, the thumbnail will retain the source
	 * image's aspect ratio within the boundaries of the specified width and
	 * height.
	 */
	public static function Thumbnail($srcfile, $dstfile, $width = 200, $height = 200, $ratio = false) {
		$size = getimagesize($srcfile);
		$oldwidth  = $size[0];
		$oldheight = $size[1];
		$imagetype = $size[2];
		if (($oldwidth < $width) && ($oldheight < $height)) return;
		if (($width >= $oldwidth) && ($height >= $oldheight)) return;
		$s = self::_GetFileData($srcfile, $size);
		if (!is_null($s)) {
			if ($ratio) {
				// try reverse normalizing oldwidth and oldheight
				$oldmax = min($oldwidth, $oldheight);
				$norm_w = ($oldwidth  / $oldmax);
				$norm_h = ($oldheight / $oldmax);
				// try fitting it into the max of width and height
				$newmax  = max($width, $height);
				$_width  = floor($norm_w * $newmax);
				$_height = floor($norm_h * $newmax);
				// exceeds bounding box
				if (($_width > $width) || ($_height > $height)) {
					// try fitting it into the min of width and height
					$newmin  = min($width, $height);
					$_width  = floor($norm_w * $newmin);
					$_height = floor($norm_h * $newmin);
					// exceeds bounding box
					if (($_width > $width) || ($_height > $height)) {
						// try normalizing oldwidth and oldheight
						$oldmax = max($oldwidth, $oldheight);
						$norm_w = ($oldwidth  / $oldmax);
						$norm_h = ($oldheight / $oldmax);
						// try fitting it into the max of width and height
						$newmax  = max($width, $height);
						$_width  = floor($norm_w * $newmax);
						$_height = floor($norm_h * $newmax);
						// exceeds bounding box
						if (($_width > $width) || ($_height > $height)) {
							// try fitting it into the min of width and height
							$newmin  = min($width, $height);
							$_width  = floor($norm_w * $newmin);
							$_height = floor($norm_h * $newmin);
						}
					}
				}
				$width  = max($_width,  1);
				$height = max($_height, 1);
			}
			$d = imagecreatetruecolor($width, $height);
			if (3 == $imagetype) {
				imagealphablending($d, true);
				imagesavealpha($d, true);
				$trans = imagecolorallocatealpha($d, 0, 0, 0, 127);
				imagefill($d, 0, 0, $trans);
			}
			imagecopyresampled($d, $s, 0, 0, 0, 0, $width, $height, $oldwidth, $oldheight);
			switch ($imagetype) {
				case 1:  imagegif($d,  $dstfile);      break;
				case 3:  imagepng($d,  $dstfile);      break;
				default: imagejpeg($d, $dstfile, 100); break;
			}
		}
	}
	/**
	 * Rotate an image.
	 * @param string $srcfile The source image.
	 * @param string $dstfile The destination file.
	 * @param float $angle Rotation angle, in degrees. The rotation angle is interpreted as the number of degrees to rotate the image anticlockwise.
	 */
	public static function Rotate($srcfile, $dstfile, $angle) {
		// load data from image file; on fail, return source filename
		list($data, $size) = self::_GetFileData($srcfile);
		if (is_null($data)) return $srcfile;
		// get image type
		$type = $size[2];
		// rotate the image and save it
		switch ($type)
		{
			case 1:
				// this is needed because there's a bug in GD:
				// when you rotate transparent GIFs by multiples
				// of 90 degrees, the transparency is lost
				$index = imagecolortransparent($data);
				if ($index >= 0) {
					$color   = imagecolorsforindex($data, $index);
					$index   = imagecolorallocate($data, $color['red'], $color['green'], $color['blue']);
					$rotated = imagerotate($data, $angle, $index);
					$index   = imagecolorexactalpha($rotated, $color['red'], $color['green'], $color['blue'], $color['alpha']);
					imagecolortransparent($rotated, $index);
				} else {
					$rotated = imagerotate($data, $angle, 0);
				}
				imagegif($rotated, $dstfile);
			break;
			case 3:
				$rotated = imagerotate($data, $angle, -1);
				imagealphablending($rotated, true);
				imagesavealpha($rotated, true);
				imagepng($rotated, $dstfile);
			break;
			default:
				$rotated = imagerotate($data, $angle, 0);
				imagejpeg($rotated, $dstfile, 100);
			break;
		}
		// return destination filename
		return $dstfile;
	}
}
