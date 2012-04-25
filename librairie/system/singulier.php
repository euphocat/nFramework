<?php
abstract class Singulier{
	
	/**
	 * Permet de transformer un tableau en variable de l'objet
	 */
	public function __construct(array $ligne=null) {
		if(!is_null($ligne))
			foreach ($ligne as $key => $value)
				$this->$key = $value;
	}	
	/**
	 * Transforme les chaines DATETIME et autres en \DateNF 
	 *
	 * @param string $var
	 * @return null|\DateNF
	 */
	public function transtyperDateNF($var) {
		
		if(!is_null($var) && !empty($var) && is_string($var)){
			$var = new \DateNF($var);
		}
		return $var;	
	}
}
?>