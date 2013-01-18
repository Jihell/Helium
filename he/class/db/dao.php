<?php
/**
 * Cette class DOIT être appelé par la factory HeDB
 * Les instance de cette class ainsi crées permettent de mémoriser
 * et analyser les différentes lignes de HeDAORow crées
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 20
 */
namespace He\DB;

final class DAO
{
	/**
	 * Instance de HeDAOParam, pour paramétrer les accès aux différentes tables
	 * @var HeDAOParam
	 */
	private $_param;
	
	/**
	 * Liste des lignes actuellement chargés sur cette table
	 * @var array
	 */
	private $_rows = array();
	
	/**
	 * Nouvelle ligne en cours de création
	 * @var type 
	 */
	private $_newRow;
	
	/**
	 * Valeur courante de la dernière ligne chargé
	 * @var mixed
	 */
	private $_lastInsertId = null;
	
	/**
	 * Interrupteur pour bloquer l'ajout automatique de nouvelle lignes dans la
	 * liste des lignes chargés
	 * @var bool
	 */
	private $_forbidAutoBinding = false;

	/**
	 * Crée la class et ajout ses paramètres
	 * @param HeDAOParam $param
	 */
	public function __construct(Param $param)
	{
		\He\Trace::addTrace("Contruction de la DAO de : ".$param->alias, get_called_class());
		$this->_param = $param;
		$this->_param->setActive();
	}
	
	/**
	 * Crée une nouvelle instance de HeDAORow le cas échéant, et la retourne
	 * @param type $id 
	 */
	public function row($id = array())
	{
		$classAlias = '\He\DB\Row\\'.ucfirst($this->_param->alias);
		
		if(empty($id))
		{
			/* Création du singleton s'il n'existe pas */
			if(empty($this->_newRow))
			{
				/* Création du parent si le fichier n'est pas présent */
				if(!file_exists(DAO_EXTENDS_PATH.'/ref/'.$this->_param->alias.'.php'))
				{
					/**
					 * Si la class dérivé de Row lié à cette table n'existe pas
					 * on la créer puis on charge la class par défaut
					 */
					$this->_makeRowParent();
				}
				/* Création du fichier à éditer */
				if(!class_exists($classAlias, true) && 
				   !file_exists(DAO_EXTENDS_PATH.'/'.$this->_param->alias.'.php'))
				{
					$this->_makeRowExtends();
				}
				$this->_newRow = new $classAlias($this, $this->_param);
			}
			
			return $this->_newRow;
		}
		else
		{
			$id = $id[0];
			if(is_array($id))
				$id_name = implode(',', $id);
			else
				$id_name = $id;
			
			/* Création du singleton s'il n'existe pas */
			if(!$this->_rows[$id_name])
			{
				/* Création du parent si le fichier n'est pas présent */
				if(!file_exists(DAO_EXTENDS_PATH.'/ref/'.$this->_param->alias.'.php'))
				{
					/**
					 * Si la class dérivé de Row lié à cette table n'existe pas
					 * on la créer puis on charge la class par défaut
					 */
					$this->_makeRowParent();
				}
				/* Création du fichier à éditer */
				if(!class_exists($classAlias, true) && 
				   !file_exists(DAO_EXTENDS_PATH.'/'.$this->_param->alias.'.php'))
				{
					$this->_makeRowExtends();
				}
				$this->_rows[$id_name] = new $classAlias($this, $this->_param, $id);
			}
			
			return $this->_rows[$id_name];
		}
	}
	
