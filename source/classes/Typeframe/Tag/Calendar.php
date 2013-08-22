<?php
class Typeframe_Tag_Calendar extends Pagemill_Tag {
	private static $_calID = -1;
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$data = $data->fork();
		// Will contain the variables to do operations against before I send them all to the pagemill.
		$atts = array();

		foreach ($this->attributes() as $k => $v) {
			//$pm->setVariable($k, $data->parseVariables($v));
			$atts[$k] = $data->parseVariables($v);
		}

		// ID needs to be set if it's not.
		if (!isset($atts['id'])) {
			self::$_calID++;
			//$pm->setVariable('id', 'cal_' . CalendarTag::$_calID);
			$atts['id'] = 'cal_' . self::$_calID;
		}
		$atts['showtime'] = (isset($atts['showtime']))? 'true' : 'false';
		// Is typeframe default configured to display time as a 24-hr or 12-hr clock?
		$h24 = (strpos('g', TYPEF_DEFAULT_DATE_TIME_FORMAT) !== false || strpos('h', TYPEF_DEFAULT_DATE_TIME_FORMAT) !== false);


		// Allow the option of easily selecting dates in the past.
		// This is really a shorthand method of setting some variables.
		if(isset($atts['inpast']) && $atts['inpast'] == 'true'){
			if(!isset($atts['minDate'])) $atts['minDate'] = '-60y';
			if(!isset($atts['maxDate'])) $atts['maxDate'] = '-12y';
			// translate the min/max date to year ranges.
			$minyear = preg_match('/[\-\+]{0,1}[0-9]*y/i', $atts['minDate'])? preg_replace('/([\-\+]{0,1}[0-9]*)y/i', '$1', $atts['minDate']) : 0;
			$maxyear = preg_match('/[\-\+]{0,1}[0-9]*y/i', $atts['maxDate'])? preg_replace('/([\-\+]{0,1}[0-9]*)y/i', '$1', $atts['maxDate']) : 0;
			if(!isset($atts['defaultDate'])) $atts['defaultDate'] = ( ($minyear + $maxyear) / 2 ) . 'y';
			if(!isset($atts['yearRange'])) $atts['yearRange'] = $minyear . ':+' . abs($minyear);
			$atts['monthNames'] = "['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']";
			$atts['changeYear'] = 'true';
		}


		// More defaults
		if(!isset($atts['monthNames'])) $atts['monthNames'] = "['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']";
		if(!isset($atts['minDate'])) $atts['minDate'] = 'null';
		if(!isset($atts['maxDate'])) $atts['maxDate'] = 'null';
		if(!isset($atts['yearRange'])) $atts['yearRange'] = 'c-10:c+10';
		if(!isset($atts['changeYear'])) $atts['changeYear'] = 'false';
		// PHP has some differences with Javascript.
		$dateformatdiffs = array(
			'n' => 'm',
			'j' => 'd',
			'Y' => 'yy'
		);
		$dateFormat = (isset($atts['dateFormat']) ? $atts['dateFormat'] : TYPEF_DEFAULT_DATE_FORMAT);
		if(!isset($atts['dateFormat'])) {
			$atts['dateFormat'] = str_replace(array_keys($dateformatdiffs), array_values($dateformatdiffs), TYPEF_DEFAULT_DATE_FORMAT);
		} else {
			$atts['dateFormat'] = str_replace(array_keys($dateformatdiffs), array_values($dateformatdiffs), $atts['dateFormat']);
		}
		if(!isset($atts['defaultDate'])) $atts['defaultDate'] = 'null';
		if(!isset($atts['time24h'])) $atts['time24h'] =  ($atts['showtime'] == 'true' && $h24)? 'true' : 'false';
		if(!isset($atts['input24h'])) $atts['input24h'] =  ($atts['showtime'] == 'true' && $h24)? 'true' : 'false';


		// Format the incoming value if it's set.  It should be formatted in the same manner.
		if (isset($atts['value'])) {
			// Because the timepicker doesn't allow for a formatted time string....
			if($atts['showtime'] == 'true'){
				$valueformat = $dateFormat . (($h24)? ' H:i' : ' h:i a');
			} else {
				$valueformat = $dateFormat;
			}
			if($atts['value'] == '0000-00-00' || $atts['value'] == '0000-00-00 00:00:00') {
				$atts['value'] = '';
			}
			if ($atts['value']) {
				$atts['formattedvalue'] = Pagemill_ExprFunc::format_date($valueformat, $atts['value']);
			}
		}


		// Pad the appropriate fields so they don't bomb out the javascript.
		// (These generally can be null, so the JS needs them to be appropriately escaped, ie: null vs. 'some string').
		if($atts['minDate'] != 'null') $atts['minDate'] = "'" . $atts['minDate'] . "'";
		if($atts['maxDate'] != 'null') $atts['maxDate'] = "'" . $atts['maxDate'] . "'";
		if($atts['defaultDate'] != 'null') $atts['defaultDate'] = "'" . $atts['defaultDate'] . "'";


		/*$pm->setVariable('ajaxie', requestIsAjax());
		$pm->setVariableArray($atts);

		$tree = $pm->writeText('<pm:include template="/datetimepicker.inc.html"/>');
		return $tree;*/
		$data->setArray($atts);
		$include = new Typeframe_Tag_Include('include', array('template' => '/datetimepicker.inc.html'), $this);
		$include->process($data, $stream);
	}
}
