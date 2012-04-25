<?php
final class View{
	
	/**
	 * Liste des bibliothèques javascript additionnelles
	 * @var array
	 */
	public $scriptsJS = array();
	/**
	 * Liste de css additionnels
	 *
	 * @var array 
	 */
	public $css = array();
	/**
	 * Tableau de variables pouvant être affichés dans le template
	 *
	 * @var array = null;
	 */
	public $templateData = null;
	
	/**
	 * Prise en compte dans les stats
	 *
	 * @var boolean
	 */
	public $stats = false;
	/**
	 * interdire la mise en cache pour IE
	 * 
	 * @var boolean
	 */
	public $ieNoFuckingCache = false;
	
	private $data;	
	private $templateName = null;
	private $template;
	private $contenu = null;
	private $nom;
	private $notifications = array();
	
	public function __construct($nom, $data = null, $cache = null){
		$this->nom 		= $nom;
		$this->data 	= $data;	
		$this->cache	= $cache; 
	}
	public function afficher() 
	{
		$this->template = $this->chargerTemplate();
		
		if(is_null($this->contenu)) 
			$this->contenu = $this->chargerContenu();
		
		// si un objet cache est passé à la vue, on enregistre
		// le contenu généré pour être réaffiché plus tard	
		if(!is_null($this->cache))
			$this->cache->enregistrer($this->contenu);
		
		$balises = array('body','script','css','notification','stats','IEnoFuckingCache');
		
		// mise en forme des balises
		array_walk($balises,function(&$item,$key){
			$item = "<!--[[$item]]-->";
		});
		
		$remplacement 	= 
			array(
				$this->contenu,
				$this->charger("scriptsJS"),
				$this->charger("css"),
				$this->getNotifications(),
				$this->getStatCode(),
				$this->getIeNoFuckingCache()
			);
			
		// affichage
		echo str_replace($balises,$remplacement,$this->template);
	}
	/**
	 * Charge le template et ses données
	 * 
	 * @return string
	 */
	private function chargerTemplate()
	{
		if(!is_null($this->templateData))
		{
			// stripslashes de manière récursive à tous les élèments du tableau de type non-numérique
			array_walk_recursive($this->templateData,array($this,"stripslashesPerso"));
			
			// création des variables pour le template
			foreach($this->templateData as $key=>$variable)
				$$key = $variable;			
			
		}
		ob_start();
			require(LIBRAIRIE."templates/{$this->getTemplate()}.php");
			$template = ob_get_contents();
		ob_clean();
		
		return $template;
	}
	/**
	 * Charge les data dans la vue et génère le contenu;
	 * fonction pouvant être utilisée pour charger des données
	 * de cache
	 * 
	 * @param String $donnees
	 * @return String
	 */
	public function chargerContenu()
	{
		if(!is_null($this->data))
		{
			// stripslashes de manière récursive à tous les éléments du tableau de type non-numérique
			array_walk_recursive($this->data,array($this,"stripslashesPerso"));
			
			foreach($this->data as $key=>$variable)
				$$key = $variable;			
		}
		ob_start();
			
			if(is_file(LIBRAIRIE."views/{$this->nom}.php"))
				require(LIBRAIRIE."views/{$this->nom}.php");
			else 
				throw new ViewException("Vue {$this->nom} introuvable");
			
			$contenu = ob_get_contents();
		ob_clean();
		return $contenu;
	}
	/**
	 * @todo virer la constante 
	 * 
	 * @return string $template
	 */
	public function getTemplate()
	{
		if(is_null($this->template))
			$this->template = TEMPLATE;
						
		return $this->template;
	}
	/**
	 * Modifie le template par défaut
	 * 
	 * @param string $template
	 * @throws ViewException
	 */
	public function setTemplate($template)
	{
		if(is_file(LIBRAIRIE."templates/{$template}.php"))
			$this->template = $template;
		else 
			throw new ViewException("Template introuvable");
	}
	/**
	 * Outrepasser le chargement du contenu 
	 * pour afficher des pages statiques ou
	 * des trucs genre je sais pas trop...
	 * 
	 * @param string $contenu
	 */
	public function setContenu($contenu) {
		$this->contenu = $contenu;
	}
	/**
	 * Ajout de javascripts
	 * 
	 * @param array|string $script
	 */
	public function addScriptJS($script) {
		if(is_array($script)){
			foreach ($script as $s)
				$this->scriptsJS[] = $this->makeScriptJS($s);
		}else{
			$this->scriptsJS[] = $this->makeScriptJS($script);
		}
	}
	/**
	 * Ajout de feuilles de styles
	 * 
	 * @param array|string $css
	 */
	public function addCSS($css) {
		if(is_array($css))
			foreach ($css as $c)
				$this->css[] = "<link href='/css/$c.css' rel='stylesheet' type='text/css' />";
		else
			$this->css[] = "<link href='/css/$css.css' rel='stylesheet' type='text/css' />";
	}
	/**
	 * Détermine si le script est interne ou externe et le formate
	 * 
	 * @param string $script
	 * @return string
	 */
	private function makeScriptJS($script) {
		
		if(substr($script,0,7) == "http://")
			return "<script type='text/javascript' src='$script'></script>\r\n";
		else
			return "<script type='text/javascript' src='/scripts/$script.js'></script>\r\n";		

	}
	private function charger($scripts){
		$toReturn = '';
		
		if(!is_null($this->$scripts))
			foreach($this->$scripts as $script)
				$toReturn .="$script\r\n";
				
		return $toReturn;
	}
	public function setNotification($notifications) {
		$this->notifications = $notifications;
	}	
	public function getNotifications() 
	{
		$toReturn .= "<div id='notification'>\r\n";
		
		if(count($this->notifications)>0)
		{
			foreach ($this->notifications as $key=>$notifications)
			{
				$toReturn .= "\t<ul id='notif_$key'>\r\n";
				foreach ($notifications as $notification)
					$toReturn .= "\t\t<li>$notification</li>\r\n";
				$toReturn .= "\t</ul>\r\n";
			}
		}
		$toReturn .= "</div>\r\n";
		
		return $toReturn;
	}
	public function addData($key, $value){
		$this->data[$key] = $value;
	}
	private function getStatCode() {
		return @file_get_contents(LIBRAIRIE."/templates/_tracker.php");
	}
	private function getIeNoFuckingCache() {
		if($this->ieNoFuckingCache)
			return '<meta http-equiv="PRAGMA" content="NO-CACHE" />';
	}
	/**
	 * Callback utilisé dans la génération du template et du corps
	 * @param string $item
	 * @param mixed $key
	 */
	private function stripslashesPerso(&$item, $key){	
		if(!is_numeric($item) && is_string($item)) 
			$item = @stripslashes($item);
	}
}
?>