<?php
/**
 * @class CheckForm
 * 
 * A instancier dans un sous-controlleur, permet de vérfier
 * des données, de supprimer des tags html ainsi que le
 * "pseudo-typage" des helpers.
 *
 *@version 2
 *@todo créer des tests unitaires de tout ça
 */
class CheckForm{
	/**
	 * Langue du site
	 */
	public $lang = "fr";
	/**
	 * Liste des erreurs trouvées dans les champs à vérifier.
	 *
	 * @var array string
	 */
	private $errorList = array();

	/**
	 * Supprime les tags html. 
	 *
	 * @param array &$data
	 */
	public function nettoyage($data,array $cleanData=array()){
		unset($data["envoyer"]);
		unset($data["MAX_FILE_SIZE"]);
		foreach($data as $key=>$value){
			if(!in_array($key,$cleanData)){
				$data[$key] = trim(strip_tags($value));
			}else{
				$data[$key] = trim(strip_tags($value,'<br><i><u><em><p><b><cite><blockquote><strong><div><li><ul><ol><dl><dd><dt><strike><table><tr><th><td>'));
			}
		}
		return $data;
	}
	
	/**
	 * Vérifier un tableau de data en fonction des
	 * instructions passées dans une tableau de checks
	 *
	 * @param array &$data
	 * @param array $checks
	 * @return array string
	 * 
	 * EXEMPLE :
	 * 
	 *  $toCheck = array("email"	=> array('isEmail'),
						"login"	=> array('isNotVoid'),
						"age"	=> array('isNotVoid',
									 	 'inRange'=>array(18,99)
									 	)
						);
			
		$this->errorList = $this->getCheckForm()->verifier($_POST,$toCheck);
	 */
	public function verifier(array &$data, array $checks){
		foreach ($checks as $key=>$values){
			if(isset($values['nom'])){
				$err = $values['nom'];
				unset($checks[$key]['nom']);
			}else{
				$err = strtoupper(substr($key,0,1)).substr($key,1);
			}
			foreach ($values as $skey=>$fn) {
				if($skey === 'inRange'){
					$this->inRange($data[$key],$err,$fn);
				}elseif($skey === 'tailleMin'){
					$this->tailleMini($data[$key],$err,$fn)	;
				}elseif($skey === 'tailleMax'){
					$this->tailleMaxi($data[$key],$err,$fn)	;					
				}else{
					switch ($fn) {
						case 'isEmail':
							$this->isEmail($data[$key],$err);
						break;
						case 'isNum':
						case 'isInt':
							if($this->isInt($data[$key],$err)){
								$data[$key] = (int) $data[$key];
							}
						break;
						case 'isNotVoid':
							$this->isNotVoid($data[$key],$err);
						break;	
						case 'isFloat':
							$value = str_replace(",", ".", $data[$key]);
							if($this->isFloat($value,$err))
								$data[$key] = $value;
						break;	
						case 'isDate':
							$this->isDate($data[$key],$err);
						break;
						case 'isDateFormat';
							$this->isDateFormat($data[$key],$err);
						break;
						case 'alphaNum' :
							$this->onlyAlphanum($data[$key],$err);
						break;
					}
				}
			}
		}
		return $this->errorList;
	}
	
