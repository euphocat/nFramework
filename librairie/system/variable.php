<?php
Class Variable{
	
	private $name 		= null;
	private $type 		= null;
	private $label		= null;
	/**
	 * @var bool
	 */
	private $required 	= null;
	
	public function __construct($name){
		$this->name = $name;	
	}
	/**
	 * Défini le type de la variable en question
	 * 
	 * @param string $type
	 * @return Variable
	 */
	public function setType($type){
		$this->type = $type;
		return $this;
	}
	/**
	 * Défini si la variable est requise
	 * 
	 * @param bool $value
	 */
	public function setRequired($value) {
		
		if(!is_bool($value))
			new VariableException("Booléen attendu pour la propriété Required");
		
		$this->required = $value;
		return $this;
	}
}
?>