<?php
/**
 * Classe d'authentification générique et paramétrable
 * 
 * @package system
 * @version 1.0
 * @author Nico
 *
 */
Class Auth{
	
	private $cnx 		= null;
	/**
	 * nom de l'auth
	 * @var string
	 */
	private $type 		= null;
	/**
	 * champs de la bdd pour créer l'objet
	 * @var array()
	 */
	private $champs		= null;

	/**
	 * nom de la table
	 * @var unknown_type
	 */
	public $table		= null;
	public $mdpNom		= null;
	public $loginNom 	= null;
	public $ttlCookie	= 15;
	
	/**
	 * 
	 * @param string $type
	 * @param array $champs
	 * @param string $typeObjet
	 * @return Auth
	 */
	public function __construct($type, array $champs, $mdpNom = 'mdp', $loginNom = 'login') {
		
		$this->cnx 			= Connexion::getInstance();
		$this->type 		= $type;
		
		$this->champs 		= $champs;
		
		$this->mdpNom 		= $mdpNom;
		$this->loginNom 	= $loginNom;
				
	}
	/**
	 * Vérification simple si un utilisateur est connecté.
	 * 
	 * @return bool
	 */
	public function isLogged() {
		
		if(isset($_SESSION[$this->type]))
			return true;
				
		if(isset($_COOKIE[$this->type])){
			$cookie = unserialize(stripslashes($_COOKIE[$this->type]));
			
			if($this->login($cookie[0],$cookie[1]))
				return true;
		}
		
		return false;
	}
	/**
	 * Vérifie la validité une connexion.
	 * Attention, le mdp doit être encodé en md5 !
	 * 
	 * @param string $login
	 * @param string md5($mdp.SALT_KEY)
	 * @param bool $setCookie
	 * @return bool
	 */
	public function login($login, $mdp, $setCookie = true) {
		
		// initialisation
		$champs 	= implode(',',$this->champs);
		$table 		= $this->getTable();
		
		// communication avec la BDD
		$sql = "SELECT $champs FROM $table WHERE $this->loginNom = :$this->loginNom LIMIT 1";	
		$stm = $this->cnx->prepare($sql,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$stm->execute(array(":$this->loginNom"=>$login));
		$rs = $stm->fetch(PDO::FETCH_ASSOC);
				
		// si une ligne est retournée : ok, sinon : ko
		if(is_array($rs) && count($rs)>0){
					
			// vérifie si le mdp est correct
			if ($mdp != md5($rs[$this->mdpNom].SALT_KEY))
				return false;
			
			// encodage du mdp pour stockage
			$rs[$this->mdpNom] = md5($rs[$this->mdpNom].SALT_KEY);
						
			// mise en session
			$_SESSION[$this->type] = $rs;
			
			// enregistrement d'un cookie
			if ($setCookie){
				
				$value = serialize(array($rs['login'],$rs[$this->mdpNom]));
				
				// cookie en place pour 15 jours par défaut
				setcookie($this->type,$value,time()+(3600*24*$this->ttlCookie),'/');
			} 
			return true;
		}else{
			return false;
		}
	}
	/**
	 * Si aucun nom de table n'est précisée,
	 * le nom de l'authentification est pris par
	 * défaut.
	 * 
	 * @return string 
	 */
	public function getTable() {
		if(is_null($this->table)){
			$this->table = $this->type;
		}
		return $this->table;
	}
	/**
	 * Se déconnecter
	 * 
	 */
	public static function deconnexion($type) {
		session_unset();
		session_destroy();
		setcookie($type,$value,time()+(3600*24*60),'/');
		session_start();
	}
}
?>