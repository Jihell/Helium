<?php
/**
 * Enregistre les paramètres d'une instance DAO, pour les centraliser lors
 * de l'appel via la factory HeTab
 *
 * ============================================================================
 * USAGE
 * ============================================================================
 * 
 * \He\DB\Param::make()->table('maTable')
 *					   ->alias('comptes')
 *					   ->readOnly(true)
 *					   ->bindJoinAlias('test', 'id_test', 'table_test')
 * 
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 3
 * 
 * TODO ajuter dépendances pour effacer les lignes de tables liés
 */
namespace He\DB;

final class Param
{
	/**
	 * Liste des paramètres de la DAO :
	 * - bdd			: nom de la base de donnee
	 * - colsField		: array des noms de champs de la table
	 * - colsType		: array des types de champs de la table
	 * - colsPosition	: array de l'ordre des champs de la table
	 * - listCol		: Chaine de caratère des champs de la table, sous forme
	 *					 directement exploitable dans une requète
	 * - listColSth		: Chaine de caractères des champs de la table, sous forme
	 *					 directement exploitable dans une requète PDO pour binder
	 *					 les valeurs
	 * - listColT		: Chaine de caractères des champs de la table, sous forme
	 *					 directement exploitable dans une requète PDO pour binder
	 *					 les valeurs avec des jointures. La table courante à
	 *					 pour nom "t"
	 * - listColPrimary : Chaine de caractères des clefs primaire de la table,
	 *					 sous forme directement exploitable dans une requète
	 * - listColTPrimary: Chaine de caractères des clefs primaire de la table,
	 *					 sous forme directement exploitable dans une requète
	 *					 avec jointures
	 * - listColTWherePrimary : Chaine de caractères des clefs primaires avec
	 *					 les sélecteur PDO pour l'expoitation dans des clauses
	 *					 WHERE d'une requète
	 * - primary		: Liste des clefs primaires de la table. (en cas de clef jointes)
	 * - join			: liste des jointures possible et leur destination
	 * - dependance		: liste des jointures possible et leur destination
	 * - table			: Nom de la table, pour PDO
	 * - alias			: Alias de la table, on l'appelera avec ce nom
	 * - readOnly		: Interdit l'écriture via les DAO sur cette table
	 * - load			: Tableau de séquence de chargement
	 * 
	 * @var array
	 */
    private $_param = array();
	
	/**
	 * Par défaut, afin d'allger la mémoire cette class est en someil.
	 * Dès que \He\DB réclame le chargment d'une ligne on active les paramètres,
	 * dès lors la class est activé.
	 * @var bool
	 */
	private $_activate = false;
	
	/**
	 * Alias de jointures.
	 * De façon automatique, les jointures sont détecté par la syntaxe de la
	 * colonne : Si elle commence par id_ alors c'est un jointure.
	 * Ce système n'autorise pas de joindre deux fois la même table, sauf
	 * si on utilise un alias avec bindJoinAlias()
	 * @var array 
	 */
	private $_joinAlias = array();
	
	/**
	 * Crée et renvoi une nouvelle instance de param
	 * @return static
	 */
	public static function make()
	{
		$instance = new static();
		return $instance;
	}
    
	/**
	 * Clone privé, les paramètres sont instanciés une seule et unique fois !
	 */
	private function __clone(){}
	
	/**
	 * Initialise les différents paramètres
	 */
    private function __construct()
    {
		/* Préparation des variables */
        $this->_param['bdd'] = DEF_BASE;
        $this->_param['colsField'] = array();
        $this->_param['colsType'] = array();
        $this->_param['colsPosition'] = array();
        $this->_param['listCol'] = '';
        $this->_param['listColSth'] = '';
        $this->_param['listColT'] = '';
        $this->_param['listColPrimary'] = '';
        $this->_param['listColTPrimary'] = '';
        $this->_param['listColTWherePrimary'] = '';
        $this->_param['primary'] = array();
        $this->_param['join'] = array();
        $this->_param['noJoin'] = array();
        $this->_param['joinAlias'] = array();
        $this->_param['dependance'] = array();
		
		/* Données a renseigner par le developpeur */
		$this->_param['table'] = '';
		$this->_param['alias'] = '';
		$this->_param['readOnly'] = '';
		$this->_param['load'] = array();
    }
	
	/**
	 * définie quel base de donnée est associé à cette DAO
	 * @return $this 
	 */
	public function bdd()
	{
		$this->_param['bdd'] = !empty($bdd) ? $bdd : DEF_BASE;
		return $this;
	}
	
	/**
	 * Nom de la table associé à cette DAO
	 * @param string $table
	 * @return $this
	 */
	public function table($table)
	{
		$this->_param['table'] = $table;
		$this->_param['alias'] = $table;
		return $this;
	}
	
	/**
	 * Alias de la table, si aucun n'est spécifié, l'alias devient le nom
	 * de la table
	 * @param string $alias
	 * @return $this 
	 */
	public function alias($alias = '')
	{
		$this->_param['alias'] = !empty($alias) ? $alias : $table;
		return $this;
	}
	
