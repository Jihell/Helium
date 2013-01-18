<?php
/**
 * Générateur de template.
 * 
 * Class statique regroupant une collection de class \He\Template\Node traitant
 * l'affichage selon l'html envoyé dans des fichiers extérieurs.
 * 
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He;

class Template
{
	/**
	 * Noeud racine de ce template
	 * @var \He\Template\Node 
	 */
	protected static $_root;
	
	/**
	 * Liste des template chargés
	 * @var array
	 */
	protected static $_loadedTemplate = array();
	
	/**
	 * Détermine si le template chargé est une image du cache. Si vrai la méthode
	 * bindTemplate ne chargera pas de nouveau template.
	 * @var type 
	 */
	protected static $_fromCache = false;
	
	/**
	 * Class statique
	 */
	protected function __construct() {}
	protected function __clone() {}
	
	/**
	 * Ajoute le template indiqué par le chemin $path aux templates chargés.
	 * L'analyse est effectué dans la foulé depuis l'objet \He\Template\Node.
	 * On retourne un objet \He\Template\Node
	 * @param string $path 
	 * @return \He\Template\Node
	 */
	public static function bind($path)
	{
		if(!$_SESSION['SU'])
		{
			/**
			 * On recherche dans le cache si une version localisé existe
			 */
			if(LOCALISE && !\He\Dispatch::getAskSU())
			{
				if(file_exists(CACHE_PATH.'/localised/'.\He\Dispatch::getControler().'.php'))
				{
					/**
					 * Une version localisé et complète du template existe, on la charge
					 * et on ne chargera aucun autre template pour l'affichage
					 */
					$path = CACHE_PATH.'/localised/'.\He\Dispatch::getControler().'.php';

					if(empty(static::$_root))
					{
						static::$_root = new \He\Template\Node(file_get_contents($path), 
								null, 
								static::_getPath($path));
					}

					return static::$_root;
				}
			}

			/**
			 * On recherche si une version fusionné existe
			 */
			if(MERGE_TEMPLATE && !\He\Dispatch::getAskSU())
			{
				if(file_exists(CACHE_PATH.'/merge/'.\He\Dispatch::getControler().'.php'))
				{
					/**
					 * Une version fusionné et complète du template existe, on la charge
					 * et on ne chargera aucun autre template pour l'affichage
					 */
					$path = CACHE_PATH.'/merge/'.\He\Dispatch::getControler().'.php';

					if(empty(static::$_root))
					{
						static::$_root = new \He\Template\Node(file_get_contents($path), 
								null, 
								static::_getPath($path));
					}

					return static::$_root;
				}
			}
		}
		
		/**
		 * Si on a déjà chargé ce template on envoi une erreur, c'est une boucle
		 * infinie.
		 */
		if(in_array($path, static::$_loadedTemplate))
		{
			\He\Trace::addTrace('Le template '.$path.' est déjà ajouté !', get_called_class(), -1);
			return true;
		}
		
		if(file_exists($path))
		{
			/**
			 * Si on à pas encore de template, on créer la racine avec les
			 * données récupérés.
			 */
			if(empty(static::$_root))
			{
				static::$_root = new \He\Template\Node(file_get_contents($path), 
						null, 
						static::_getPath($path));
			}
			/**
			 * Sinon on ajoute les données à la suite du node.
			 */
			else
			{
				static::$_root->addContent(file_get_contents($path), static::_getPath($path));
			}
			
			static::_addLoadedTemplate($path);
		}
		else
		{
			throw new \He\Exception('Aucun template trouvé à l\'adresse '.$path.' !');
		}
		
		return static::$_root;
	}
	
	/**
	 * Récupère le nom du chemin du fichier envoyé exemple :
	 * c:/wamp/www/he/index.php => c:/wamp/www/he
	 * @param string $filePath
	 * @return string 
	 */
	protected static function _getPath($filePath)
	{
		$parsed = explode('/', $filePath);
		array_pop($parsed);
		return implode('/', $parsed);
	}
	
	/**
	 * Ajoute le contenue $xml au node racine
	 * @param string $xml 
	 * @return \He\Template\Node
	 */
	public static function addContent($xml)
	{
		if(!empty(static::$_root))
			static::$_root->addContent($xml);
		
		return static::$_root;
	}
	
	/**
	 * Renvoi un objet \He\Template\Node avec le contenue du fichier envoyé
	 * par le chemin $path.
	 * @param string $path
	 * @return \He\Template\Node
	 */
	public static function makeNode($path)
	{
		if(file_exists($path))
		{
			\He\Trace::addTrace('Création d\'un nouveau node en standalone', get_called_class());
			return new \He\Template\Node(file_get_contents($path), null, static::_getPath($path));
		}
		else
		{
			throw new \He\Exception('Aucun template trouvé à l\'adresse '.$path.' !');
		}
	}
	
	/**
	 * Renvoi le contenu traité du template
	 * @param string $path
	 * @return string 
	 */
	public static function extract($path)
	{
		if(file_exists($path))
		{
			$temp = new \He\Template\Node(file_get_contents($path));
			return $temp->getContent();
		}
		else
		{
			throw new \He\Exception('Aucun template trouvé à l\'adresse '.$path.' !');
		}
	}
	
	/**
	 * Renvoi le contenu brut du template
	 * @param string $path
	 * @return string 
	 */
	public static function getRaw($path)
	{
		if(file_exists($path))
		{
			return file_get_contents($path);
		}
		else
		{
			throw new \He\Exception('Aucun template trouvé à l\'adresse '.$path.' !');
		}
	}
	
	/**
	 * Ajoute le template à la liste de templates chargés
	 * @param string $path 
	 */
	protected static function _addLoadedTemplate($path)
	{
		static::$_loadedTemplate[] = $path;
		return true;
	}
	
	/**
	 * Envoi le header spécifique ajax
	 * @return bool
	 */
	public static function sendAjaxHeader()
	{
		header('Content-Type:text/plain');
		return true;
	}
	
	/**
	 * Ajoute les headers pour interdire la mise en cache du client
	 * @return bool
	 */
	public static function sendDefaultHeader()
	{
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1980 00:00:00 GMT");
		return true;
	}
	
	/**
	 * Affiche les templates stockés
	 * @return bool
	 */
	public static function draw()
	{
		\He\Trace::addTrace('Envoi du rendu au client', get_called_class(), 2);
		if(!empty(static::$_root))
			echo static::$_root->getContent();
		
		/**
		 * Pour des raisons de sécurité, les fichiers ne seront JAMAIS
		 * générés depuis un compte super user
		 */
		if(!$_SESSION['SU'])
		{
			/**
			 * Création du fichier mergé
			 */
			if(MERGE_TEMPLATE && !\He\Dispatch::getAskSU())
				static::$_root->_makeMergeFile();

			/**
			 * Création du fichier localisé et du fichier de listage des variables
			 * de localisation utilisés
			 */
			if(LOCALISE && !\He\Dispatch::getAskSU())
				static::$_root->_makeLocalisedFile();
		}
		
		return true;
	}
	
	/**
	 * Test si le chemin envoyé est un template valide
	 * @param string $path
	 * @return bool
	 */
	public static function isTemplate($path)
	{
		/* Si la chaine envoyé est trop longue, ce n'est pas un chemin valide */
		if(strlen($path) > 1024)
			return false;
		
		/* Est-ce que le template existe ? */
		if(file_exists($path))
			return true;
		else
			return false;
	}
}