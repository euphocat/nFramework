<?php
/**
 * Classe tranversale au MVC pour paginer tout est n'importe quoi
 * 
 * @author nicolas
 *
 */
class Paginator{
	
	public $nbParPage;
	public $nbTotal;
	public $pageEnCours;
	
	public function __construct($nbParPage, $pageEnCours = 1){
		$this->nbParPage 	= $nbParPage;
		$this->pageEnCours	= $pageEnCours;
	}
	public function getLimit(){
		return ' LIMIT '. ($this->nbParPage*($this->pageEnCours-1). ','.$this->nbParPage);
	}
	public function getNbPages(){
		return $this->nbTotal/$this->nbParPage;
	}
}

?>