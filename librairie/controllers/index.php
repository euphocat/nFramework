<?php
Class Index extends \Controller{
	/**
	 * Appel explicite de la méthode magique __construct
	 * sinon confusion avec la méthode index()
	 *
	 * @param array $options
	 */
	public function __construct($options) {
		parent::__construct($options);
	}
	public function index() {
		
		$data['test'] = "salut!!";
		
		//$this->addNotif("Test de notification");
		//$this->addError("Grosse erreur !");
		
		$view = new View('home',$data);
		$this->afficherVue($view);
	}
	public function test() {
		
		$this->addNotif("Test de notification poilue");
		$this->addError("Grosse erreur poilue!");
		
		$data = array('rien','et encore rien');
		
		$this->templateData['test'] = "variable de template";
				
		$view = new View('home',$data);
		$this->afficherVue($view);
	}
	public function testException() {
		//$view = Load::view('pasla',$data);
		//$this->afficherVue($view);
	}
	public function testdb() {
		
		$modeltest = new ModelTest();
		$modeltest->getLigne("5", "rien");
	}
	public function faq() {
		$view = new View('home',$data);
		$this->afficherVue($view, true);
	}
	public function testroute() {
		$view = new View('route',$data);
		$this->afficherVue($view, true);
	}
}
?>