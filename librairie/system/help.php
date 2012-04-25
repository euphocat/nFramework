<?php
class Help{
		
	/**
	 * Facilite la redirection
	 *
	 * @param string $module
	 * @param string $action
	 * @param array $options
	 */
	public static function redirect($module="index",$action=null,$options=null){
		$location = ADRESSE.$module;
				
		if(!is_null($action)) $location .= '/'.$action;
		if(!is_null($options)){
			if(is_array($options)){			
				foreach ($options as $key => $value) {
					$location .= "/$key:$value";
				}
			}elseif(is_string($options)){
				$location .= "/$options";
			}
		}		
		header("location:".$location);
		exit;
	}	
	/**
	 * @todo virer MODEL et ACTION
	 * 
	 * Pagination
	 * 
	 * Affichage de la pagination en fonction des données
	 * passées par l'appel dans la vue.
	 * 
	 * utilisation v3 : pour la pagination des pages avec options,
	 * il faut transmettre les options à la fonction de la même manière
	 * que l'affichage dans la querystring.
	 * 
	 * Exemple : $options = 'monoption:15'
	 *
	 * @param int $nbTotal
	 * @param int $nbParPage
	 * @param int $pageEnCours
	 * @return string
	 */
	public static function pagination($nbTotal = 20, $nbParPage = 20, $pageEnCours = 1, $options = null){
		if($nbTotal == 0 or $nbParPage == 0) return '1';
		$nbPages = ceil($nbTotal / $nbParPage);
		
		
		$toReturn = self::pluriel('Page',($nbTotal/$nbParPage)).' ';
		
		if(!is_null($options))
			$options = $options.'/';
			
		if($pageEnCours > 1)
			$toReturn .='<a href="'.ADRESSE.MODEL.'/'.ACTION.'/'.$options.'page:'.($pageEnCours-1).'">précédente</a> ';
				
		for($i=1; $i<$nbPages+1;$i++){
			if($i != $pageEnCours){
				$toReturn .='<a href="'.ADRESSE.MODEL.'/'.ACTION.'/'.$options.'page:'.$i.'">'. $i.'</a> ';
			}else{
				$toReturn .= $i.' ';
			}
		}
		if($pageEnCours < $nbPages && $nbPages > 1)
			$toReturn .='<a href="'.ADRESSE.MODEL.'/'.ACTION.'/'.$options.'page:'.($pageEnCours+1).'">suivante</a> ';
			
		return $toReturn;
	}
	
