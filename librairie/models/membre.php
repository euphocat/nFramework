<?php
Class Membre extends \Singulier{

	public $id					= null;
	public $login				= null;
	public $su					= null;
	public $mdp					= null;
	public $nom					= null;
	public $prenom				= null;
	public $cacheridentite		= null;
	public $civilite			= null;
	public $societe				= null;
	public $adresse				= null;
	public $codepostal			= null;
	public $ville				= null;
	public $pays				= null;
	public $email				= null;
	public $telephone			= null;
	public $datenaissance		= null;
	public $newsletters			= null;
	public $pub					= null;
	public $avatar				= null;
	public $description			= null;
	public $materiel			= null;
	public $statut				= null;
	public $commentaires		= null;
	public $ip					= null;
	/**
	 * @var \DateNF
	 */
	public $dateheureconnexion	= null;
	public $lettres				= null;
	/**
	 * @var \DateNF
	 */
	public $dateajout			= null;
	/**
	 * @var \DateNF
	 */
	public $datemaj				= null;

	public function __construct($ligne){
		parent::__construct($ligne);
		$this->dateheureconnexion = $this->transtyperDateNF($this->dateheureconnexion);
		$this->dateajout = $this->transtyperDateNF($this->dateajout);
		$this->datemaj = $this->transtyperDateNF($this->datemaj);
	}
}

Class Membres extends \Model{

	private $liste = array();
	private $sql = null;

	public function getListe(){
		$sql 	= parent::sqlCraft($this->getSql());
		$rs 	= parent::preparedSelect($sql);

		foreach($rs as $ligne){
			$this->liste[] = new Membre($ligne);
		}
		return $this->liste;
	}
	public function nbResultats(){
		$sql = $this->getSql();
		return parent::compterResultats($sql);
	}
	public static function getMembre($id){
		return new Membre(parent::getLigne($id,'membre'));
	}
	private function getSql(){
		if(is_null($this->sql)){
			$this->sql = "SELECT * FROM membre";
		}
		return $this->sql;
	}

}
?>
