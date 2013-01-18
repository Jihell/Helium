<?php
/**
 * Localise un texte à partir d'une BDD
 * 
 * Cette class à besoin des tables suivantes :
 * he_lang : liste de langues disponible
 * he_localise : clefs de traduction traduite
 * he_lang_cat : catégorie de clefs
 * 
 * @author Joseph Lemoine - joseph.lemoine@gmail.com
 * @version 2
 */
namespace He;

class Localise
{
	/**
	 * Constructeur de class, class static
	 */
	private function __construct() {}
	private function __clone() {}
	
	/**
     * Touve et remplace toutes les occurences de localisation dans le XML
     * envoyé. La langue utilisé sera celle présente dans $_SESSION['lang']
     * @param	&string	&$xml	xml où appliquer la localisation
	 * @return	bool
     */
    public static function run(&$xml)
	{
		/* Recherche des clef de localisation */
		$pattern = '#{%([^}]*)}#s';
		preg_match_all($pattern, $xml, $matches);
		/* Si on à des clefs à traiter */
		if (!empty($matches[1]))
		{
			/* Préchargement des clefs pour optimiser */
		    foreach ($matches[1] AS $n => $keyName)
			{
				$selector = ' t.key = '.\He\PDO::bind($keyName).' OR ';
			}
			$selector = ' t.id_he_lang = '.\He\PDO::bind($_SESSION['lang'])
					.' AND ('.substr($selector, 0, -4).')';
			
		    /* Enregistrement de chaque clef */
		    foreach ($matches[1] AS $n => $keyName)
			{
				/* Récuépration de la valeur */
				$key = \He\DB::he_localise(array($_SESSION['lang'], strtoupper($keyName)));

				/* Définition de la valeur par défaut de la clef si elle n'existe pas */
				if(!$key->exist())
				{
					$replace = $key->getNeedle();
				}
				else
				{
					$replace = $key->getContent();
				}
				
				$xml = str_replace($matches[0][$n], $replace, $xml);
		    }
		}
    }
	
    /**
     * Définie la langue à utiliser selon le navigateur du client.
	 * Si la langue du navigateur n'est pas reconnu, on prend la langue
	 * par défaut du site définie dans la constante DEFAULT_LANG
	 * @return bool
     */
    public static function setDefaultLang()
	{
		/* Si on à pas déjà créer de session spéciale */
		if(empty($_SESSION['lang']))
		{
		    /**
		     * Récupération et test de la langue du navigateur
		     * Si elle est invalide on utilise $_lang qui est définie par
		     * défault dans une langue valide
		     */
		    $tempLang = strtoupper(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
		    if(static::isValidLang($tempLang))
			{
				/* On définie cette langue comme langue d'affichage */
				$_SESSION['lang'] = $tempLang;
				\He\Trace::addTrace('Langue récupéré du navigateur : '.$_SESSION['lang'], get_called_class());
		    }
			else
			{
				\He\Trace::addTrace('Langue par défaut : '.$_SESSION['lang'], get_called_class());
				$_SESSION['lang'] = DEFAULT_LANG;
			}
		}
		
		return true;
    }
	
    /**
     * Test si une langue donnée existe dans les localisations du site
     * @param	string	$tag	Tag de la langue à tester
     */
    public static function isValidLang($iso)
	{
		return \he\DB::find('he_lang')->exist($iso);
    }
}