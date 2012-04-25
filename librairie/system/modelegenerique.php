<?php
/**
 * Classe permettant d'utiliser les méthodes de la classe abstraite Model.
 * 
 * @author Nicolas Baptiste
 *
 */
class ModeleGenerique extends Model{
	public function preparedSelect($sql, array $values = array(),PDO $cnx = null) {
		return parent::preparedSelect($sql,$values,$cnx);
	}
	/**
	 * @return \PDO
	 */
	public function getCnx() {
		return $this->cnx;
	}
}
?>