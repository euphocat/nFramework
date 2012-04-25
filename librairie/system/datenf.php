<?php
/**
 * Gestion des dates pour MySQL et les formats français
 * 
 * @author euphocat
 * @copyright Vélo 101
 * @version 1.0
 * 
 * @todo test unitaires
 *
 */
Class DateNF extends DateTime{
	
	const SQL 		= 'Y-m-d H:i';
	const SQLJour	= 'Y-m-d';
	const FR 		= 'd/m/Y';
	const EN 		= 'm/d/Y';
	const FRH		= 'd/m/Y H:i';
	
	private $timestamp = null;
	
	/**
	 * Prend en arg1 une date au format accepté par strtotime() ou au format
	 * français
	 * 
	 * @example "17/05/1986', '17/05/1986 21:00'
	 * 
	 * Ne pas oublier les 0 
	 * 
	 * @example 07/12/2009 et pas 7/12/2009 
	 * 
	 * @param $time
	 * @param $timezone
	 * 
	 * @todo refaire ce constructeur
	 */
	public function __construct($time = "now") {

		
		$frFormatRegExp = '@^([0123]{1}\d{1})/([01]{0,1}\d{1})/(\d{4})\s*(\d{2}:\d{2})?$@';
		$enFormatRegExp = '@^()$@';
		
		if(preg_match($frFormatRegExp,$time,$m) == 0){
			parent::__construct($time);
		}else{
			$heure = (isset($m[4]))?" $m[4]":"";
			parent::__construct("$m[3]-$m[2]-$m[1]".$heure);
		}
		$this->timestamp = $this->format('U');
	}
	/**
	 * Retourne une date au format MySQL
	 * 
	 * @return String
	 */
	public function sqlFormat() {
		return $this->format(self::SQL);
	}
	/**
	 * Retourne une date au format sql sans l'heure
	 * 
	 * @return String
	 */
	public function sqlJourFormat() {
		return $this->format(self::SQLJour);
	}
	/**
	 * Retourne une date au format français simple
	 * 
	 * @return String
	 */
	public function frFormat() {
		return $this->format(self::FR);
	}
	/**
	 * Retourne une date au format anglais simple
	 * 
	 * @return String
	 */
	public function enFormat() {
		return $this->format(self::EN);
	}
	/**
	 * Retourne une date au format français date + heure
	 * 
	 * @return String
	 */
	public function frFormatHeure() {
		return $this->format(self::FRH);
	}
	/**
	 * Retourne une date au format français jour de la semaine + date
	 * 
	 * @example "samedi 17 mai 1998"
	 * @return string
	 */
	public function frLitteral() {
		return strftime("%A %d %B %Y",strtotime($this->sqlFormat()));
	}
	/**
	 * Retourne une date mois + année au format littéral
	 * 
	 * @example "avril 2008"
	 * @return string
	 */
	public function moisAnnees() {
		return strftime("%B %Y",strtotime($this->sqlFormat()));
	}
	public function infoFlash() {
		//timestamp de 00:00 
		$minuit = mktime(0,0,0,date('m'),date('d'),date('Y'));
		
		if($this->format('U')<$minuit)
			return $this->format('d/m');
		else
			return $this->format('H:i');
	}
	/**
	 * (non-PHPdoc)
	 * @see DateTime::getTimestamp()
	 */
	public function getTimestamp() {
		if(is_null($this->timestamp))
			$this->timestamp = $this->format('U');
		return (int) $this->timestamp;
	}
	/**
	 * Converti un mois numérique au format litteral
	 * 
	 * @param int $i
	 * @return string $mois
	 */
	public static function mois($i) {
		return strftime("%B",strtotime("2010-$i-01"));
	}
	/**
	 * Calcul l'age de la date
	 * 
	 * @throws Exception
	 * @return int $age 
	 */
	public function age() {
		$now = new self();
		
		if($this > $now)
			throw new DateNfException("impossible de calculer l'age, la date est dans le futur");
		
		$age = self::diff($now);
		return $age->format("%y");
	}
	public function __toString() {
		return $this->frFormatHeure();
	}
}
?>