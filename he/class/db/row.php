<?php
/**
 * Class possédants les méthode utilile à UNE SEULE LIGNE de la DAO
 * TODO : Ajouter la gestion des clefs primaires multiple pour les tables de liaisons
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 2
 */
namespace He\DB;

class Row
{
	/**
	 * DAO rataché à cette ligne
	 * @var \He\DB\DAO
	 */
	private $_parent;
	
	/**
	 * Liste des paramètres de la table
	 * @var \He\DB\Param
	 */
	private $_param;
	
	/**
	 * indique si la ligne est bien présente physiquement en base
	 * @var bool
	 */
	private $_exist;
	
	/**
	 * Tableau des objets \He\PDOStatement de chargement / supressione etc...
	 * @var array
	 */
	private $_sth = array();
	
	/**
	 * Objet \He\DB\Pending stockant les requète de sauvegarde de masse
	 * @var array 
	 */
	private $_pending = null;
	
	/**
	 * Liste des valeurs
	 * @var type 
	 */
	private $_data = array();
	
	public function __construct($parent, $param, $id = null)
	{
		$this->_parent = $parent;
		$this->_param = $param;
		
		$this->_sth = &Sth::getCursor($param->bdd, $param->table);
		$this->_pending = &Pending::getCursor($param->bdd, $param->table);
		
		if($id)
			$this->_load($id);
	}
	
	/**
	 * Charge la ligne correspondant au sélecteur inséré. Supporte la charge
	 * de cclef primaire multiple
	 * @param mixed $id selon la table. Peut prendre en charge les arrays
	 * @return bool 
	 */
	private function _load($id)
	{	
		if(!is_array($id) && count($this->_param->primary) == 1)
		{
			$column = empty($this->_param->load['column']) ? $this->_param->primary[0] : $this->_param->load['column'];
			$order = empty($this->_param->load['order']) ? $this->_param->primary[0] : $this->_param->load['order'];
		}
		elseif(count($id) == count($this->_param->primary))
		{
			$column = empty($this->_param->load['column']) ? $this->_param->primary : $this->_param->load['column'];
			$order = empty($this->_param->load['order']) ? $this->_param->primary : $this->_param->load['order'];
		}
		else
		{
			Throw new \He\Exception('ERREUR FATALE : Pas assez de paramètres pour chargé la ligne. On attend '.count($this->_param->primary).' paramètres !');
		}
		
		$sth = $this->_prepareLoad($column, $order, $this->_param->load['selector']);
		
		/* Exécution de la requète préparée */
		$sth->execute($this->_makeParam($id, $column));
		$answer = $sth->fetchAll(\He\PDO::FETCH_ASSOC);
		
		if(!empty($answer))
		{
			$this->_exist = true;
			
			foreach($answer AS $row => $val)
			{
				$this->_data = $val;
			}
			
			return true;
		}
		else
		{
			$this->_exist = false;
			
			if(!is_array($id))
			{
				\He\Trace::addTrace('Ligne '.$id.' non trouvé en base, '
						.'on définie la colonne '.$column, 
						get_called_class());
				
				$this->_data[$column] = $id;
				
				return false;
			}
			else
			{
				\He\Trace::addTrace('Ligne '.print_r($id, 1).' non trouvé en '
						.'base, on définie les colonnes '.print_r($column, 1), 
						get_called_class());
				
				foreach($id AS $row => $value)
					$this->_data[$column[$row]] = $value;
				
				return false;
			}
		}
	}
	
	/**
	 * Créer le tableau de paramètre de la requète préparé
	 * @param mixed $column
	 * @param mixed $order
	 * @return array
	 */
	private function _makeParam($id, $column)
	{
		$param = array();
		
		/* Gestion des tableau de colonne de sélection */
		if(is_array($column))
		{
			foreach($column AS $row => $name)
				$param[':'.$name] = $id[$row];
		}
		else
		{
			$param[':'.$column] = $id;
		}
		
		return $param;
	}
	
