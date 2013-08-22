<?php
interface Typeframe_Application_HandlerInterface {
	public function __construct(Typeframe_Page $page);
	public function page();
	public function allow();
	public function start();
	public function finish();
}
