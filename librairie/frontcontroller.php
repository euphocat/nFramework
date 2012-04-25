<?php
session_cache_limiter ('private_no_expire, must-revalidate');
session_start();

// cacher les notices pour OVH
//ini_set('display_errors','Off');

// constantes de configuration 
include_once 'config.php';

// variable locale de temps
setlocale(LC_TIME,'fr','fr_FR.UTF8');

/**
 * 
 * @name FrontController
 * @version 2.2 - 11/07/2011
 * 
 * @author Nicolas Baptiste - nicolas [dot] baptiste [at] gmail [dot] com
 * 
 */
class FrontController{

	private static $instance;
	private $auth;
	private $requete = null;
	/**
	 * @var Routes
	 */
	private $routes = null;
	
	public $debug 	= false;
	
	/**
	 * Initialisation de la requête
	 * 
	 * @return FrontController
	 */
	private function __construct()
	{
		$this->requete["controller"] 	= 'index';
		$this->requete["action"] 		= null;
		$this->requete["options"] 		= array();
		
		$this->routes = new RouteConfig();
		
		set_exception_handler('FrontController::exception_handler');
	}
	/**
	 * Empêchement du clonage pour le singleton
	 */
	private function __clone(){}
	
	/**
	 * Vérifie si la classe est déjà instanciée, retourne la référence de l'instanciation si oui
	 * créé une nouvelle instance le cas échéant.
	 *
	 * @return FrontController
	 */
	public static function getInstance()
	{
		if(is_null(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;		
	}
	/**
	 * Appelle le parsage d'url, puis lançant le
	 * le controlleur et l'action demandé par la requete
	 */
	public function dispatch()
	{		
		if($this->routage())
			$this->requete = $this->parseRoutage();
		else
			$this->requete = $this->parseURL();
							
		$this->launch();
	
		Connexion::fermerConnexion();
	}
	/**
	 * Formate la requete passée en URL et la retourne dans un tableau
	 *
	 * @return array(string, string, array(string))
	 */
	public function parseURL()
	{		
		if(isset($_GET["url"]) && !empty($_GET['url']))
		{
			$raw = explode("/",$_GET["url"]);
			
			$requete["controller"] 	= $raw[0];
			$requete["action"] 		= $raw[1];
			$requete["options"]		= array();
			
			for($i=2;$i<count($raw);$i++)
			{
				$requete["options"][] = $raw[$i];
			}

			return $requete;	
		}
		else
		{
			return $this->requete;
		}
	}	
	/**
	 * Execute les instructions passées en url et instanciation
	 * du sous-contrôleur.
	 */
	public function launch()
	{			
		// instanciation du controleur demandé
		$ctrl = new $this->requete["controller"]($this->requete["options"]);
		
		if($this->debug)
			$ctrl->debug = true;
		
		$ctrl->setRequete($this->requete);
		
		if(!is_null($this->requete["action"]))
		{
			$action = (string) $this->requete['action'];			
			$ctrl->$action();
		}
		else
		{
			$ctrl->index();
		}
	}
	/**
	 * @todo mettre en forme tout ça
	 */
	public static function exception_handler(\Exception $exception){
		if(DEBUG){ 
			var_dump($exception);
		}else{
			$ctrl = new Index(array());
			$ctrl->affichageMessageErreur($exception);
		}
	}
	/**
	 * @todo écrire cette fonction
	 * Cherche si l'URL fait partie un routage défini
	 * 
	 * @return bool
	 */
	private function routage() {
		return $this->routes->parseRoute($_GET['url']);
	}
	/**
	 * Retourne une requête, si un routage a été trouvé
	 *
	 * @return array
	 */
	private function parseRoutage() {
		return $this->routes->getRoute()->getRequete();
	}
}
/**
 * Chargement automatique des classes
 * 
 * @param string $name
 */
function __autoload($name){
	
	$name = strtolower($name);
	
	if(strstr($name,"\\") !== FALSE){
		$name = str_replace("\\", "/", $name);
		
		if(is_file(LIBRAIRIE.$name.".php"))
			include_once $name.".php";
		else{
			if(strstr($name,"modele") !== FALSE){
				
				if(substr($name, -1) == "s")
					$name = substr($name, 0, -1);
				
				list($modele,$name) = explode("/", $name);
				include_once "models/".$name.".php";
			}else			
				throw new ControllerException("Contrôleur $name introuvable");	
		}	
	}
	
	$dossiers = 
		array(
			"system",
			"system/exceptions",
			"controllers",
			"controllers/admin",
			"models",
			"config"
		);
	
	foreach ($dossiers as $dossier){
		if(is_file(LIBRAIRIE."{$dossier}/{$name}.php"))
			include_once "{$dossier}/{$name}.php";
	}
}
?>