    /**
     * Création d'une requète préparé pour lire la ligne
     * @return PDOStatement
     */
	private function _prepareLoad($column, $order, $selector)
	{
		if(!$this->_sth['load'])
		{
			\He\Trace::addTrace('Création de la requète préparée de chargement d\'ID', 
					get_called_class());
			$sql = 'SELECT '
				.$this->_param->listCol
				.' FROM '.$this->_param->table
				.' WHERE ';
			
			/* Gestion des tableau de colonne de sélection */
			if(is_array($column))
			{
				foreach($column AS $row => $name)
					$sql .= $name.' = :'.$name.' AND ';
			}
			else
			{
				$sql .= $column.' = :'.$column.' AND '; // Le AND sert à se faire suprimer
			}
			
			$sql = substr($sql, 0, -4).' '.$selector.' ORDER BY ';
			
			/* Gestion des tableau de colonne de tri */
			if(is_array($order))
			{
				foreach($order AS $row => $name)
					$sql .= $name.', ';
			}
			else
			{
				$sql .= $order.', '; // La virgule sert à se faire suprimer
			}
			
			$sql = substr($sql, 0, -2).' DESC LIMIT 0,1';
			
			$this->_sth['load'] = \He\PDO::getInstance($this->_param->bdd)->prepare($sql);
		}
		
		return $this->_sth['load'];
	}
	
    /**
     * Création d'une requète préparé pour sauvegarder la ligne
     * @return PDOStatement
     */
    private function _prepareSave()
	{
		if(!$this->_sth['save'])
		{
			\He\Trace::addTrace('Création de la requète préparée de sauvegarde', 
					get_called_class());
			$sql = 'REPLACE INTO '.$this->_param->table
					.' VALUES ('.$this->_param->listColSth.');';
	
			$this->_sth['save'] = \He\PDO::getInstance($this->_param->bdd)->prepare($sql);
		}
		
		return $this->_sth['save'];
    }
	
	private function _prepareDel()
	{
		if(!$this->_sth['del'])
		{
			\He\Trace::addTrace('Création de la requète préparée de supression', 
					get_called_class());
			$sql = 'DELETE FROM '.$this->_param->table.' WHERE ';
			
			foreach($this->_param->primary AS $row => $name)
					$sql .= $name.' = :'.$name.', ';
			
			$sql = substr($sql, 0, -2);
			
			$this->_sth['del'] = \He\PDO::getInstance($this->_param->bdd)->prepare($sql);
		}
		
		return $this->_sth['del'];
	}
	
	/**
	 * Empèche l'accès aux propriété innexistante de l'objet en 
	 * renvoyant null. En pratique cette méthode ne doit jamais être appelé.
	 * Sinon c'est qu'il y a un problème dans la structure du programme
	 * @param	string	$name	Nom de la variable à récupérer
	 * @return	null
	 */
	public function __get($name)
	{
		if(DEBUG)
			throw new \He\Exception('On essaie d\'accéder à une propriété privé : "'
					.$name.'" d\'une des lignes de '.$this->_param->alias);
		else
			return NULL;
	}
	
	/**
	 * Empèche l'accès en écriture aux propriété innexistante de l'objet en 
	 * renvoyant null. En pratique cette méthode ne doit jamais être appelé.
	 * Sinon c'est qu'il y a un problème dans la structure du programme.
	 * 
	 * ATTENTION : cette méthode sera toujours appelé quand on créer une instance
	 * depuis PDO, c'est pour cela que la méthode vérifie si la colonne
	 * existe dans $this->_param et qu'elle est vide pour autoriser 
	 * exceptionnellement l'accès à la propriété en écriture.
	 * 
	 * @param	string	$name	Nom de la variable à récupérer
	 * @return	NULL
	 */
	public function __set($name, $value)
	{
		/* Si cette variable est bien en BDD, et qu'elle n'est est null */
		if($this->_param->colsPosition[$name] &&
			is_null($this->_data[$name]))
		{
			$this->_set($name, $value);
		}
		else
		{
			if(DEBUG)
				throw new \He\Exception('On essaie d\'accéder en écriture à une propriété privé : "'
						.$name.'" d\'une des lignes de '.$this->_param->alias);
			else
				return NULL;
		}
	}
	
