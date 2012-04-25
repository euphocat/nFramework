<?php
/**
 * 
 * Gestion des routes 
 * @author euphocat
 *
 */
Abstract Class Routes {
	
	private $routes = array();
	private $route = null;	
	
	public function __construct(){
		// prise en compte de la configuration utilisateur
		$this->config();
	}
	/**
	 * Définir les routes dans cette fonction, dans une classe héritée
	 * 
	 * @mustoverride
	 */
	abstract public function config();
	/**
	 * Ajouter une route dans la config
	 * 
	 * @param string $identifiant
	 * @param string $modele
	 * @param string $action
	 * @param string $url
	 */
	public function ajouterRoute($identifiant, $modele, $action, $url) {
		$this->routes[] = new Route($identifiant, $modele, $action, $url); 
	}
	/**
	 * @return array $routes
	 */
	public function getRoutes() {
		return $this->routes;
	}
	/**
	 * 
	 * Parcours des routes définies par l'utilisateur pour trouver
	 * un routage
	 * 
	 * @param string $url
	 *
	 * @return bool
	 */
	public function parseRoute($url) {
				
		if (count($this->routes) == 0)
			return false;
			
		foreach ($this->routes as $route){
			$route instanceof Route;
			
			// vérification de passage d'options ou non
			if(preg_match("/(.*?)\/\[options\]/u", $route->getUrl(),$matches) == 1){
				$base 		= str_replace("/", "\/", $matches[1]);
				$patterne 	= "/^({$base})\/?(.*?)\/?$/u";
			}else{
				$patterne 	= "/^(".str_replace("/", "\/", $route->getUrl()).")\/?$/u";
			}
			
			// si une route correspond, on retourne TRUE
			if(preg_match($patterne, $url, $matches) == 1){
				
				if($matches[2] != "")
					$route->setOptions($matches[2]);
				
				$this->route = $route;
				return true;
			}
		}
		return false;
	}
	/**
	 * Retourne la route trouvée par la fonction parseRoute($url)
	 *
	 * @return Route
	 */
	public function getRoute() {
		return $this->route;
	}
}
Class Route{
	
	private 
		$identifiant 	= null,
	 	$controller		= null,
	 	$action			= null,
	 	$url			= null,
	 	$options		= null;
	 	 	 	
	public function __construct($identifiant, $controller, $action, $url, $options = null){
	 	$this->identifiant 	= $identifiant;
	 	$this->controller	= $controller;
	 	$this->action 		= $action;
	 	$this->url			= $url;
	 	$this->options		= $options;
	}
	/**
	 * Mise en forme de l'objet Route vers un tableau Requete
	 *
	 * @return array
	 */
	public function getRequete() {
		$requete = array();
		
		$requete["controller"] 	= $this->getController();
		$requete["action"] 		= $this->getAction();
		
		$requete["route"] 		= $this->getIdentifiant();
		
		if(!is_null($this->getOptions()))
			$requete["options"]		= explode("/",$this->getOptions());
		
		return $requete;
	}
	public function getIdentifiant() {
		return $this->identifiant;
	}
	public function getController() {
		return $this->controller;
	}
	public function getAction() {
		return $this->action;
	}
	public function getUrl() {
		return $this->url;
	}
	public function getOptions() {
		return $this->options;
	}
	public function setOptions($options) {
		$this->options = $options;
	}
}
?>