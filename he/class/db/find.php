<?php
/**
 * Class de listage par défaut d'une table, à hériter aux classes de listage.
 * COntient les méthodes de sélection basique.
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\DB;

class Find
{
	/**
	 * Liste des paramètes de la table
	 * @var \He\DB\Param
	 */
	protected $_param;
	
	/**
	 * Début de requète de sélection standar
	 * @var string
	 */
	protected $_selectString;
	
	/**
	 * Début de requète de comptage standar
	 * @var string
	 */
	protected $_countString;
	
	/**
	 * Début de requète de récupération du max standar
	 * @var string
	 */
	protected $_maxString;
	
	/**
	 * Active les paramètres de la table
	 * @param \He\DB\Param $param paramètre de la DAO
	 */
	public function __construct(\He\DB\Param $param)
	{
		\He\Trace::addTrace("Contruction de la Find de : ".$param->alias, get_called_class());
		$this->_param = $param;
		$this->_param->setActive();

		$this->_sth = &\He\DB\Sth::getCursor($param->bdd, $param->table);
	}
	
	/**
	 * Crée le début de requète de sélection sous la forme 
	 * "SELECT [t.listeDesColonnes] FROM [maTable] AS t ";
	 * @return string
	 */
	protected function _select()
	{
		if(empty($this->_selectString))
			$this->_selectString = 'SELECT '
				.$this->_param->listColT
				.' FROM '.$this->_param->table.' AS t ';
		return $this->_selectString;
	}
	
	/**
	 * Crée le début de requète de comptage sous la forme
	 * "SELECT COUNT(t.[nomDesPrimary]) FROM [maTable] AS t "
	 * @return string
	 */
	protected function _count()
	{
		if(empty($this->_countString))
			$this->_countString = 'SELECT COUNT(*) FROM '.$this->_param->table.' AS t ';
		return $this->_countString;
	}
	
	/**
	 * Crée le début de requète de comptage sous la forme
	 * "SELECT COUNT(t.[nomDesPrimary]) FROM [maTable] AS t "
	 * @return string
	 */
	protected function _max()
	{
		if(empty($this->_maxString))
			$this->_maxString = 'SELECT MAX('
				.$this->_param->listColTPrimary
				.') FROM '.$this->_param->table.' AS t ';
		return $this->_maxString;
	}
	
	/**
	 * Créer la requète de chargement de la liste et l'enregistre à travers
	 * le crc32 du sélecteur. Le sélecteur peut prendre la forme d'une clause
	 * WHERE ou l'ajout de jointures / group by etc.
	 * @param string $selector
	 * @param array $param Liste des paramètres à passer au \He\PDOStatement
	 *					   à l'exécution de la requète
	 * @return array		Liste d'objet \He\DAO\Row || extends
	 */
	protected function _makeLoadRequest($selector = '', $param = array())
	{
		$hash = crc32('load'.$selector);
		if(!$this->_sth[$hash])
		{
			$sql = $this->_select()
				.$selector;

			$this->_sth[$hash] = \He\PDO::getInstance($this->_param->bdd)->prepare($sql);
		}
		
		try
		{
			\He\Trace::addTrace('Listing dans '.$this->_param->alias.' avec pour sélecteur '.$selector, get_called_class());
			$this->_sth[$hash]->execute($param);
			return \He\DB::dao($this->_param->alias)->bindList($this->_sth[$hash]);
		}
		catch(\PDO\Exception $e)
		{
			throw new \He\Exception('Impossible d\'exécuter la requète '.$sql
					.' : '.$e->getMessage());
		}
	}
	
	/**
	 * Créer la requète de comptage de la liste et l'enregistre à travers
	 * le crc32 du sélecteur. Le sélecteur peut prendre la forme d'une clause
	 * WHERE ou l'ajout de jointures / group by etc.
	 * @param string $selector
	 * @param array $param Liste des paramètres à passer au \He\PDOStatement
	 *					   à l'exécution de la requète
	 * @return int
	 */
	protected function _makeCountRequest($selector = '', $param = array())
	{
		$hash = crc32('count'.$selector);
		if(!$this->_sth[$hash])
		{
			$sql = $this->_count()
				.$selector;

			$this->_sth[$hash] = \He\PDO::getInstance($this->_param->bdd)->prepare($sql);
		}
		
		try
		{
			\He\Trace::addTrace('Comptage dans '.$this->_param->alias.' avec pour sélecteur '.$selector, get_called_class());
			$this->_sth[$hash]->execute($param);
			return $this->_sth[$hash]->fetchColumn();
		}
		catch(\PDO\Exception $e)
		{
			throw new \He\Exception('Impossible d\'exécuter la requète '.$sql
					.' : '.$e->getMessage());
		}
	}
	
	/**
	 * Créer la requète de récupération de la valeur maximum de la colonne
	 * sélectionné selon les critères passé au sélecteur.
	 * Le sélecteur peut prendre la forme d'une clause
	 * WHERE ou l'ajout de jointures / group by etc.
	 * @param string $selector
	 * @param array $param Liste des paramètres à passer au \He\PDOStatement
	 *					   à l'exécution de la requète
	 * @return int
	 */
	protected function _makeMaxRequest($selector = '', $param = array())
	{
		$hash = crc32('max'.$selector);
		if(!$this->_sth[$hash])
		{
			$sql = $this->_max()
				.$selector;

			$this->_sth[$hash] = \He\PDO::getInstance($this->_param->bdd)->prepare($sql);
		}
		
		try
		{
			\He\Trace::addTrace('Maximum dans '.$this->_param->alias.' avec pour sélecteur '.$selector, get_called_class());
			$this->_sth[$hash]->execute($param);
			return $this->_sth[$hash]->fetchColumn();
		}
		catch(\PDO\Exception $e)
		{
			throw new \He\Exception('Impossible d\'exécuter la requète '.$sql
					.' : '.$e->getMessage());
		}
	}
	
	/**
	 * =========================================================================
	 * Méthodes standard
	 * =========================================================================
	 * 
	 * Les émthodes standard sont des alias vers les requètes les plus courante
	 * pour accéder à une table de donnée.
	 */
	
	/**
	 * Charge et liste toute les lignes de la table
	 * @param string $order // ASC ou DESC
	 * @return array()
	 */
	public function loadAll($order = 'ASC')
	{
		return $this->_makeLoadRequest('ORDER BY '.$this->_param->listColTPrimary.' '.$order);
	}
	
	/**
	 * Charge et liste toute les lignes de la table selon l'ordre demandé
	 * @param string $orderBy // Liste des colonnes de tri et leur sens
	 * @return array()
	 */
	public function loadAllOrderBy($orderBy)
	{
		return $this->_makeLoadRequest('ORDER BY '.$this->_param->listColTPrimary);
	}
	
	/**
	 * Charge et liste toute les lignes répondant au criètes de sélection
	 * @param string $selector clause de sélection sans le WHERE
	 * @param string $order ASC ou DESC
	 */
	public function loadOnSelector($selector, $order = 'ASC')
	{
		return $this->_makeLoadRequest('WHERE '.$selector.' ORDER BY '.$this->_param->listColTPrimary.' '.$order);
	}
	
	/**
	 * Charge $size ligne à partir de la ligne $needle.
	 * Cette méthode peu s'utiliser pour un système de pagination
	 * @param int $needle
	 * @param int $size
	 * @return \He\DB\Row | extends 
	 */
	public function loadLimit($needle = 0, $size = 1, $order = 'ASC')
	{
		return $this->_makeLoadRequest('ORDER BY '.$this->_param->listColTPrimary.' '.$order.' LIMIT '.$needle.','.$size);
	}
	
	/**
	 * Charge et liste toute les lignes répondant au criètes de sélection
	 * @param string $selector clause de sélection sans le WHERE
	 * @param string $order ASC ou DESC
	 */
	public function loadLimitOnSelector($needle = 0, $size = 1, $selector, $order = 'ASC')
	{
		return $this->_makeLoadRequest('WHERE '.$selector.' ORDER BY '.$this->_param->listColTPrimary.' '.$order.' LIMIT '.$needle.','.$size);
	}
	
	/**
	 * Compte le nombre de ligne dans la table
	 * @return int
	 */
	public function countAll()
	{
		return $this->_makeCountRequest();
	}
	
	/**
	 * Compte le nombre de ligne dans la table
	 * @return int
	 */
	public function countOnSelector($selector, $param = array())
	{
		return $this->_makeCountRequest('WHERE '.$selector, $param);
	}
	
	/**
	 * Récupère la valeur maximum de la primary de cette table
	 * @return int
	 */
	public function getMax()
	{
		return $this->_makeMaxRequest();
	}
	
	/**
	 * Test si la ligne recherché existe
	 * @param array $id liste de sélecteur, directement lié au nombre de primary
	 * @return bool 
	 */
	public function exist($id = array())
	{
		/* Si on à une seule colonne en primary */
		if(!is_array($id) && count($this->_param->primary) == 1)
		{
			$id = array(':'.$this->_param->primary[0] => $id);
		}
		elseif(is_array($id))
		{
			foreach($id AS $row => $val)
			{
				if($row{0} != ':')
					$new_id[':'.$this->_param->primary[$row]] = $val;
			}
			$id = $new_id;
		}
		else
		{
			\He\Trace::addTrace('Il faut préciser un tableau de valeur en cas de clef primaire multiple !', get_called_class(), -2);
			throw new \He\Exception('Il faut préciser un tableau de valeur en cas de clef primaire multiple !');
			return false;
		}
		
		if($this->_makeCountRequest('WHERE '.$this->_param->listColTWherePrimary, $id) > 0)
			return true;
		else
			return false;
	}
}