	/**
	 * Renvois un identifiant unique basé sur les valeur courantes des clefs 
	 * primaires
	 * @return string
	 */
	public function getIdentifier()
	{
		/* Si on a bien un clef primaire */
		if(!empty($this->_param->primary))
		{
			foreach($this->_param->primary AS $key)
			{
				$send .= $this->_data[$key].',';
			}
		}
		/* Sinon on prend la chaine de toute les valeurs */
		else
		{
			$send = $this->_listValues(true);
		}
		return $send;
	}
	
	/**
	 * Récupère un dump des valeurs courante des colonnes de cette ligne sous
	 * forme de tableau indexé [nom] => valeur. Ce tableau est directement
	 * exploitable par \He\Template->setArrayToNode
	 * @return array
	 */
	public function getAll()
	{
		foreach($this->_param->colsField AS $name)
		{
			$send[$name] = $this->_data[$name];
		}
		return $send;
	}
	
	/**
	 * Renvoi l'indicateur de présence physique en bdd de la ligne.
	 * @return bool
	 */
	public function exist()
	{
		return $this->_exist;
	}
	
	protected function _get($name)
	{
		/* Si cette variable est bien en BDD */
		if($this->_param->colsPosition[$name])
		{
			/**
			 * Ajouter ici des traitement suplémentaires sur les variables
			 * tel que des appels à \He\Cast
			 * Pour rappel, le type de variable appelé est stocké dans 
			 * $this->_param->colsType[$name]
			 */
			return $this->_data[$name];
		}
		else
		{
			throw new \He\Exception('On tente d\'accéder avec un getter à une '
					.'propriété innexistante de '.$this->_param->alias.' : '.$name);
			return false;
		}
	}
	
	/**
	 * Recherche et définie dans _data les valeur à enregistrer dans l'objet
	 * @param string	$name	Nom de la variable
	 * @param mixed		$value	Valeur de la variable
	 */
	protected function _set($name, $value)
	{
		/* Si on donne un ID à notre class, on la rajoute à la liste des instances */
		if(in_array($name, $this->_param->primary))
			$this->_parent->bindRow($value, $this);
		
		/* Si cette variable est bien en BDD */
		if($this->_param->colsPosition[$name])
		{
			if(in_array($name, $this->_param->primary) && !empty($this->_data[$name]))
				throw new \He\Exception('ERREUR FATALE : On ne peu pas redéfinir '
						.'une clef de contrainte une fois définie !');
			
			/* Attribution de la valeur */
			if($this->_param->colsType[$this->_param->colsPosition[$name]] == 'date' &&
				strtolower($value) == 'now()')
				$value = date('Y-m-d H:i:s');
			
			$this->_data[$name] = $value;
		}
		else
		{
			/* Si la variable n'existe pas */
			throw new \He\Exception('Impossible de donner la valeur "'.$value
					.'" car la colonne "'.$name
					.'" n\'existe pas : "'.print_r($this->_data,1).'!');
		}
	}
	
	/**
	 * Appel aux getters, setters, joiner
	 * Les getters renvoient la valeur demandé
	 * Les setters renvoient $this
	 * Les joiner renvoient une instance de \He\DB\Row (ou extention)
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		if(substr($name, 0, 3) == 'get')
		{
			$var = strtolower(substr($name, 3));
			return $this->_get($var);
		}
		elseif(substr($name, 0, 3) == 'set')
		{
			$var = strtolower(substr($name, 3));
			$this->_set($var, $arguments[0]);
			return $this;
		}
		elseif(substr($name, 0, 4) == 'join')
		{
			return $this->_join(strtolower(substr($name, 4)));
		}
		elseif(substr($name, 0, 4) == 'list')
		{
			return $this->_list(strtolower(substr($name, 4)));
		}
	}
	
    /**
     * Liste les variables de l'objet dans un tableau
     * Syntaxe faite pour \He\PDO::Prepare
     * @return: array   (nomVariable => valeur)
     */
    private function _listVar()
	{
		foreach($this->_param->colsField AS $row => $name)
		{
			$list[':'.$name] = $this->_data[$name];
		}
		return $list;
    }
	