	/**
	 * Indique si on doit envoyer une erreur si on tente de sauvegarder une
	 * nouvelle ligne sur cette table
	 * @param bool $ro
	 * @return $this 
	 */
	public function readOnly($ro = false)
	{
		$this->_param['readOnly'] = $ro;
		return $this;
	}
	
	/**
	 * Ajout des paramètres à la séquence de chargement de la ligne.
	 * Les paramètres peuvent êtres :
	 * 
	 * [column] = nom de la colonne si ce n'est pas la clef primaire ou qu'il n'y en a pas.
	 * [order] = nom de la où trier si ce n'est pas la clef primaire ou qu'il n'y en a pas.
	 * [selector] = clause WHERE de la requète de chargement.
	 * 
	 * @param array $loadMethod
	 * @return $this 
	 */
	public function loadMethod($loadMethod = array())
	{
		$this->_param['load'] = $loadMethod;
		return $this;
	}
	
	/**
	 * Donne un alias de jointure à une colonne. Attention, ceci supprimera la 
	 * jointure naturelle de cette colonne
	 * @param string $alias Nom de la méthode sans le "join".
	 * @param string $column Colonne où est la clef de liaison
	 * @param string $target Table cible
	 * @return $this
	 */
	public function bindJoinAlias($alias, $column, $target)
	{
		$this->_param['noJoin'][$column] = $alias; // Pour les jointures naturelles
		$this->_param['joinAlias'][$alias]['col'] = $column;
		$this->_param['joinAlias'][$alias]['target'] = $target;
		return $this;
	}
	
	/**
	 * Indique quel table dépend directement de celle-ci. Exemple :
	 * des utilisateurs ont plusieurs rôles
	 * donc la table role dépend de la table user car id_user est présent
	 * dans la table role.
	 * @param type $alias
	 * @param type $column 
	 * @return $this
	 */
	public function bindDependance($table, $column)
	{
		$this->_param['dependance'][$table] = $column;
		return $this;
	}
	
	/**
	 * Séquence d'activation des paramètres, permet de préserver le système
	 * de la création de requète de lecteur de colonne sur des table non utilisés
	 */
	public function setActive()
	{
		if(!$this->_activate)
		{
			\He\Trace::addTrace('Activation des paramètres de '.$this->alias, get_called_class());
			$this->_getColumns();
		}
	}
    
	/**
	 * Getter magique
	 * @param string $name
	 * @return mixed 
	 */
    public function __get($name)
    {	
        if(isset($this->_param[$name]))
           return $this->_param[$name];
        else
            throw new \He\Exception('Ce paramètre '.$name.' n\'existe pas pour cette DAO');
    }
	
	/**
	 * Interdit la création de nouvelle propriétés de cette class
	 * @param type $name
	 * @param type $value 
	 */
	public function __set($name, $value)
	{
		throw new \He\Exception('Tentative d\'accès à une propriété privée !');
	}
	
	/**
	 * Récupère les informations relative à la table paramétré.
	 * Si le cache est déjà créer, on le charge avec un return include.
	 * @return VOID 
	 */
	private function _getColumns()
	{
		if(!file_exists(DAO_CACHE_PATH.'/'.$this->_param['alias'].'.php'))
		{
			if(empty($this->_param['table']))
				throw new \He\Exception('ERREUR FATALE : Mauvais paramétrage d\'une table. Elle ne peut pas être anonyme !');
			
			if(empty($this->_param['alias']))
				$this->_param['alias'] = $this->table;
			
			\He\Trace::addTrace('Linsting des colonnes pour la table '.$this->table, get_called_class());

			$colist = \He\PDO::getInstance($this->bdd)->query('SHOW COLUMNS FROM '.$this->table)->fetchAll();

			$nbCol = 1;
			foreach ($colist AS $data)
			{
				/* Si on a récupéré la clef primaire, on la sauvegarde */
				if($data[3] == 'PRI')
				{
					$this->_param['primary'][] = $data[0];
					$this->_param['listColPrimary'] .= $data[0].', ';
					$this->_param['listColTPrimary'] .= 't.'.$data[0].', ';
					$this->_param['listColTWherePrimary'] .= $data[0].' = :'.$data[0].' AND ';
				}

				/* Si le nom de la colonne commence par id on mémorise la jointure */
				if(!empty($this->_joinAlias[$data[0]]))
				{
					$this->_param['join'][$this->_joinAlias[$data[0]]['alias']]['col'] = $data[0];
					$this->_param['join'][$this->_joinAlias[$data[0]]['alias']]['target'] = $this->_joinAlias[$data[0]]['target'];
				}
				elseif(strtolower(substr($data[0], 0, 2)) == 'id' && strlen($data[0]) > 2)
				{
					$this->_param['join'][substr($data[0], 3)]['col'] = $data[0];
					$this->_param['join'][substr($data[0], 3)]['target'] = substr($data[0], 3);
				}

				/* Liste des variable récupéré */
				$this->_param['colsField'][$nbCol] = $data[0];
				$this->_param['colsType'][$nbCol] = \He\DB\FieldType::getType($data[1]);
				$this->_param['colsPosition'][$data[0]] = $nbCol;
				/* Préparation des string pour les appels bdd */
				$this->_param['listColSth'] .= ':'.$data[0].', ';
				$this->_param['listCol'] .= $data[0].', ';
				$this->_param['listColT'] .= 't.'.$data[0].', ';
				$nbCol++;
			}

			$this->_param['listCol'] = substr($this->_param['listCol'], 0, -2);
			$this->_param['listColSth'] = substr($this->_param['listColSth'], 0, -2);
			$this->_param['listColT'] = substr($this->_param['listColT'], 0, -2);
			$this->_param['listColPrimary'] = substr($this->_param['listColPrimary'], 0, -2);
			$this->_param['listColTPrimary'] = substr($this->_param['listColTPrimary'], 0, -2);
			$this->_param['listColTWherePrimary'] = substr($this->_param['listColTWherePrimary'], 0, -4);

			/* Ajout du fichier de cache de DAO param, pour éviter les SHOW TABLE */
			$this->_makeCache();

			/* Activation */
			$this->_activate = true;
		}
		else
		{
			\He\Trace::addTrace('Inclusion du cache pour la table '.$this->_param['alias'], get_called_class());
			$this->_activate = true;
			return include(DAO_CACHE_PATH.'/'.$this->_param['alias'].'.php');
		}
	}
	
