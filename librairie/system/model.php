<?php
/**
 * Classe abstraite contenant les fonctions
 * nécessaire a la communication avec la bdd.
 * 
 * @todo remettre au propre ce bordel !
 *
 * @author Nicolas Baptiste
 *
 */
abstract class Model{
	
	/**
	 * @var PDO
	 */
	protected $cnx;
	
	/**
	 * @var Paginator
	 */
	protected $paginator;

	/**
	 * Récupération de l'instance de connexion à la BDD
	 */
	public function __construct(){
		$this->cnx = Connexion::getInstance();	
	}
	/**
	 * Fermture de la connexion 
	 */
	public function __destruct(){
		$this->cnx = null;
	}
	/**
	 * Suppression d'une ligne dans une table donnée
	 * 
	 * @param int 		$id
	 * @param string 	$table
	 */
	public function supprimer($id,$table){
		$sql 	= "DELETE FROM $table WHERE id = :id";		
		return self::preparedExecute($sql, array(':id'=>$id));
	}
		
	/**
	 * Permet de construire une requête en vérifiant automatiquement les
	 * contraintes imposées par le modèle
	 *
	 * @param string $requete
	 * @return string
	 */
	public function setPaginator($paginator){
		$this->paginator = $paginator;
	}
			
	/**
	 * Redirection vers l'update ou l'insert 
	 * en fonction de la présence de la clé id
	 * dans la collection $post
	 *
	 * @param array $post
	 * @param string table
	 */
	public function save(array $post,$table){
		
		// suppression de envoyer
		unset($post['envoyer']);		
						
		// détection du passage d'un id 
		$setString = (isset($post['id'])) ?  $this->update($post,$table) : $this->insert($post, $table); 
		
		// création d'un PDOStatement
		$stm = $this->cnx->prepare($setString);
				
		// création des commandes de bindValue
		foreach($post as $key=>$p)
		{	
			if(is_numeric($p)){
				$stm->bindValue(':'.$key, $p, PDO::PARAM_INT);
			}elseif($p == "setToNULL"){
				$stm->bindValue(':'.$key, null, PDO::PARAM_INT);
			}else{
				$stm->bindValue(':'.$key, $p, PDO::PARAM_STR); 
			}
		}
				
		$stm->execute();
		
		$arr = $stm->errorInfo();
		if($arr[0] != '00000') throw new ModelException($arr[2],$arr[0]);
				
		if(!isset($post['id']))
			return $this->cnx->lastInsertId();
		else
			return $post['id'];	
	}

	/**
	 * Retourne une ligne de table dont la clé primaire est de type int
	 * et s'appelle id
	 * 
	 * @param $id
	 * @param $table
	 * @return array
	 * @throws ModelException
	 */	
	public function getLigne($id,$table) {
		
		$sql = "SELECT * FROM $table WHERE id = :id";
		$rs = self::preparedSelect($sql, array(':id'=>$id));
		
		if(count($rs) == 0)
			throw new ModelException("Aucune ligne dans la table '$table' à l'identifiant $id");
		
		return $rs[0];
	}
	
	/**
	 * Construit une requete sql pour pouvoir prendre en compte
	 * un paginateur
	 * 
	 * @param string $requete
	 * @param array $params
	 * @return string
	 */
	protected function sqlCraft($requete, array $params = null){
		// Initialisation
		$builtRequest = $requete;
		if(!isset($params['where'])) 	$params['where'] 	= null;
		if(!isset($params['groupby'])) 	$params['groupby'] 	= null;
		if(!isset($params['order'])) 	$params['order'] 	= null;
		
		// Partie WHERE
		if(!is_null($params['where']) && !empty($params['where'])){
			$builtRequest .= " WHERE ".$params['where']." ";
		}
		// Partie GROUP BY
		if(!is_null($params['groupby']) && !empty($params['groupby'])){
			$builtRequest .= " GROUP BY ".$params['groupby']." ";
		}
		// Partie ORDER
		if(!is_null($params['order'])){
			$builtRequest .= " ORDER BY ".$params['order']." ";
		}
		
		// Partie LIMIT
		if(isset($this->paginator)){
			$builtRequest .= $this->paginator->getLimit();
		}elseif(isset($params['limit']) and $params['limit'] !=''){
			$builtRequest .= $params['limit'];
		}
		
		return $builtRequest;	
	} 
	/**
	 * Requête préparée pour interroger une base et retourner une liste de résultats (= Result Set)
	 * 
	 * @param string $sql
	 * @param array $values
	 * @param PDO $cnx
	 * 
	 * @throws ModelException
	 */
	protected function preparedSelect($sql, array $values = array(),PDO $cnx = null) {
		
		if(is_null($cnx))
			$stm = Connexion::getInstance()->prepare($sql);
		else
			$stm = $cnx->prepare($sql);
			
		$stm->execute($values);
	
		$arr = $stm->errorInfo();
		if($arr[0] != '00000') throw new ModelException($arr[2],$arr[0]);
	
		return $stm->fetchAll(PDO::FETCH_ASSOC);
	}
	