	/**
	 * @todo nettoyer cette fonction => enlever les MODEL et ACTION, virer les caractères bizarre de flèche
	 * 
	 * paginate($url, $param, $total, $current [, $adj]) appelée à chaque affichage de la pagination
	 * @param string $url - URL ou nom de la page appelant la fonction, ex: 'index.php' ou 'http://example.com/'
	 * @param string $param - paramètre à ajouter à l'URL, ex: '?page=' ou '&amp;p='
	 * @param int $total - nombre total de pages
	 * @param int $current - numéro de la page courante
	 * @param int $adj (facultatif) - nombre de numéros de chaque côté du numéro de la page courante (défaut : 3)
	 * @return string $pagination
	 */
	public static function paginate($requete, $nbTotal = 20, $nbParPage = 20, $current, $options = null, $adj=3){
		
		$modele = $requete['controller'];
		$action = (is_null($requete['action'])) ? 'index' : $requete['action'];
		
				
		$url 	= ADRESSE."$modele/$action/$options";
		$param 	= "page:"; 
		$total 	= ceil($nbTotal / $nbParPage);
		$adj	= 3;
		
		/* Déclaration des variables */
		$prev 	= $current - 1; // numéro de la page précédente
		$next 	= $current + 1; // numéro de la page suivante
		$n2l 	= $total - 1; // numéro de l'avant-dernière page (n2l = next to last)
	
		/* Initialisation : s'il n'y a pas au moins deux pages, l'affichage reste vide */
		$pagination = '';
	
		/* Sinon ... */
		if ($total > 1){
			/* Concaténation du <div> d'ouverture à $pagination */
			$pagination .= "<div class=\"pagination\">\n";
	
			/* ////////// Début affichage du bouton [précédent] ////////// */
			if ($current == 2) // la page courante est la 2, le bouton renvoit donc sur la page 1, remarquez qu'il est inutile de mettre ?p=1
				$pagination .= "<a href=\"{$url}\">◄</a>";
			elseif ($current > 2) // la page courante est supérieure à 2, le bouton renvoit sur la page dont le numéro est immédiatement inférieur
				$pagination .= "<a href=\"{$url}{$param}{$prev}\">◄</a>";
			else // dans tous les autres, cas la page est 1 : désactivation du bouton [précédent]
				$pagination .= '<span class="inactive">◄</span>';
			/* Fin affichage du bouton [précédent] */
	
			/* ///////////////
			Début affichage des pages, l'exemple reprend le cas de 3 numéros de pages adjacents (par défaut) de chaque côté du numéro courant
			- CAS 1 : il y a au plus 12 pages, insuffisant pour faire une troncature
			- CAS 2 : il y a au moins 13 pages, on effectue la troncature pour afficher 11 numéros de pages au total
			/////////////// */
	
			/* CAS 1 */
			if ($total < 7 + ($adj * 2)){
				/* Ajout de la page 1 : on la traite en dehors de la boucle pour n'avoir que index.php au lieu de index.php?p=1 et ainsi éviter le duplicate content */
				$pagination .= ($current == 1) ? '<span class="active">1</span>' : "<a href=\"{$url}\">1</a>"; 
	
				/* Pour les pages restantes on utilise une boucle for */
				for ($i = 2; $i<=$total; $i++){
					if ($i == $current) // Le numéro de la page courante est mis en évidence (cf fichier CSS)
						$pagination .= "<span class=\"active\">{$i}</span>";
					else // Les autres sont affichés normalement
						$pagination .= "<a href=\"{$url}{$param}{$i}\">{$i}</a>";
				}
			}	
			/* CAS 2 : au moins 13 pages, troncature */
			else{
				/*
				Troncature 1 : on se situe dans la partie proche des premières pages, on tronque donc la fin de la pagination.
				l'affichage sera de neuf numéros de pages à gauche ... deux à droite (cf figure 1)
				*/
				if ($current < 2 + ($adj * 2)){
					/* Affichage du numéro de page 1 */
					$pagination .= ($current == 1) ? "<span class=\"active\">1</span>" : "<a href=\"{$url}\">1</a>";
	
					/* puis des huit autres suivants */
					for ($i = 2; $i < 4 + ($adj * 2); $i++){
					if ($i == $current)
						$pagination .= "<span class=\"active\">{$i}</span>";
					else
						$pagination .= "<a href=\"{$url}{$param}{$i}\">{$i}</a>";
					}
	
					/* ... pour marquer la troncature */
					$pagination .= ' ... ';
	
					/* et enfin les deux derniers numéros */
					$pagination .= "<a href=\"{$url}{$param}{$n2l}\">{$n2l}</a>";
					$pagination .= "<a href=\"{$url}{$param}{$total}\">{$total}</a>";
				}
	
				/*
				Troncature 2 : on se situe dans la partie centrale de notre pagination, on tronque donc le début et la fin de la pagination.
				l'affichage sera deux numéros de pages à gauche ... sept au centre ... deux à droite (cf figure 2)
				*/
				elseif ( (($adj * 2) + 1 < $current) && ($current < $total - ($adj * 2)) )
				{
					/* Affichage des numéros 1 et 2 */
					$pagination .= "<a href=\"{$url}\">1</a>";
					$pagination .= "<a href=\"{$url}{$param}2\">2</a>";
	
					$pagination .= ' ... ';
	
					/* les septs du milieu : les trois précédents la page courante, la page courante, puis les trois lui succédant */
					for ($i = $current - $adj; $i <= $current + $adj; $i++)
					{
						if ($i == $current)
						$pagination .= "<span class=\"active\">{$i}</span>";
						else
						$pagination .= "<a href=\"{$url}{$param}{$i}\">{$i}</a>";
					}
	
					$pagination .= ' ... ';
	
					/* et les deux derniers numéros */
					$pagination .= "<a href=\"{$url}{$param}{$n2l}\">{$n2l}</a>";
					$pagination .= "<a href=\"{$url}{$param}{$total}\">{$total}</a>";
				}
	
				/*
				Troncature 3 : on se situe dans la partie de droite, on tronque donc le début de la pagination.
				l'affichage sera deux numéros de pages à gauche ... neuf à droite (cf figure 3)
				*/
				else
				{
					/* Affichage des numéros 1 et 2 */
					$pagination .= "<a href=\"{$url}\">1</a>";
					$pagination .= "<a href=\"{$url}{$param}2\">2</a>";
	
					$pagination .= ' ... ';
	
					/* puis des neufs dernières */
					for ($i = $total - (2 + ($adj * 2)); $i <= $total; $i++)
					{
						if ($i == $current)
							$pagination .= "<span class=\"active\">{$i}</span>";
						else
							$pagination .= "<a href=\"{$url}{$param}{$i}\">{$i}</a>";
					}
				}
			}
			/* Fin affichage des pages */
	
			/* ////////// Début affichage du bouton [suivant] ////////// */
			if ($current == $total)
				$pagination .= "<span class=\"inactive\">►</span>\n";
			else
				$pagination .= "<a href=\"{$url}{$param}{$next}\">►</a>\n";
			/* Fin affichage du bouton [suivant] */
	
			/* </div> de fermeture */
			$pagination .= "</div>\n";
		}
	
		/* Fin de la fonction, renvoi de $pagination au programme */
		return ($pagination);
	}

	
	/**
	 * Même utilisation que sa petite sœur mais dans un contexte Ajax
	 * 
	 * @param int $nbTotal
	 * @param int $nbParPage
	 * @param int $pageEnCours
	 * @param string $options
	 * @return string
	 */
	public static function paginationAjax($nbTotal = 20, $nbParPage = 20, $pageEnCours = 1, $funcName, $options = null){
		if($nbTotal == 0 or $nbParPage ==0) return '1';
		$nbPages = ceil($nbTotal / $nbParPage);
		
		$toReturn = self::pluriel('Page',($nbTotal/$nbParPage)).' ';
		
		for($i=1; $i<$nbPages+1;$i++){
			if($i != $pageEnCours){
				if(!is_null($options))
					$options = $options.'/';
					
				//$toReturn .='<a href="'.$i.'">'. $i.'</a> ';
				$toReturn .= "<a href='javascript:$funcName($i)'>$i</a> ";
			}else{
				$toReturn .= $i.' ';
			}
		}
		return $toReturn;
	}
	/**
	 * @deprecated
	 * 
	 * Detecte si un lien est actif sur la page
	 * 
	 * @param string $texte
	 * @param string $modele
	 * @param string $action
	 *
	 * @return string
	 */
	public static function lienDetecteurs($texte,$modele = null,$action = null) {
		$link = ADRESSE;
		if(!is_null($modele)){ 
			$link.= $modele;
		}else{
			$modele ='index';
		}
		if(!is_null($action)){ 
			$link.= "/".$action;
		}else{
			$action ='';
		}
			
		if(MODEL == $modele){
			return "<a href='".$link."' class='actif'>".$texte."</a>";
		}else{
			return "<a href='".$link."'>".$texte."</a>";
		}
	}
	/**
	 * Tronque une chaine de caractères en fonction d'une longueur définie
	 * 
	 * @param string $chaine
	 * @param int $longueur
	 *
	 * @return string
	 */
	public static function tronque($chaine, $longueur = 120){
		if (empty ($chaine)){
			return "";
		}elseif (strlen ($chaine) < $longueur){
			return $chaine;
		}elseif (preg_match ("/(.{1,$longueur})\s./ms", $chaine, $match)){
			return $match [1] . " [...]";
		}else{
			return substr ($chaine, 0, $longueur) . " [...]";
		}
	}
	/**
	 * Converti n'importe quelle chaine pour être passé dans une URL
	 * 
	 * @param string $text
	 * @param string $from_enc
	 *
	 * @return string
	 */
	public static function urlize($text,$from_enc='UTF-8') {
		$text = mb_convert_encoding($text,'HTML-ENTITIES',$from_enc);
		
		$test = trim($text);
		
		//On vire les accents
		$text = preg_replace( array('/ß/','/&(..)lig;/', '/&([aouAOU])uml;/','/&(.)[^;]*;/'), 
								array('ss',"$1","$1".'e',"$1"),  
								$text);
		
		//on vire tout ce qui n'est pas alphanumérique
		
		$out_text = preg_replace("[^a-z0-9/[[:space:]]/]",'',$text);
		$out_text = str_replace("/","-",$out_text);
		$out_text = str_replace("&","",$out_text);
		$out_text = str_replace(".","",$out_text);
		$out_text = str_replace("'","",$out_text);
		$out_text = str_replace(":","",$out_text);
		$out_text = str_replace('"',"",$out_text);
		$out_text = str_replace("#","-",$out_text);
		$out_text = str_replace("-","",$out_text);
		$out_text = str_replace("?","",$out_text);
		$out_text = str_replace("%","pourcent",$out_text);
		
		//traitement des espaces
		$out_text = preg_replace('/\s{1,}/', '-', $out_text);
		
		while(substr($out_text,-1)=='-')
			$out_text = substr($out_text,0,-1);
				
		//on renvoie la chaîne transformée
		return strtolower($out_text);
	}
	/**
	 * Callback 
	 * 
	 * Convertit les chaines ISO-8851-15 en UTF-8
	 *
	 */
	public static function toUTF8(&$item){
		$item = iconv("ISO-8859-15","UTF-8",$item);
	}
	
