<?php

class Typeframe_TagPreprocessor_BodyAttributes extends Pagemill_TagPreprocessor {
	public function process(\Pagemill_Tag $tag, \Pagemill_Data $data, \Pagemill_Stream $stream) {
		$pmBody = Typeframe_TagPreprocessor_Export::Peek('body');
		if ($pmBody) {
			foreach ($pmBody->attributes() as $key => $value) {
				$tag->setAttribute($key, $value);
			}
		}
	}
}
