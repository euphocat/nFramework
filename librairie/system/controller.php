<?php
Abstract Class Controller{
	
	/**
	 * Tableau des options
	 *
	 * @var array(string)
	 */
	public $options;
	/**
	 * Outils de vérification de formulaires
	 *
	 * @var CheckForm
	 */
	private $checkForm;
	
	public $template		= null;
	public $templateData	= null;
	

	public $statistiques	= true;
	public $debug 			= false;
		
	/**
	 * gestion de la pagination
	 *
	 * @var Paginator
	 */
	private $paginator;
	
	/**
	 * Tableau contenant les notifications, les warnings et erreurs
	 * 
	 * @var array
	 */
	private $notifications 	= array(); 
	
	/**
	 * Tableau representant la requête HTTP
	 * 
	 * @var array
	 */
	private $requete = null;
	
	public function __construct($options)
	{
		header('Content-type: text/html; charset=UTF-8');
		$this->options 			= $this->traitementOptions($options);
	}
	/**
	 * Vérifie les erreurs, avertissements et notifications 
	 * et les passe dans la session s'ils existent
	 * 
	 */
	public function __destruct()
	{
		if(!is_null($this->notifications) && count($this->notifications)>0)
			$_SESSION['notifications'] 	= $this->notifications;
	}
	/**
	 * Appel automatique de l'index si l'action spécifiée est inconnue
	 * 
	 * @param string $fonction
	 * @param array(mixed) $args
	 */
	public function __call($fonction,$args=null)
	{
		$this->index();
	} 
	/**
	 * @param array $requete
	 * @throws ControllerException
	 *
	 */
	public function setRequete(array $requete){
		if(is_null($this->requete))
			$this->requete = $requete;
		else
			throw new ControllerException("Le tableau requête à déjà été renseigné");
	}
	public function getRequete() {
		return $this->requete;
	}
	/**
	 * Vue par défaut à instancier
	 */
	protected abstract function index();
	
	/** 
	 * Commande l'affichage de la vue
	 * 
	 * @param View $view
	 * @param bool $debug
	 */
	protected function afficherVue(View $vue, $debug = false) 
	{
		if($debug || $this->debug){
			$vue->requete = $this->getRequete();
			$this->templateData["debug"] = true;
		}
		
		// change le template par defaut
		if(!is_null($this->template)) $vue->setTemplate($this->template);
		
		//transmet automatiquement le paginator s'il est instancié
		if (isset($this->paginator) and !$ajax) 
		{
			$vue->addData('nbTotal',$this->paginator->nbTotal);
			$vue->addData('nbParPage',$this->paginator->nbParPage);	
			$vue->addData('pageEnCours',$this->paginator->pageEnCours);						 
		}
		
		// transmet les informations au template, s'il y en a
		if(isset($this->templateData)) $vue->templateData = $this->templateData;	
		
		// insérer les bout de code pour activer les stats 
		if($this->statistiques)	$vue->stats = true;
		
		$vue->setNotification($this->getNotifications());
		
		$vue->afficher();
	}
	/**
	 * Permet d'ajouter un niveau d'imbrication dans les urls
	 * et de charger des fonctions de sous-controleurs en fonction
	 * du premier parametre passé en option
	 */
	public function chargerNiveauEnPlus($options) 
	{
		$this->options = $options;
		
		if(isset($this->options['texte'])){
			$action = $this->options['texte'];
			$this->$action();
		}else
			$this->index();
	}
	/**
	 * Affiche la page d'erreur 404
	 */
	public function erreur404()
	{
		header("HTTP/1.0 404 Not Found");
		$view = Load::view('erreur');
		$this->afficherVue($view);
	}
	/**
	 * Attention ne pas utiliser dans un contexte AJAX, sinon l'erreur
	 * sera intégrée dans la session et affichée lors du prochain 
	 * rechargement de page
	 * 
	 * @param String $erreur
	 */
	protected function addError($erreur){
		$this->notifications['erreurs'][] = $erreur;
	}
	/**
	 * Attention ne pas utiliser dans un contexte AJAX, sinon l'erreur
	 * sera intégrée dans la session et affichée lors du prochain 
	 * rechargement de page
	 * 
	 * @param String $notif
	 */
	protected function addNotif($notif){
		$this->notifications['notifs'][] = $notif;
	}
	/**
	 * Attention ne pas utiliser dans un contexte AJAX, sinon l'erreur
	 * sera intégrée dans la session et affichée lors du prochain 
	 * rechargement de page
	 * 
	 * @param String $warning
	 */
	protected function addWarning($warning){
		$this->notifications['warnings'][] = $warning;
	}
	/**
	 * Setter du paginator, avec détection automatique de 
	 * la page en cours.
	 *
	 * @param Paginator $paginator
	 * @param int $nbPagesTotal
	 */
	protected function setPaginator(Paginator $paginator, $nbPagesTotal = 0){
		
		$paginator->nbTotal	= $nbPagesTotal;	
		
		if(isset($this->options['page']) and ($nbPagesTotal >= $this->options['page']))
			$paginator->pageEnCours = $this->options['page'];
		else
			$paginator->pageEnCours = 1;
		
		$_SESSION['pageEnCours'] = $paginator->pageEnCours;
		
		$this->paginator = $paginator;	
	}
	/**
	 * @param Array $errorList
	 */
	protected function setErrorList(Array $errorList){
		$this->notifications['erreurs'] = $errorList;
	}
	/**
	 * @param Array $notifList
	 */
	protected function setNotifList(Array $notifList){
		$this->notifications['notifs'] = $notifList;
	}
	/**
	 * @param Array $warningList
	 */
	protected function setWarningList(Array $warningList){
		$this->notifications['warnings'] = $warningList;
	}
	/**
	 * @return Array
	 */
	protected function getErrorList() {
		return $this->notifications['erreurs'];
	}
	/**
	 * @return Array
	 */
	protected function getNotifList() {
		return $this->notifications['notifs'];
	}
	/**
	 * @return Array
	 */
	protected function getWarningList() {
		return $this->notifications['warnings'];
	}
	/**
	 * Vérifie si des notification existent en session
	 * et s'il y en a, les regroupent avec celle déjà
	 * définies
	 * 
	 * @return array
	 */
	private function getNotifications() {
		//vérification des notifications en sessions
		if(isset($_SESSION['notifications'])){
			$this->notifications = array_merge_recursive($_SESSION['notifications'],$this->notifications);
			unset($_SESSION['notifications']);
		}
		
		$notifications 			= $this->notifications;
		$this->notifications 	= null;
		
		//
		return $notifications;
	}
	/**
	 * @return Paginator
	 */
	protected function getPaginator(){
		return $this->paginator;
	}
	/**
	 * @return CheckForm
	 */
	public function getCheckForm() {
		if(is_null($this->checkForm))
			$this->checkForm = new CheckForm();
		return $this->checkForm;
	}
	/**
	 * Mise en forme des options passées dans l'url
	 *
	 * @param array $options
	 * @return array(String)
	 */
	private function traitementOptions($options)
	{	
		$toReturn = array();
		if(!is_null($options))
		{
			foreach($options as $option)
			{
				if(strstr($option,":"))
				{
					list($key,$value) = explode(":",$option);
					$toReturn[$key] = $value;
				}
				else
				{
					if(is_numeric($option))
						$toReturn['id'] = $option;
					elseif(trim($option)!="")
						$toReturn['texte'] = $option;
				}
			}
		}
		return $toReturn;
	}
	/**
	 * Détermine s'il y a des erreurs qui ont été inscrites
	 * 
	 * @return boolean
	 */
	protected function errorsDefined() {
		return (count($this->getErrorList()) > 0);
	}
	
}
?>