	/**
	 * Détermine si un mot doit être mis au pluriel ou non
	 * et renvoie le mot mis au pluriel ou pas en fonction
	 * du $nombre passé
	 * 
	 * @param string $motSingulier
	 * @param int $nombre
	 * @param char $lettrePluriel
	 * @param string $motPluriel
	 * @return string
	 */
	public static function pluriel($motSingulier, $nombre, $lettrePluriel = 's', $motPluriel=null) {
		if($nombre>1){
			if(is_null($motPluriel)){
				return $motSingulier.$lettrePluriel;
			}else{
				return $motPluriel;
			}	
		}else{
			return $motSingulier;
		}
	}
	/**
	 * Supprimer les entrées vides d'un tableau
	 * @return array
	 */
	public static function nettoyerTableau(array $tab) {
		$toReturn = array();
		foreach ($tab as $key => $value) 
			if($value != "" && !is_null($value))
				$toReturn[$key] = $value;
		return $toReturn;
	}	
	/**
	 * Nettoie les RS et autres tableaux en les convertissant en
	 * UTF- 8
	 * @param $tableau
	 * @return array
	 */
	public static function arrayToUFT8(array $tableau) {
		array_walk_recursive($tableau,array('self','arrayToUTF8'));
		return $tableau;
	}
	/**
	 * callback de la fonction self::arrayToUFT8
	 * @param &$item
	 * @param $key
	 */
	private static function arrayToUTF8(&$item, $key) {
		$item = iconv('iso-8859-1','utf-8',$item);
	}
		
}

?>