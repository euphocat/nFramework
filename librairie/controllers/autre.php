<?php
Class Autre extends Controller{
	public function index() {
		//$trace = debug_backtrace();
		//var_dump($trace);
		//krsort($trace);
		$view = Load::view('home',$data);
		$this->afficherVue($view);
	}
}
?>