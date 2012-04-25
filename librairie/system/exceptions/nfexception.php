<?php
Class NfException extends Exception{
	/**
	 * Heure à laquelle s'est produit l'exception
	 * 
	 * @var DateNF
	 */
	protected $heure = null;
	
	public function __construct($message = null,$code = null,$previous = null){
		parent::__construct($message,$code,$previous);
		$this->heure = new DateNF();
	}
	
	public function getHeure() {
		if(is_null($this->heure))
			$this->heure = new DateNF();
		return $this->heure->frFormatHeure();
	}
}
?>