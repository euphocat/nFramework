<?php
/**
 * Class système de génération de modèles et d'admin
 * 
 * @package system
 * @author euphocat
 *
 */
Class Generateur{

	/**
	 * Variable faisant le lien avec la classe modèle pour pouvoir 
	 * communiquer avec la bdd
	 * 
	 * @var ModeleGenerique 
	 */
	private $modeleGenerique = null;
	
	public function __construct() {
		$this->modeleGenerique = new ModeleGenerique();
	}
	
	public function genererModele($table, $db = null) {
		
		//obtenir les champs de la table + leur type
		$champs = $this->getChamps($table, $db);
		
		return $champs;

	}
	private function genererModeleSingulier($champs) {
		;
	}
	private function genererModelePluriel($champs) {
		;
	}
	private function getChamps($table, $db = null) {
		
		$champs = array();
		
		if(!is_null($db)){
			$this->modeleGenerique->getCnx()->exec("USE $db;");
		}
		
		$sql = sprintf("DESCRIBE %s", $table);
		$rs = $this->modeleGenerique->preparedSelect($sql);
				
		if($rs[0]['Field']	!= "id") 		throw new \Exception("L'identifiant ne s'appelle 'id'");
		if($rs[0]['Type']  	!= "int(11)") 	throw new \Exception("L'identifiant n'est pas numérique");
		
		 
		foreach ($rs as $key=>$ligne){
			
			// découpe des varchar et recherche de la taille
			if(strstr($ligne["Type"],"varchar")){
				preg_match("@\((.*?)\)@ui", $ligne["Type"], $matches);
				$rs[$key]["Size"] 	= (int) $matches["1"];
				$rs[$key]["Type"] 	= "varchar";
			}
			// création des labels
			$rs[$key]["Label"] 	= $this->getLabel($ligne["Field"]);
		}
					
		return $rs;
	}
	private function getLabel($field) {
		return str_replace("_"," ",ucfirst($field));
	}
}
?>