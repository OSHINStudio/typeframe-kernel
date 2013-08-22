<?php

class Typeframe_Tag_Debug extends Pagemill_Tag {
	private function _recurseTines(Pagemill_Data $base) {
		$base['tines'] = array();
		foreach($base['data']->tines() as $tine) {
			$debug = new Pagemill_Data();
			$debug['data'] = $tine;
			$base['tines'][] = $debug;
			$this->_recurseTines($debug);
		}
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		Typeframe::Timestamp('Starting debug output');
		$debug = new Pagemill_Data();
		$timestamps = Typeframe::GetTimestamps();
		$tdata = array();
		if ($timestamps) {
			$begin = $timestamps[0]->time();
			foreach ($timestamps as $t) {
				//$dump .= "{$t->action()}: " . ($t->time() - $begin) . "<br/>";
				$tdata[] = array('action' => $t->action(), 'time' => ($t->time() - $begin));
			}
		}
		$debug['timestamps'] = $tdata;
		$debug['memory_used'] = memory_get_usage();
		$debug['memory_used_real'] = memory_get_usage(true);
		$debug['includes'] = get_included_files();
		$debug['querycount'] = Dbi_Source::QueryCount();
		$debug['templates'] = Pagemill::ProcessedTemplates();
		// TODO: Get template files
		$debug['data'] = $data;
		$this->_recurseTines($debug);
		$include = new Typeframe_Tag_Include('include', array('template' => '/pagemill/tag/debug.html'));
		$include->process($debug, $stream);
	}
}
