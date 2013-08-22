<?php
interface PluginInterface {
	public function admin(Pagemill_Data $data, Pagemill_Stream $stream, Pagemill_Tag $tag);
	public function output(Pagemill_Data $data, Pagemill_Stream $stream, Pagemill_Tag $tag);
	public function update(array $input = null);
}
