<?php
/**
 * Factory servant à lire via des DAO les lignes d'une table.
 * 
 * =============================================================================
 * USAGE COURANT
 * =============================================================================
 * 
 * HeDB::Matable(|$idRow)->colonne => Accès à une propriété de ligne de la table
 * HeDB::Matable(|$idRow)->mamethod() => Accès à une méthode de HeDAORow
 * HeDB::Matable()->colonne => Accès à une nouvelle ligne de la table
 * HeDB::find('Matable')->methodDeListage => Accès à un tableau d'objet HeDAO
 * HeDB::dao('Matable') => Accès à la dao
 * 
 * =============================================================================
 * NOTES
 * =============================================================================
 * 
 * Class statique
 * 
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 2
 */
namespace He;

final class DB
{
	/**
	 * Liste des instances des différentes tables
	 * @var array 
	 */
	protected static $_daoInstances = array();
	
	/**
	 * Liste des objet de listage des différentes tables
	 * @var array 
	 */
	protected static $_findInstances = array();
	
	/**
	 * Liste des objet d'update des différentes tables
	 * @var array 
	 */
	protected static $_updateInstances = array();
	
	/**
	 * Liste des objets de paramétrage des différentes tables
	 * @var type 
	 */
	protected static $_param = array();
	
	/**
	 * Class static
	 */
	protected function __construct() {}
	protected function __clone() {}
	
	/**
	 * Retourne l'instance de HeFind de la table demandé lors de l'appel d'une
	 * propriété, pour des facilités d'écritures
	 * @param string $name
	 * @return \He\DB\Find 
	 */
	public static function find($name)
	{
		return self::_getFindInstanceOf($name);
	}
	
	/**
	 * Retourne l'instance de HeFind de la table demandé lors de l'appel d'une
	 * propriété, pour des facilités d'écritures
	 * @param string $name
	 * @return \He\DB\Update
	 */
	public static function update($name)
	{
		return self::_getUpdateInstanceOf($name);
	}
	
	/**
	 * Appel d'un objet HeDAO
	 * @param string $name
	 * @return \He\DB\DAO 
	 */
	public static function dao($name)
	{
		return self::_getDAOInstanceOf($name);
	}
	
	/**
	 * Appel d'un objet HeDAORow lié à l'objet HeDAO correspondant à la table
	 * demandé.
	 * @param string $name
	 * @param mixed $arguments
	 * @return HeDAORow 
	 */
	public static function __callStatic($name, $arguments)
	{
		return self::_getDAOInstanceOf($name)->row($arguments);
	}
	
	/**
	 * Crée le cas échéant et retourne une intance de HeDAO paramétré précédement
	 * @param string $name
	 * @return HeDAO
	 */
	protected static function _getDAOInstanceOf($name)
	{
		if(!self::$_daoInstances[$name])
		{
			self::$_daoInstances[$name] = new DB\DAO(self::_getParam($name));
		}
		
		return self::$_daoInstances[$name];
	}
	
	/**
	 * Crée le cas échéant et retourne une instance de HeFind
	 * @param string $name
	 * @return HeFind 
	 */
	protected static function _getFindInstanceOf($name)
	{
		if(!self::$_findInstances[$name])
		{
			/* Si le fichier n'existe pas, on le crée */
			$findName = '\He\DB\Find\\'.$name;
			if(!class_exists($findName) &&
				!file_exists(FIND_EXTENDS_PATH.'/'.$name.'.php'))
			{
				static::_makeFindExtends(self::_getParam($name));
			}
			
			self::$_findInstances[$name] = new $findName(self::_getParam($name));
		}
		
		return self::$_findInstances[$name];
	}
	
	/**
	 * Crée le cas échéant et retourne une instance de HeFind
	 * @param string $name
	 * @return HeFind 
	 */
	protected static function _getUpdateInstanceOf($name)
	{
		if(!self::$_updateInstances[$name])
		{
			/* Si le fichier n'existe pas, on le crée */
			$updateName = '\He\DB\Update\\'.$name;
			if(!class_exists($updateName) &&
				!file_exists(UPDATE_EXTENDS_PATH.'/'.$name.'.php'))
			{
				static::_makeUpdateExtends(self::_getParam($name));
			}
			
			self::$_updateInstances[$name] = new $findName(self::_getParam($name));
		}
		
		return self::$_updateInstances[$name];
	}
	