	/**
     * Liste les variables de l'objet dans un tableau
     * Syntaxe faite pour HeTemplate::SetArrayToNode
     * @return: array   (nomVariable => valeur)
     */
    private function _listValues($justList = false)
	{
		/* Listing des variables dans l'ordre */
		foreach($this->_param->colsField AS $row => $name)
		{
			$send .= \He\PDO::bind($this->_data[$name]).',';
		}
		if($justList)
			return $send;
		else
			return '('.substr($send,0,-1).')';
    }
	
	/**
	 * Joint les informations d'une colonne commençant par "id" 
	 * à la table correspondante
	 * @param string $table nom de la table
	 * @return \He\DB\Row
	 * @throw \He\Exception
	 */
	protected function _join($table)
	{
		$table = strtolower($table);
		
		if(isset($this->_param->joinAlias[$table]))
		{
			$col = $this->_param->joinAlias[$table]['col'];
			$target = $this->_param->joinAlias[$table]['target'];
			
			return \He\DB::$target($this->_data[$col]);
		}
		elseif(isset($this->_param->join[$table]))
		{
			$col = $this->_param->join[$table]['col'];
			$target = $this->_param->join[$table]['target'];
			
			return \He\DB::$target($this->_data[$col]);
		}
		else
		{
			throw new \He\Exception("Jointure innexistante vers ".$table."!");
		}
	}
	
	/**
	 * Joint cette table vers une table dépendante de celle-ci. On utilisite
	 * donc la méthode \He\DB::maTable()->find([...])
	 * @param string $table nom de la table
	 */
	protected function _list($table)
	{
		if(count($this->_param->primary) > 1)
			throw new \He\Exception("Jointure automatique de dépendance "
	."impossible car cette table ".$table." possède plusieur clefs primaire !");
		
		if(isset($this->_param->dependance[$table]))
		{
			$col = $this->_param->dependance[$table];
			$selector = $col.' = '.$this->getId();
			return \He\DB::find($table)->loadOnSelector($selector);
		}
		else
		{
			throw new \He\Exception("Dépendance innexistante vers ".$table."!");
		}
	}
	
	/**
	 * Vide les données de cet objet
	 * @return $this
	 */
	protected function _flush()
	{
		$this->_data = array();
		return $this;
	}
	
	/**
	 * Supprime la ligne de la base et du DAO
	 */
	public function del()
	{
		$param = array();
		foreach($this->_param->primary AS $row => $name)
			$param[':'.$name] = $this->_data[$name];
		
		\He\Trace::addTrace("Supression de la ligne ".print_r($param[':'.$name.$row], 1)."...", get_called_class());
		
		$this->_prepareDel()->execute($param);
		$this->_exist = false;
		\He\Trace::addTrace("Effectué !", get_called_class());
		unset($this);
	}
	
	/**
     * Fonction de sauvegarde des objets ayant été chargé / créer avec _load()
     * @return	int	Nombre d'enregistrements
     */
    private function _save()
	{
		try
		{
			/* Enchainement a commiter */
			\He\PDO::getInstance($this->_param->bdd)->beginTransaction();

			$nb_row_affected = $this->_prepareSave()->execute($this->_listVar());
			
			$lastInsertId = \He\PDO::getInstance($this->_param->bdd)->lastInsertId();
			if(!empty($lastInsertId) && count($this->_param->primary) == 1)
			{
				\He\Trace::addTrace('Dernier ID ajouté en base de la table '.$this->_param->alias.' : '.$lastInsertId, get_called_class(), 1);
				$this->_data[$this->_param->primary[0]] = $lastInsertId;
				$this->_parent->bindRow($lastInsertId, $this);
			}

			\He\Trace::addTrace('Commit de la sauvegarde ...', get_called_class());
			\He\PDO::getInstance($this->_param->bdd)->commit();
			$this->_exist = true;
			\He\Trace::addTrace('Sauvegarde exécutée', get_called_class());
			return $nb_row_affected;
		}
		catch (\PDOException $e)
		{
			\He\PDO::getInstance($this->_param->bdd)->rollBack();
			throw new \He\Exception('Impossible de sauver l\'objet !'.$e->getMessage());
		}
    }
	
