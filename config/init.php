<?php
/**
 * Constantes de gestion, et alias
 */
define('SERVER_NAME', 'http'.($_SERVER['HTTPS'] ? 's' : '')
	.'://'.$_SERVER['SERVER_NAME']
	.':'.$_SERVER['SERVER_PORT'].'/');
/**
 * Pour les mot de passes, on les concat avec ça pour générer le MD5 à 
 * enregistrer dans la base. Comme ça en cas de vol des données les mdp restent 
 * secrets
 */
define('PWD_SEED', 'Hash more and more');

/* Controleur à appeler par défaut */
//define('DEFAULT_CONTROLER', 'Down'); // Controleur à appeler pendant une maintenance
define('DEFAULT_CONTROLER', 'Index');
define('DEFAULT_LANG', 'FR');

/**
 * Constantes de fonctionnement
 */
define('PWD_SU', md5('test'.PWD_SEED));
define('ROOT', substr(dirname(__FILE__), 0, -strlen('/config')));
define('DAO_CACHE_PATH', ROOT.'/config/daocache');
define('DAO_EXTENDS_PATH', ROOT.'/class/table');
define('FIND_EXTENDS_PATH', ROOT.'/class/find');
define('UPDATE_EXTENDS_PATH', ROOT.'/class/update');
define('DEF_BASE', 'HE'); // Nom de la constante de base de donnée par défaut

/**
 * Le chemin par défaut du template est indiqué pour le front office, pour 
 * l'utiliser dans d'autre ressources, il faut le spécifier AVANT la première  
 * déclaration de la class
 */
define('CACHE_PATH', ROOT.'/cache');

/* Constantes de paramétrage de la plateforme */
//require(ROOT.'/config/param/dev.php');
require(ROOT.'/config/param/prod.php');

/**
 * Ouverture de l'accès aux objets
 */
require('autoload.php');
He\Autoload::init();
He\Trace::init();
He\Session::init();

/**
 * Initialisation des DAO
 */
require('initdb.php');

/**
 * Initialisation du layer d'affichage
 */
require('initlayer.php');

/**
 * Initialisation des paramètres locaux
 */
require('inituser.php');