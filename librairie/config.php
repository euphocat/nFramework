<?php
/**
 * Chemin d'accès à la librairie
 *
 */
//define("RACINE",'D:/travail/nicoframework/');
//define("RACINE",'E:/workspace/nicoframework/trunk/');
define("RACINE",'/home/nicolas/workspace/nFramework/');


/**
 * Chemin d'accès à la librairie
 *
 */
define("LIBRAIRIE",RACINE.'librairie/');

/**
 * Chemin d'accès au cache
 * 
 */
define("CACHE",RACINE."site/cache");

/**
 * Activation ou non du cache de données
 */
define("UTILISER_CACHE",false);

/**
 * Gestion des exceptions
 */
define("DEBUG",true);

/**
 * Chemin du template par défaut du site
 *
 */
define("TEMPLATE","default");

/**
 * Adresse du site
 *
 */
define("ADRESSE","http://".$_SERVER['HTTP_HOST']."/");

/**
 * Variables locales FR
 */
setlocale(LC_ALL,"fr_FR"); 

/**
 * Salt key 
 * 
 */
define("SALT_KEY","gh4t45tyht54@!&kughjçà!è(§(§è))");

/**
 * Version de jQuery
 *  
 */
define("JQUERY",'jquery-1.5.1.min');

define("SIMPLE_TEST",LIBRAIRIE."plugin/simpletest/")

?>