	protected function preparedSelectUnique($sql, $toReturn, array $values = array(),PDO $cnx = null) {
		
		if(is_null($cnx))
			$stm = Connexion::getInstance()->prepare($sql);
		else
			$stm = $cnx->prepare($sql);

		$stm->execute($values);
		
		$arr = $stm->errorInfo();
		if($arr[0] != '00000') throw new ModelException($arr[2],$arr[0]);
	
		$rs = $stm->fetchAll(PDO::FETCH_ASSOC);
		
		if(count($rs) == 0) throw new ModelException("Aucune donnée pour le champ '$toReturn'");
		
		return $rs[0][$toReturn];
	}
	/**
	 * Execution préparée via SQL
	 * 
	 * @param string $sql
	 * @param array $condition
	 * @throws ModelException
	 */
	protected function preparedExecute($sql, array $condition = array()){
		$stm = Connexion::getInstance()->prepare($sql);

		$stm->execute($condition);
		
		$arr = $stm->errorInfo();
		
		if($arr[0] != '00000')
			throw new ModelException($arr[2],$arr[0]);
	}
	/**
	 * @param string $sql
	 * @return PDOStatement
	 */
	protected function prepareRequete($sql){
		$stm = Connexion::getInstance()->prepare($sql);
		return $stm;
	}
	
	/**
	 * Execution de la requete de MAJ de la bdd
	 *
	 * @param array $post
	 * @param string $table
	 * @return string $setString
	 */
	private function update(array $post, $table){
		
		$post['id'] = (int) $post['id'];
		
		// Tableau des valeurs a modifier
		$values = array_filter(array_keys($post),array($this,"filtreTab"));
		
		// Initialisation de la chaine a passer au prepared statement
		$setString = "UPDATE $table SET ";
		
		foreach($values as $value)
			$setString .= "`$value` = :$value, ";
		
		$setString = substr($setString,0,-2);
		$setString .= " WHERE id = :id";
		
		return $setString;
		
	}
	
	/**
	 * @deprecated
	 * 
	 * Execution de la requete de MAJ de la bdd pour les tables n'utilisant pas 
	 * de clé primaire numérique
	 *
	 * @param array $post
	 * @param string $table
	 * @param string $pk
	 */
	private function updateNonNumericKey(array $post, $table, $pk){
				
		// Tableau des valeurs a modifier
		$values = array_filter(array_keys($post),array($this,"filtreTab"));
		
		// Initialisation de la chaine a passer au prepared statement
		$setString = "UPDATE $table SET ";
		
		foreach($values as $value)
			$setString .= "`$value` = :$value, ";
		
		$setString = substr($setString,0,-2);
		$setString .= " WHERE $pk = :$pk";
		
		return $setString;
		
	}
	private function insert(array $post, $table){
			
		// Tableau des valeurs a modifier
		$values = array_filter(array_keys($post),array($this,"filtreTab"));
		
		// Initialisation de la chaine a passer au prepared statement
		$setString = 'INSERT INTO '.$table.'(';
		
		foreach($values as $value)
			$setString .= "`$value`, ";
		
		$setString = substr($setString,0,-2);
		$setString .= ") VALUES(";
		
		foreach (array_keys($post) as $key) 
			$setString .= ":$key, ";
		
		$setString = substr($setString,0,-2).")";
		
		return $setString;
	}
	
	public function compterResultats($sql, $values = array()) {
		$sql = "SELECT COUNT(*) AS nb FROM ($sql) AS total";
		$rs = $this->preparedSelect($sql, $values);
		
		return $rs[0]['nb'];
	}
	/**
	 * Callback utilisé dans self::update par la fonction
	 * array_filter
	 *
	 * @param unknown_type $var
	 * @return unknown
	 */
	private function filtreTab($var){
		$cles = array('envoyer','id');
		foreach($cles as $cle){
			if ($var == $cle) 
				return false;
		}
		return true;
	}
}
?>