	/**
	 * Crée le fichier php correspondant à la class étendu définissant les 
	 * getters et setters personnalisés de la table associé à cette DAO
	 * @return $this
	 */
	protected function _makeRowParent()
	{
		/* Création du fichier générique */
		$content = '<?php'.PHP_EOL
			.'/**'.PHP_EOL
			.' * Class étendu de \He\DB\Row'.PHP_EOL
			.' * Ne touchez pas à cette class, elle sera régénéré lors de la purge du cache !'.PHP_EOL
			.' * Représentation objet d\'une ligne de la table '.$this->_param->bdd.'/'.$this->_param->table.' ('.$this->_param->alias.') '.PHP_EOL
			.' * '.PHP_EOL
			.' * @author: He@'.__CLASS__.''.PHP_EOL
			.' * @version: 1'.PHP_EOL
			.' */'.PHP_EOL
			.'namespace He\DB\Row\Ref;'.PHP_EOL
			.PHP_EOL
			.'abstract class '.ucfirst($this->_param->alias).' extends \He\DB\Row'.PHP_EOL
			.'{'.PHP_EOL;
		
		/* Ajout des getter */
		foreach($this->_param->colsField AS $field)
		{
			$type = $this->_param->colsType[$this->_param->colsPosition[$field]];
			
			$content .= "\t".'/**'.PHP_EOL
						."\t".' * Renvoi la valeur courante du champ '.$field.PHP_EOL
						."\t".' * @return '.$type.PHP_EOL
						."\t".' */'.PHP_EOL
						."\t".'public function get'.ucfirst($field).'()'.PHP_EOL
						."\t".'{'.PHP_EOL
						."\t"."\t".'return $this->_get(\''.$field.'\');'.PHP_EOL
						."\t".'}'.PHP_EOL
						.PHP_EOL;
		}
		
		/* Ajout des setters */
		foreach($this->_param->colsField AS $field)
		{
			$type = $this->_param->colsType[$this->_param->colsPosition[$field]];
			
			$content .= "\t".'/**'.PHP_EOL
						."\t".' * Renvoi la valeur courante du champ '.$field.PHP_EOL
						."\t".' * @param '.$type.' $value'.PHP_EOL
						."\t".' * @return $this'.PHP_EOL
						."\t".' */'.PHP_EOL
						."\t".'public function set'.ucfirst($field).'($value)'.PHP_EOL
						."\t".'{'.PHP_EOL
						."\t"."\t".'$this->_set(\''.$field.'\', $value);'.PHP_EOL
						."\t"."\t".'return $this;'.PHP_EOL
						."\t".'}'.PHP_EOL
						.PHP_EOL;
		}
		
		if($this->_param->joinAlias && $this->_param->join) 
		{
			/* Ajout des jointures avec alias */
			if($this->_param->joinAlias)
			foreach($this->_param->joinAlias AS $table => $details)
					$content .= "\t".'/**'.PHP_EOL
								."\t".' * Jointure avec alias vers la table '.$table.PHP_EOL
								."\t".' * @return \He\DB\Row\\'.$table.PHP_EOL
								."\t".' */'.PHP_EOL
								."\t".'public function join'.ucfirst($table).'()'.PHP_EOL
								."\t".'{'.PHP_EOL
								."\t"."\t".'return $this->_join(\''.$table.'\');'.PHP_EOL
								."\t".'}'.PHP_EOL
								.PHP_EOL;

			/* Ajout des jointures */
			if($this->_param->join)
				foreach($this->_param->join AS $table => $details)
					if(!isset($this->_param->noJoin[$details['col']]))
						$content .= "\t".'/**'.PHP_EOL
									."\t".' * Jointure automatique vers la table '.$table.PHP_EOL
									."\t".' * @return \He\DB\Row\\'.$table.PHP_EOL
									."\t".' */'.PHP_EOL
									."\t".'public function join'.ucfirst($table).'()'.PHP_EOL
									."\t".'{'.PHP_EOL
									."\t"."\t".'return $this->_join(\''.$table.'\');'.PHP_EOL
									."\t".'}'.PHP_EOL
									.PHP_EOL;
		}
		
		/* Ajout des dépendances */
		if(count($this->_param->dependance) > 0)
		{
			foreach($this->_param->dependance AS $table => $column)
				$content .= "\t".'/**'.PHP_EOL
							."\t".' * Dépendance vers la table '.$table.PHP_EOL
							."\t".' * @return array(\He\DB\Row\\'.$table.')'.PHP_EOL
							."\t".' */'.PHP_EOL
							."\t".'public function list'.ucfirst($table).'()'.PHP_EOL
							."\t".'{'.PHP_EOL
							."\t"."\t".'return $this->_list(\''.$table.'\');'.PHP_EOL
							."\t".'}'.PHP_EOL
							.PHP_EOL;
		}
		
		$content .= '}'; 
		/* On ne referme pas la balise php ! */
		
		\He\Trace::addTrace('Création de la class parent étendu de '.$this->_param->alias, get_called_class(), -1);
		file_put_contents(\He\Dir::makePath(DAO_EXTENDS_PATH.'/ref/').$this->_param->alias.'.php', $content);
		return $this;
	}
	
