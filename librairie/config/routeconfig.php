<?php 
/**
 * Définition des routes personnalisées
 * 
 * @author nicolas
 *
 */
Class RouteConfig extends \Routes{

	/**
	 * Créer ici les routes personnalisées
	 * 
	 * @example route simple
	 * 		$this->ajouterRoute("faq", "index", "faq", "questions-frequentes");
	 * 
	 * @example route avec options dynamiques
	 * 		$this->ajouterRoute("test", "index", "testroute", "superbe/route/magnifique/[options]");
	 * 
	 * @see Routes::config()
	 */
	public function config(){
		$this->ajouterRoute("faq", "index", "faq", "questions-frequentes");
		$this->ajouterRoute("test", "index", "testroute", "superbe/route/magnifique/[options]");
	}
}
?>