<?php
/**
 * Version 2
 * Recherche possible sur plusieurs champs avec plusieurs termes différents
 * 
 * @author Nicolas Baptiste
 *
 */
Class RechercheGenerique extends model{

	//vars
	
	protected $table 					= null;
	protected $jointure				= null;

	protected $champsRecherche 		= null;
	
	protected $champsReponse	 		= null;
	protected $motsCherches 			= null;
	protected $motsNonVoulus	 		= null;
	protected $rechercheAdditionnelle = null;
	
	protected $nombreResultats		= null;
	
	protected $requete				= null;
	
	/**
	 * Initialisation de la classe de recherche
	 * @param $sTable
	 * @param $aChampsRecherche
	 * @param $aChampsReponse
	 * @return RechercheGenerique
	 */
	public function __construct($sTable, $aChampsRecherche, $aChampsReponse){
		parent::__construct();
		$this->table 			= $sTable;
		$this->champsRecherche 	= $aChampsRecherche;
		$this->champsReponse 	= $aChampsReponse;
	}
	
	public function setMotsCherchesFromString($sMots) {
		$this->motsCherches = $this->decoupeMots($sMots);
	}
	public function setMotsNonVoulusFromString($sMots) {
		$this->motsNonVoulus = $this->decoupeMots($sMots);
	}
	
	
	/**
	 * @param String $sMots
	 * @return array
	 */
	protected function decoupeMots($sMots) {
		$toReturn 	= null;
		$sMots 		= trim($sMots);
		
		if(empty($sMots))
			return null;
			
		// recherche les expressions entre guillemets
		$pattern = '/"([^"]*)"/i';

		// extraction des termes entres guillemets
		preg_match_all($pattern,$sMots,$matches);	
		
		// suppression des termes entres guillemets
		$sMots = preg_replace($pattern,'',$sMots);
		
		$sMots = trim($sMots);
		
		// découpe de la chaine selon les espaces
		$aMots = preg_split('/\s+/i',$sMots);
		
		// regroupement des expressions entre guillemets et des mots simples 
		$aMots	= array_merge($aMots,$matches[1]);
		
		//supprime les valeurs vides
		foreach ($aMots as $key=>$value)
			if($value=="") unset($aMots[$key]);
		
		return $aMots;
	}
	
	public function doSearch() {
			
		if(is_null($this->requete))
			throw new Exception('Recherche non initialisée');
		
		$sql = parent::sqlCraft($this->requete);
		$rs =  parent::preparedSelect($sql);
				
		return $rs;
	}
	
	
	/**
	 * Création de la requête, execution de la recherche
	 * 
	 * @return ResultSet
	 */
	public function initSearch($wheres = null, $order = null) {
		if(is_null($this->table) || is_null($this->champsRecherche) || is_null($this->champsReponse))
			throw new Exception('Paramètres manquant pour effectuer la recherche');
		
			$sChampsReponse 	= implode(',',$this->champsReponse);
			
			$sql = "SELECT $sChampsReponse FROM $this->table ";
			
			if(!is_null($this->jointure))
				$sql .= $this->createJointure();

			$sql .= " WHERE 1=1 ";			
				
			if(!is_null($this->motsCherches)){
				foreach ($this->champsRecherche as $champs)
					$aChampsRecherche[] =  $champs. ' REGEXP (\''.implode('.*',$this->motsCherches).'\')';
				$sql.= " AND ".implode(' OR ',$aChampsRecherche);
			}	
						
			if(!is_null($this->motsNonVoulus)){
				foreach ($this->champsRecherche as $champs)
					$aChampsNonVoulus[] =  $champs. ' NOT REGEXP (\''.implode('.*',$this->motsNonVoulus).'\')';
				$sql .= " AND (".implode(' OR ',$aChampsNonVoulus).")";
			}
			if(!is_null($this->rechercheAdditionnelle))
				$sql .= $this->rechercheAdditionnelle;
			
			if(!is_null($wheres))
				$sql .= 'AND '.$wheres;
				
			if(!is_null($order))
				$sql .= ' ORDER BY '.$order;
			
			$this->requete = $sql;
	}
	
	public function ajouterRechercheAdditionnelle($aChampCherches,$sMotsCherches) {
		
		$toReturn = "";
		if(trim($sMotsCherches) !=""){ 
			$aMotsCherches = $this->decoupeMots($sMotsCherches);
			
			foreach ($aChampCherches as $champs)
				$aChampsRecherche[] =  $champs. ' REGEXP (\''.implode('.*',$aMotsCherches).'\')';
			
				
			$this->rechercheAdditionnelle .= " AND ".implode(' OR ',$aChampsRecherche);
		}
	}

	
	public function getNombreResultats(){
		
		if(!is_null($this->nombreResultats))
			return $this->nombreResultats;
		
		if(!is_null($this->requete)){
			$sql ="SELECT COUNT(*) AS nb FROM (".$this->requete.") AS total";
			$rs = parent::preparedSelect($sql,array());
			$this->nombreResultats = $rs[0]['nb'];
			return $this->nombreResultats;
		}else{
			throw new Exception('Recherche non initialisée');
		}
	}
	
	/**
	 * Tableau de tableau
	 * array(
	 * 		array(
	 * 			"table" 	=> $table1,
	 * 			"on1"		=> $on1,
	 * 			"on2"		=> $on2
	 * 		)
	 * )
	 * 	 
	 * @param $jointures
	 * @return null
	 */
	public function setJointure($jointures) {
		$this->jointure = $jointures;
	}
	protected function createJointure(){
		$toReturn = null;
		foreach($this->jointure as $join){
			$join = (object) $join;
			$toReturn .= " LEFT JOIN $join->table ON $join->on1 = $join->on2 \n";
		}
		return $toReturn;
	}
	
	public function __sleep(){
		return array(
			'table',
			'jointure',
			'champsRecherche',
			'champsReponse',
			'motsCherches',
			'motsNonVoulus',
			'rechercheAdditionnelle',
			'nombreResultats',
			'requete'
		);
	}
	
}
?>