	/**
	 * Crée le fichier php correspondant à la class étendu définissant les 
	 * getters et setters personnalisés de la table associé à cette DAO
	 * @return $this
	 */
	protected function _makeRowExtends()
	{
		/* Création du fichier générique */
		$content = '<?php'.PHP_EOL
			.'/**'.PHP_EOL
			.' * Class étendu de \He\DB\Row'.PHP_EOL
			.' * Vous pouvez ajouter ici les méthodes personnalisés'.PHP_EOL
			.' * Représentation objet d\'une ligne de la table '.$this->_param->bdd.'/'.$this->_param->table.' ('.$this->_param->alias.') '.PHP_EOL
			.' * '.PHP_EOL
			.' * @author: He@'.__CLASS__.''.PHP_EOL
			.' * @version: 1'.PHP_EOL
			.' */'.PHP_EOL
			.'namespace He\DB\Row;'.PHP_EOL
			.PHP_EOL
			.'final class '.ucfirst($this->_param->alias).' extends \He\DB\Row\Ref\\'.ucfirst($this->_param->alias).PHP_EOL
			.'{'.PHP_EOL
			."\t".'/**'.PHP_EOL
			."\t".' * Ajoutez vos getters personnalisés ici.'.PHP_EOL
			."\t".' * Liste des variables : '.PHP_EOL;
		
		/* Ajout des getter */
		foreach($this->_param->colsField AS $field)
		{
			$type = $this->_param->colsType[$this->_param->colsPosition[$field]];
			
			$content .= "\t".' * @var '.$field.PHP_EOL;
		}
		
		if($this->_param->joinAlias && $this->_param->join) 
		{
			$content .= "\t".' * '.PHP_EOL;
			$content .= "\t".' * Liste des jointures : '.PHP_EOL;
		
			/* Ajout des jointures avec alias */
			if($this->_param->joinAlias)
				foreach($this->_param->joinAlias AS $table => $details)
					$content .= "\t".' * @join '.$table.' => '.$details['col'].'@'.$details['target'].PHP_EOL;

			/* Ajout des jointures */
			if($this->_param->join)
				foreach($this->_param->join AS $table => $details)
					if(!isset($this->_param->noJoin[$details['col']]))
						$content .= "\t".' * @join '.$table.PHP_EOL;
		}
		
		/* Ajout des dépendances */
		if(count($this->_param->dependance) > 0)
		{
			$content .= "\t".' * '.PHP_EOL;
			$content .= "\t".' * Liste des dépendances : '.PHP_EOL;
			
			foreach($this->_param->dependance AS $table => $column)
				$content .= "\t".' * @depend '.$table.PHP_EOL;
		}
		
		$content .= "\t".' */'.PHP_EOL
					.'}';
		/* On ne referme pas la balise php ! */
		
		\He\Trace::addTrace('Création de la class étendu de '.$this->_param->alias, get_called_class(), -1);
		file_put_contents(\He\Dir::makePath(DAO_EXTENDS_PATH.'/').$this->_param->alias.'.php', $content);
		return $this;
	}
	
	/**
	 * 
	 * @param type $list 
	 */
	public function bindList($list)
	{
		\He\Trace::addTrace('Début d\'ajout des nouvelles lignes', get_called_class());
		
		$className = '\He\DB\Row\\'.ucfirst($this->_param->alias);
		
		/* Création du parent si le fichier n'est pas présent */
		if(!file_exists(DAO_EXTENDS_PATH.'/ref/'.$this->_param->alias.'.php'))
		{
			/**
			 * Si la class dérivé de Row lié à cette table n'existe pas
			 * on la créer puis on charge la class par défaut
			 */
			$this->_makeRowParent();
		}
		/* Création du fichier à éditer */
		if(!class_exists($classAlias, true) && 
		   !file_exists(DAO_EXTENDS_PATH.'/'.$this->_param->alias.'.php'))
		{
			$this->_makeRowExtends();
		}
				
		$this->_forbidAutoBinding = true;
		$rows = $list->fetchAll(\He\PDO::FETCH_CLASS | \He\PDO::FETCH_PROPS_LATE, $className, array($this, $this->_param));
		$this->_forbidAutoBinding = false;
		\He\Trace::addTrace('Ajout effectué, début de traitement des nouvelle lignes', get_called_class());
		if(count($rows) > 0)
		{
			foreach($rows AS $key => $nrow)
			{
				if(!$this->_rows[$nrow->getIdentifier()])
				{
					$this->_rows[$nrow->getIdentifier()] = $nrow;
					$rows[$key] = $this->_rows[$nrow->getIdentifier()];
				}
			}
			\He\Trace::addTrace('Traitement effectué, retour de la liste', get_called_class());
			return $rows;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Enregistrement d'une nouvelle ligne
	 * @param int $id
	 * @param HeDAORow $row 
	 * @return HeDAORow
	 */
	public function bindRow($id, $row)
	{
		if($this->_rows[$id] != $row && !$this->_forbidAutoBinding)
		{
			\He\Trace::addTrace("Ajout d'une nouvelle ligne pk : ".$id, get_called_class());
			$this->_rows[$id] = $row;
			$this->_newRow = null;
			$this->setLastInsertId($id);
		}
		return $row;
	}
	
	/**
	 * Définie la dernière ID rentrée
	 * @param mixed $id 
	 */
	public function setLastInsertId($id)
	{
		$this->_lastInsertId = $id;
	}
	
	/**
	 * Renvoi l'id de la dernière ligne sauvegardé
	 * @return mixed
	 */
	public function lastInsertId()
	{
		return $this->_lastInsertId;
	}
}