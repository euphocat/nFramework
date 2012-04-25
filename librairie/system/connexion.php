<?php
/**
 * Singleton de la connexion à la base de donnée via PDO
 * 
 * @author Nico
 */
final class Connexion
{
	/**
	 * @var PDO
	 */
	private static $cnx;
	
	private function __construct(){}
	private function __clone(){}
	
	/**
	 * @return PDO
	 */
	public static function getInstance()
	{
		if(is_null(self::$cnx))
		{
			try 
			{
				self::$cnx = new PDO('mysql:host=127.0.0.1;dbname=nfm;','root','');
			}
			catch (Exception $e)
			{
				echo "Probl&egrave;me de connexion &agrave; la base de don&eacute;es<br />";
				echo $e->getMessage();
				exit;
				//echo $e->getMessage();
			}
		}
		// toutes le requêtes reverront de l'UTF-8
		self::$cnx->query('SET NAMES utf8;');
		return self::$cnx;
	}
	public static function fermerConnexion() 
	{
		self::$cnx = null;
	}
}
?>