	/**
	 * Créer le cache à charger dans la méthode _getColumns() et l'enregistre
	 * dans un fichier du type config/daocache/alias_de_la_table.php
	 * Pour enregistrer les fichiers de cache DAO ailleur, 
	 * modifiez la constante DAO_CACHE_PATH
	 */
	private function _makeCache()
	{
		\He\Trace::addTrace('Début de création du fichier de cache de '.$this->_param['alias'], get_called_class());
		$send = '<?php'.PHP_EOL;
		$send .= '$this->_param[\'listColSth\'] = \''.$this->_param['listColSth'].'\';'.PHP_EOL;
		$send .= '$this->_param[\'listCol\'] = \''.$this->_param['listCol'].'\';'.PHP_EOL;
		$send .= '$this->_param[\'listColT\'] = \''.$this->_param['listColT'].'\';'.PHP_EOL;
		$send .= '$this->_param[\'listColPrimary\'] = \''.$this->_param['listColPrimary'].'\';'.PHP_EOL;
		$send .= '$this->_param[\'listColTPrimary\'] = \''.$this->_param['listColTPrimary'].'\';'.PHP_EOL;
		$send .= '$this->_param[\'listColTWherePrimary\'] = \''.$this->_param['listColTWherePrimary'].'\';'.PHP_EOL;
		
		foreach($this->_param['primary'] AS $needle => $data)
			$send .= '$this->_param[\'primary\'][\''.$needle.'\'] = \''.$data.'\';'.PHP_EOL;
		foreach($this->_param['colsField'] AS $needle => $data)
			$send .= '$this->_param[\'colsField\'][\''.$needle.'\'] = \''.$data.'\';'.PHP_EOL;
		foreach($this->_param['colsType'] AS $needle => $data)
			$send .= '$this->_param[\'colsType\'][\''.$needle.'\'] = \''.$data.'\';'.PHP_EOL;
		foreach($this->_param['colsPosition'] AS $needle => $data)
			$send .= '$this->_param[\'colsPosition\'][\''.$needle.'\'] = \''.$data.'\';'.PHP_EOL;
		
		/* Ecritures des jointures */
		foreach($this->_param['join'] AS $needle => $data)
			foreach($data AS $type => $val)
				$send .= '$this->_param[\'join\'][\''.$needle.'\'][\''.$type.'\'] = \''.$val.'\';'.PHP_EOL;
		foreach($this->_param['joinAlias'] AS $needle => $data)
			foreach($data AS $type => $val)
				$send .= '$this->_param[\'joinAlias\'][\''.$needle.'\'][\''.$type.'\'] = \''.$val.'\';'.PHP_EOL;		
		foreach($this->_param['noJoin'] AS $needle => $data)
			$send .= '$this->_param[\'noJoin\'][\''.$needle.'\'] = \''.$data.'\';'.PHP_EOL;
		
		/* Ecriture des dépendances */
		foreach($this->_param['dependance'] AS $needle => $data)
			$send .= '$this->_param[\'dependance\'][\''.$needle.'\'] = \''.$data.'\';'.PHP_EOL;

		if(file_put_contents(\He\Dir::makePath(DAO_CACHE_PATH).'/'.$this->_param['alias'].'.php', $send))
			\He\Trace::addTrace('Fichier de cache de '.$this->_param['alias'].' crée avec succès', get_called_class(), 1);
		else
		{
			\He\Trace::addTrace('Fichier de cache de '.$this->_param['alias'].' vide !', get_called_class(), -2);
			throw new \He\Exception('ERREUR : Impossible de créer le fichier de cache DAO pour '.$this->table);
		}
	}
}