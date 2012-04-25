<?php
/**
 * Classe de gestion de cache
 * 
 * Cette classe récupère le modele, l'action et les options
 * passés par un controlleur, génère un hash en md5 qui 
 * sert de nom de fichier cache.
 * 
 * 
 * @author Nicolas Baptiste
 *
 */
Class Cache{
		
	private $modele;
	private $action;
	private $options;
	
	private $hash = null;
	
	/**
	 * sert à test la présence du cache ou non après instanciation
	 * @var Boulean
	 */
	private $exists = false;
	
	public function __construct($modele, $action, $options){
		$this->modele 	= $modele;
		$this->action 	= $action;
		$this->options	= $options;
		
		$this->exists = $this->isCache();
	}
	
	public function __get($var) {
		return $this->$var;
	}
	
	/**
	 * Enregistre un cache généré par une vue
	 * @return Boolean
	 */	
	public function enregistrer($donnees) {
						
		$handle = fopen($this->getFile(), "w+b");
		
		$tatoo = "<?php /* 
			Modèle 	: $this->modele
			Action 	: $this->action
			Options	: ".serialize($this->options)."
		*/\n?>";
		
		$toReturn = fwrite($handle,$tatoo.$donnees);
				
		if($toReturn === false){
			return $toReturn;
		}else{
			return true;
		}
	}
	
	/**
	 * Retourne un le fichier cache s'il est trouvé
	 * 
	 * @return String 
	 */
	public function recuperer() {
		return file_get_contents($this->getFile());
	}
	
	/**
	 * Supprime un fichier de cache pour qu'il soit
	 * remis à jour par la suite
	 * 
	 * @return Boolean
	 */
	public static function supprimer($modele) {
		
		$dir = RACINE.'site/cache/'.$modele;
	
		if(!$dh = @opendir($dir))
        	return;
    	
    	while (false !== ($obj = readdir($dh))){
    		if($obj == '.' || $obj == '..')
    			continue;

    		if (!@unlink($dir . '/' . $obj))
    			self::unlinkRecursive($dir.'/'.$obj, true);
    	}

    	closedir($dh);
      
	    return; 
	}
	/**
	 * Supression d'un fichier de cache
	 * 
	 * @param string $modele
	 * @param string $fichier
	 */
	public static function suppressionPrecise($modele,$fichier) {
		
		$dir = RACINE."site/cache/$modele/";
        @unlink($dir.$fichier);

	}
	
	/**
	 * Retourne le hash et le valorise s'il n'existe pas
	 * @return string
	 */
	public function getHash() {
		
		//convertion des options en string
		$sOptions = serialize($this->options);
		
		if(is_null($hash))
			$this->hash = md5($this->modele.$this->action.$sOptions);
		
		return $this->hash;
	} 
	
	/**
	 * Vérifie la présence d'un cache en fonction de 
	 * l'empreinte calculée
	 * 
	 * @return Boolean
	 */
	public function isCache() {
		return (is_file($this->getFile()) && UTILISER_CACHE);
	}
	
	/**
	 * Retourne le nom du fichier de cache avec son chemin complet.
	 *  
	 * @return String
	 */
	private function getFile() {
	
		return $this->getDir().'/'.$this->getHash().'.cache';	
	}
	/**
	 * Vérifie l'existance du dossier nommé selon le modèle
	 * et le créé s'il n'existe pas.
	 * 
	 * @return String
	 */
	private function getDir() {
		$racine = RACINE.'site/cache/';
		
		if(!is_dir(RACINE.'site/cache/'.$this->modele))
			mkdir($racine.$this->modele,0777);
		
		return RACINE.'site/cache/'.$this->modele;
	}
	/**
	 * Recursively delete a directory
	 *
	 * @param string $dir Directory name
	 * @param boolean $deleteRootToo Delete specified top-level directory as well
	 */
	private function unlinkRecursive($dir, $deleteRootToo){
	    if(!$dh = @opendir($dir))
	        return;
	    
	    while (false !== ($obj = readdir($dh))){
	        if($obj == '.' || $obj == '..')
	            continue;
	        
	        if (!@unlink($dir . '/' . $obj))
	            self::unlinkRecursive($dir.'/'.$obj, true);
	    }
	
	    closedir($dh);
	   
	    if ($deleteRootToo)
	        @rmdir($dir);
	        
	    return;
	}

}
?>