	public function isEmail($email, $key) {
		$res = preg_match("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $email);
		if($res>0){
			return true;
		}else{
			$this->errorList[] = $this->t('email_invalide');
			return false;
		}
		
	}
	public function isInt($value, $key){
		preg_match("@^\d*$@iu",$value,$trouve);
		if(empty($trouve)){
			$this->errorList[] = $this->t("nombre_entier",$key);
			return false;
		}else{
			return true;
		}
	}
	public function isFloat($value, $key){
		if(!is_numeric($value)){
			$this->errorList[] = $this->t("nombre",$key);
			return false;
		}else{
			return true;
		}
	}
	public function tailleMini($value, $key, $taille){
		if(strlen(trim($value)) < $taille){
			$this->errorList[] = $this->t("tailleMini",$key,array($taille-1));
			return false;
		}else{
			return true;
		}
	}
	public function tailleMaxi($value, $key, $taille){
		if(strlen(trim($value))>$taille){
			$this->errorList[] = $this->t("tailleMaxi",$key,array($taille+1));
			return false;
		}else{
			return true;
		}
	}
	public function inRange($value,$key, array $range){
		if($this->isInt(intval($value), $key)){
			if($value>=$range[0] and $value<=$range[1]){
				return true;
			}else{
				$this->errorList[] = $this->t("inRange",$key,$range);
				return false;	
			}			
		}
	}
	public function isNotVoid($value, $key){
	
		if(trim($value) == ""){
			$this->errorList[] = $this->t("isNotVoid",$key);
			return false;
		}else{
			return true;
		}
	}
	public function isDate($value, $key) {
		
		$regexp = '@\b[0123]{1}\d{1}/[01]{0,1}\d{1}/\d{4}\b@';
		
	
		if(preg_match($regexp,$value) == 0){
			
			$this->errorList[] = "$key doit être une date au format jj/mm/aaaa";
			return false;
		}else{
			
			list($jour,$mois,$annee) = explode('/',$value);
				
			if(checkdate($mois,$jour,$annee) && ($annee <= date('Y'))){
				return true;
			}else{
				$this->errorList[] = "$key doit être une date valide au format jj/mm/aaaa";
				return false;
			}
			
		}
	}
	
	public function isDateFormat($value, $key) {
		$regexp = '@^[0123]{1}\d{1}/[01]{0,1}\d{1}/\d{4}\s*(\d{2}:\d{2})?$@';	
		
		if(preg_match($regexp,$value) == 0){
			$this->errorList[] = "$key doit être une date au format jj/mm/aaaa hh:mm";
			return false;
		}else
			return true;
	}
	
	public function comparer($val1,$val2,$strict=false) {
		if($strict){
			return ($val1 === $val2)? true : false;
		}else{
			return ($val1 == $val2)? true : false;
		}
	}
	
	public function onlyAlphanum($value, $key) {
		$regexp = '@^[a-zA-Z0-9]*$@';
		if(preg_match($regexp,$value) == 0){
			$this->errorList[] = "$key ne doit contenir que des lettres ou des chiffres (pas de caractères accentués ou spéciaux)";
			return false;
		}else
			return true;
	}
	
	public static function genererCaptcha() {
		
		// init
		$chiffres = array('un','deux','trois','quatre','cinq','six','sept','huit','neuf');		
		$max = 9;
		
		$randomNumber1 = rand(2,$max);
	
		do{
			$randomNumber2 = rand(1,$max);
		}while ($randomNumber1 < $randomNumber2);
		
		$operateurChoix = rand(0,2);
		
		$codeverif = "$randomNumber1-$randomNumber2-$operateurChoix";
		
			
		switch($operateurChoix){
			case 0:
				$operateur = "plus";
				break;
			case 1: 
				$operateur = "moins";
				break;
			case 2:
				$operateur = "fois";
				break;
		}
		
		$phrase = "Combien font ".$chiffres[$randomNumber1-1]." $operateur " .$chiffres[$randomNumber2-1]." ?";
		
		//retourne le code et la phrase
		return array('code'=>$codeverif,'phrase'=>$phrase);
	}
	/**
	 * 
	 * @param $code
	 * @param $reponse
	 * @return bool
	 */
	public static function verfierCaptcha($code,$reponse) {
		//init
		$nb1 = $nb2 = $operateur = $rep = null;
		
		list($nb1,$nb2,$operateur) = explode('-',$code);
		
		switch($operateur){
			case 0:
				$rep = $nb1+$nb2;
				break;
			case 1: 
				$rep = $nb1-$nb2;
				break;
			case 2:
				$rep = $nb1*$nb2;
				break;
		}
		
		return ($rep == $reponse);
	}
	/**
	 * 
	 * @param string $jj
	 * @param string $mm
	 * @param string $aaaa
	 * @return DateNF
	 */
	public function verifierDateTroisPartie($jj,$mm,$aaaa) {
		if($jj > 31 || $mm > 12)
			return false;
			
		try{
			$date = new DateNF("$jj/$mm/$aaaa");
			return $date;
		}catch(Exception $e){
			return false;
		}
	}
	
	private function t($mot,$valeur=null,$option=null){
		
		$mots = array(
			"email_invalide"	=> array("Email invalide","Incorrect email"),
			"nombre_entier"		=> array("$valeur doit être un nombre entier","$valeur must be an integer"),
			"nombre"			=> array("$valeur doit être un nombre","$valeur must be a number"),
			"tailleMini" 		=> array("$valeur doit être supérieur à {$option[0]} caractères", "$valeur must be more than {$option[0]} chars"),
			"tailleMax" 		=> array("$valeur doit être inférieur à {$option[0]} caractères","$valeur must be less than {$option[0]} chars"),
			"inRange" 			=> array("$valeur doit être un nombre compris entre {$option[0]} et {$option[0]}","$valeur must be a number between {$option[0]} and {$option[0]}"),
			"isNotVoid" 		=> array("$valeur est requis","$valeur is required"),
			"isDate" 			=> array("$valeur doit être une date valide au format jj/mm/aaaa","$valeur must be a valid date dd/mm/yyyy"),
			"isDateFormat" 		=> array("$valeur doit être une date valide au format jj/mm/aaaa hh:mm","$valeur must be a valid date jj/mm/aaaa hh:mm"),
			"onlyAlphanum" 		=> array("$valeur ne doit contenir que des lettres ou des chiffres (pas de caractères accentués ou spéciaux)","$valeur must contains only letters and numbers (no specials chars)")
		);
		
		if($this->lang == "en"){
			return $mots[$mot][1];
		}else{
			return $mots[$mot][0];
		}
	} 
}
?>