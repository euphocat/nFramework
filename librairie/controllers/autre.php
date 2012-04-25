<?php
/**
 * Controleur de test
 *
 * @author Nicolas Baptiste
 */
Class Autre extends Controller{
	public function index() {
		$view = Load::view('home',$data);
		$this->afficherVue($view);
	}
}
?>