	/**
     * Fonction de sauvegarde publique, si on effectue une sauvegarde de masse
	 * et que cet objet est un nouvel objet, on le vide
	 * @param	bool	$mass_storage	Force l'enregistrement en mass insert
     * @return	int		Nombre d'enregistrements
     */
    public function stor($mass_storage = false)
	{
		if($mass_storage)
		{
			/* On parse la requète en plein de petites requète pour limiter le
			risque de crash */
			if($this->_pending["nbrow"] >= Pending::getLimit())
			{
				$this->_pending['cursor']++;
				$this->_pending["nbrow"] = 0;
			}
			
			/* Si on a déjà des requète en cours d'écriture, on rajoute à la suite */
			if(!empty($this->_pending['query'][$this->_pending['cursor']]))
				$this->_pending['query'][$this->_pending['cursor']] .= ',';
			
			/* Ajout des valeurs courantes */
			$this->_pending['query'][$this->_pending['cursor']] .= $this->_listValues();
			$this->_pending["nbrow"]++;
			/* Si c'est une nouvelle ligne, on la vide */
			if(!$this->_exist)
				$this->_flush();
		}
		else
		{
			if($this->_save(true))
				return false;
			else
				return true;
		}
    }
	
	/**
	 * Vide le cache du stockage de masse via une requète d'insert dans la table
	 * @return bool
	 */
	public function massStorage($no_dao_binding = false)
	{
		if(!empty($this->_pending))
		{
			foreach($this->_pending["query"] AS $query)
			{
				/* Sauvegarde des requètes en attente */
				try
				{
					/* Enchainement a commiter */
					\He\PDO::getInstance($this->_param->bdd)->beginTransaction();

					$sql = 'REPLACE INTO '.$this->_param->table.' VALUES'.$query.';';
					
					\He\PDO::getInstance($this->_param->bdd)->prepare($sql)->execute();
					
					$lastInsertId = \He\PDO::getInstance($this->_param->bdd)->lastInsertId();

					\He\Trace::addTrace('Dernier ID ajouté en base de la table '.$this->_param->alias.' : '.$lastInsertId, get_called_class(), 1);
					/**
					 * Protection contre les erreur lors des appel pendant la
					 * séquence de destruction
					 */
					if(!$no_dao_binding)
						$this->parent->setLastInsertId($lastInsertId);

					\He\Trace::addTrace('Commit de la sauvegarde de masse ...', get_called_class());
					\He\PDO::getInstance($this->_param->bdd)->commit();
					\He\Trace::addTrace('Sauvegarde exécutée', get_called_class());
				}
				catch (\PDOException $e)
				{
					\He\PDO::getInstance($this->_param->bdd)->rollBack();
					throw new \He\Exception('Impossible d\'exécuter la sauvegarde de masse : <br/>'.$sql.' <p>'.$e->getMessage().'</p>');
				}
			}

			/* On vide la liste des inserts */
			$this->_pending = array("query" => array(), "cursor" => 1, "nbrow" => 0);
			$this->_parent->setLastInsertId(\He\PDO::getInstance($this->_param->bdd)->lastInsertId($this->_param->table));
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
     * Destructeur de class, envoie les instructions de sauvegarde, attention 
	 * les messages de \He\Trace ne sont pas pris en compte ici
     */
    public function  __destruct()
	{
		$this->massStorage(true);
	}
}