	/**
	 * Crée la class étendu \He\DB\Find
	 * @todo mettre le template dans un fichier séparer, et passer par He\Template
	 * @param \He\DB\Param $param paramètres de la table
	 * @return bool 
	 */
	protected static function _makeFindExtends(\He\DB\Param $param)
	{
		$content = '<?php'.PHP_EOL
				.'/**'.PHP_EOL
				.' * Class étendu de \He\DB\Find'.PHP_EOL
				.' * Ajoutez vos méthodes personnalisé pour la récupération de lignes'.PHP_EOL
				.' * de la table '.$param->bdd.'/'.$param->table.' ('.$param->alias.')'.PHP_EOL
				.' * '.PHP_EOL
				.' * @author '.__CLASS__.PHP_EOL
				.' * @version 1'.PHP_EOL
				.' */'.PHP_EOL
				.'namespace He\DB\Find;'.PHP_EOL
				.PHP_EOL
				.'final class '.$param->alias.' extends \He\DB\Find'.PHP_EOL
				.'{'.PHP_EOL
				."\t".'/* Créer ici vos méthodes personnalisés de sélection */'.PHP_EOL
				.'}';
		/* On ne referme pas la balise php ! */
		
		\He\Trace::addTrace('Création de la class étendu de '.$param->alias, get_called_class(), -1);
		if(file_put_contents(\He\Dir::makePath(FIND_EXTENDS_PATH.'/').$param->alias.'.php', $content))
			return true;
		else
			return false;
	}
	
	/**
	 * Crée la class étendu \He\DB\Update
	 * @todo mettre le template dans un fichier séparer, et passer par He\Template
	 * @param \He\DB\Param $param paramètres de la table
	 * @return bool 
	 */
	protected static function _makeUpdateExtends(\He\DB\Param $param)
	{
		$content = '<?php'.PHP_EOL
				.'/**'.PHP_EOL
				.' * Class étendu de \He\DB\Find'.PHP_EOL
				.' * Ajoutez vos méthodes personnalisé pour la mise à jour'.PHP_EOL
				.' * de la table '.$param->bdd.'/'.$param->table.' ('.$param->alias.')'.PHP_EOL
				.' * '.PHP_EOL
				.' * @author '.__CLASS__.PHP_EOL
				.' * @version 1'.PHP_EOL
				.' */'.PHP_EOL
				.'namespace He\DB\Update;'.PHP_EOL
				.PHP_EOL
				.'final class '.$param->alias.' extends \He\DB\Update'.PHP_EOL
				.'{'.PHP_EOL
				."\t".'/* Créer ici vos méthodes personnalisés de sélection */'.PHP_EOL
				.'}';
		/* On ne referme pas la balise php ! */
		
		\He\Trace::addTrace('Création de la class étendu de '.$param->alias, get_called_class(), -1);
		if(file_put_contents(\He\Dir::makePath(UPDATE_EXTENDS_PATH.'/').$param->alias.'.php', $content))
			return true;
		else
			return false;
	}
	
	/**
	 * Récupère les paramètres d'une table, si ils existent
	 * @param type $name
	 * @return type 
	 */
	protected static function _getParam($name)
	{
		if(!self::$_param[$name])
		{
			throw new HeException('La class '.$name.' ne possède pas de paramètres !');
		}
		
		return self::$_param[$name];
	}
	
	/**
	 * Définie les paramètres d'une table
	 * @param He\DB\Param $param
	 * @return bool
	 */
	public static function bindTable(DB\Param $param)
	{
		if(empty(self::$_param[$param->alias]))
		{
			self::$_param[$param->alias] = $param;
			return true;
		}
		else
		{
			throw new \He\Exception('Le paramètre "'.$param->alias.'" a déjà été attribué !');
		}
	}
	
	/**
	 * Récupère la valeur de la dernière ligne enregistré dans la dao demandé
	 * @param string $name
	 * @return mixed
	 */
	public static function lastInsertId($name)
	{
		return self::_getDAOInstanceOf($name)->lastInsertId();
	}
}