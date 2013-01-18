<?php
/**
 * Gestion des templates du site
 * Analyse le template et remplace les variables là où il faut
 * ainsi que les objets etc selon le modèle du fichier template
 * Utilisation :
 * - Créer l'objet avec le fichier à charger
 * -- Dans le fichier template on trouve :
 * --- {@nom_de_template} : Template lié (comme un include)
 * --- {$nom_de_variable} : Variable à remplacer
 * --- {%nom_de_clef_de_localisation} : Clef de localisation
 * --- {node::nom_du_node}{/node::nom_du_node} : Noeud où effectuer des trucs
 * --- {*commentaire*} : Un commentaire qui n'apparaitra pas dans l'html
 * --- <plug>HTML</plug> : Plug du code dans le header
 * --- Tout le reste de l'html est laissé tel quel
 * - le fichier est découpé et placé dans un array $_TmpTemplate
 * -- L'array est découpé en sous tableaux pour chaque node présent
 * -- Chaque node est enregistré dans le tableau $_Nodes[nom] = path
 * - A partir de là on peu envoyer des tableaux de variables à remplacer
 *   dans un node précis avec la méthode geneNode
 * - La méthode display recrée dans un premier temps le template complet, puis
 *   Récupère les variable de localisation dans la langue correspondante
 *   à cette du navigateur client et enfin stock le rendu dans une variable
 *   pour permettre au controleur d'afficher le contenu
 * - La méthode draw affiche et vide le contenue de la variable static::$_ready
 *
 * @author  Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 6
 */
namespace He;

class Template
{
	/**
     * template sous forme de tableau
     * @var	array
     */
    private $_tmpTemplate = array();

    /**
     * Plugins à inssérer dans head
     * @var	string
     */
    private $_plugins = '';
	
	/**
	 * Listes des fichiers chargés
	 * @var	array
	 */
	private $_includes = array();
	
	/**
	 * Nom du fichier d'origine
	 * @var string
	 */
	private $_file = '';
	
	/**
	 * Chemin vers les fichiers mergés
	 * @var string
	 */
	private $_cachePath = '';
	
	/**
	 * Indique si le fichier doit être localisé ou si c'est déjà le cas
	 * @var bool
	 */
	private $_isLocalised = false;
	
	/**
	 * Templates prêts à être affichés
	 * @var string
	 */
	private static $_ready = '';

    /**
     * Charge le tableau de node et leur adresse
     * @param	string	$file	Fichier de template à charger
     * @access	public
     */
    public function __construct($file = null)
	{
		$this->_file = $file;
		Trace::addTrace('Ouverture du template '.$this->_file, get_called_class());
		/* Récupération du template */
		$this->_tmpTemplate['xml'] = $this->_load($this->_file);
		
		/* Création du cache des fichiers mergés si on a chargé plusieurs templates */
		if(count($this->_includes) > 1 && MERGE_TEMPLATE)
			if(!file_exists(PRIM_TEMPLATE_PATH.CACHE_PATH.'merge/'.$file.'.html'))
				$this->_mergeTemplates($file, $this->_tmpTemplate['xml']);

		if(LOCALISE && !$this->_isLocalised)
			$this->_localiseXML($file, $this->_tmpTemplate['xml']);
			
		/* Enregistrement des nodes à utiliser */
		$this->_registerNodes($this->_tmpTemplate);
    }
	
	private function _localiseXML($file, &$xml)
	{
		/* Si la chaine du nom de fichier contient déjà des dossiers */
		$path = $this->_makePathFromFileName($file);
		$file = $this->_makeFileName($file);
		
		\He\Localise::run($xml);
		\He\Trace::addTrace('Création du cache localised : '.$this->_cachePath.$path.' => '.$file.'.html', get_called_class(), 1);
		return file_put_contents(\He\Dir::makePath($this->_cachePath.$path).$file.'.html', $xml);
	}
	
	/**
	 * Créer un fichier de cache avec tout les fichier dépendant du template
	 * courant directement remplacé et purgé des espaces.
	 * @param string $file
	 * @param string $xml
	 * @return int nombre d'octets écrits 
	 */
	private function _mergeTemplates($file, $xml)
	{
		/* Si la chaine du nom de fichier  contient déjà des dossiers */
		$path = $this->_makePathFromFileName($file);
		$file = $this->_makeFileName($file);
		
		\He\Trace::addTrace('Création du cache merge : '.$this->_cachePath.$path.' => '.$file.'.html', get_called_class(), 1);
		return file_put_contents(\He\Dir::makePath($this->_cachePath.$path).$file.'.html', $xml);
	}
	
	public function _makeFileName($file)
	{
		$file = explode('/', $file);
		return array_pop($file);
	}
	
	/**
	 * Créer le chemin vers le dossier de cache
	 * @param string $file
	 * @return string 
	 */
	private function _makePathFromFileName($file)
	{
		/* Si la chaine contient déjà des dossiers */
		$path = explode('/', $file);
		if(count($path) > 1)
		{	
			$file = array_pop($path);
			$path = implode('/', $path).'/';
		}
		/* Ajout du slash si le chemin le cache est incomplet */
		elseif(!empty($this->_cachePath))
		{
			if(substr($this->_cachePath, -1) != '/')
			{
				$path = '/';
			}
			else
			{
				$path = '';
			}
		}
		
		return $path;
	}
	
	/**
	 * Test si le template indiqué existe
	 * @param type $file 
	 * @return bool
	 */
	public static function isTemplate($file)
	{
		/* On recherche d'abord dans les fichiers de He, puis dans le reste */
		if(!file_exists(PRIM_TEMPLATE_PATH.$file.'.html') &&
			!file_exists(SECO_TEMPLATE_PATH.$file.'.html'))
		{
			\He\Trace::addTrace('Test du fichier "'.$file.'" : Ce n\'est pas un template !', get_called_class(), -1);
			return false;
		}
		else
		{
			\He\Trace::addTrace('Test du fichier "'.$file.'" : C\'est un template !', get_called_class(), 1);
			return true;
		}
	}
	
	/**
	 * Crée à la volé une instance de lui même pour extraire le xml du fichier
	 * demandé et le renvoyer sous forme lisible par un autre parseur
	 * @param string $file
	 * @return string
	 */
	public static function showXML($file)
	{
		$template = new static($file);
		return $template->display(true);
	}

    /**
     * Charge le template et sous template
     * @param	string	$file	Fichier à charger
     * @access	private
     */
    private function _load($file)
	{
		/* On test si le fichier est déjà chargé */
		if(in_array($file, $this->_includes))
			throw new \He\Exception('Chargement du template déjà effectué, '
					.'boucle infinie, vérifiez l\'appel du template '.$file);
		
		/* Si on localise */
		if(LOCALISE)
		{
			$xml = $this->_loadLocalised($file);
		}
		/* Si on merge */
		elseif(MERGE_TEMPLATE)
		{
			$xml = $this->_loadMerge($file);
		}
		
		/* Sinon on charge le fichier fragmenté */
		if(empty($xml))
		{
			$xml = $this->_loadParsed($file);
			/* chargement des dépendances et supression des commentaires */
			$this->_loadDependencies($xml)
				 ->_cleanComments($xml);
		}
		
		return $xml;
    }
	
	/**
	 * Charge les templates liés au xml chargé
	 * @param string $xml
	 * @return $this 
	 */
	private function _loadDependencies(&$xml)
	{
		/* Récupération des templates à inclure */
		$pattern = '#{@([^}]*)}#s';
		preg_match_all($pattern, $xml, $matches);

		/* Si on à bien des résultats */
		if (!empty($matches[1]))
		{
			foreach ($matches[1] AS $key => $fileName)
			{
				/* Chargement et remplacement dans le template parent */
				$xml = str_replace( '{@'.$fileName.'}', $this->_load($fileName), $xml);
			}
		}
		
		return $this;
	}
	
	/**
	 * Supprime le texte placé entre les balises "{*" et "*}"
	 * @param string &$xml
	 * @return $this
	 */
	private function _cleanComments(&$xml)
	{
		$pattern = '#{\*(.*?)\*}#s';
		preg_match_all($pattern, $xml, $matches);

		/* Si on à bien des résultats */
		if (!empty($matches[1]))
		{
			foreach ($matches[1] AS $key => $comment)
			{
				/* Suppression de la balise */
				$xml = str_replace( '{*'.$comment.'*}', '', $xml);
			}
		}
		
		return $this;
	}
	
	/**
	 * Charge un template pré fusionné et le retourne sous forme de chaine de
	 * caractère
	 * @param string $file
	 * @return string / false si le fichier est introuvable
	 */
	private function _loadLocalised($file)
	{
		/* Définition du cache depuis le fichier fragmenté */
		if(file_exists(PRIM_TEMPLATE_PATH.$file.'.html'))
			$this->_cachePath = PRIM_TEMPLATE_PATH.CACHE_PATH.'localised/'.$_SESSION['lang'].'/';
		else
			$this->_cachePath = SECO_TEMPLATE_PATH.CACHE_PATH.'localised/'.$_SESSION['lang'].'/';
		
		/* Recherche dans le cache des fichiers localisés */
		if(file_exists(PRIM_TEMPLATE_PATH.CACHE_PATH.'localised/'.$_SESSION['lang'].'/'.$file.'.html'))
		{
			$this->_includes[] = $file;
			$this->_isLocalised = true;
			return file_get_contents(PRIM_TEMPLATE_PATH.CACHE_PATH.'localised/'.$_SESSION['lang'].'/'.$file.'.html');
		}
		elseif(file_exists(SECO_TEMPLATE_PATH.CACHE_PATH.'localised/'.$_SESSION['lang'].'/'.$file.'.html'))
		{
			$this->_includes[] = $file;
			$this->_isLocalised = true;
			return file_get_contents(SECO_TEMPLATE_PATH.CACHE_PATH.'localised/'.$_SESSION['lang'].'/'.$file.'.html');
		}
		
		return false;
	}
	
	/**
	 * Charge un template pré fusionné et le retourne sous forme de chaine de
	 * caractère
	 * @param string $file
	 * @return string / false si le fichier est introuvable
	 */
	private function _loadMerge($file)
	{
		/* Définition du cache depuis le fichier fragmenté */
		if(empty($this->_cachePath))
		{
			if(file_exists(PRIM_TEMPLATE_PATH.$file.'.html'))
				$this->_cachePath = PRIM_TEMPLATE_PATH.CACHE_PATH.'merge/';
			else
				$this->_cachePath = SECO_TEMPLATE_PATH.CACHE_PATH.'merge/';
		}
		
		/* Recherche dans le cache des fichiers merged */
		if(file_exists(PRIM_TEMPLATE_PATH.CACHE_PATH.'merge/'.$file.'.html'))
		{
			$this->_includes[] = $file;
			return file_get_contents(PRIM_TEMPLATE_PATH.CACHE_PATH.'merge/'.$file.'.html');
		}
		elseif(file_exists(SECO_TEMPLATE_PATH.CACHE_PATH.'merge/'.$file.'.html'))
		{
			$this->_includes[] = $file;
			return file_get_contents(SECO_TEMPLATE_PATH.CACHE_PATH.'merge/'.$file.'.html');
		}
		
		return false;
	}
	
	/**
	 * Charge le fichier spécifié depuis le répertoire de template et le retourne
	 * après avoir supprimer les tabulation et retours chariot.
	 * @param string $file
	 * @return string / false si le fichier est introuvable
	 */
	private function _loadParsed($file)
	{
		if(file_exists(PRIM_TEMPLATE_PATH.$file.'.html'))
		{
			$this->_includes[] = $file;
			
			if(empty($this->_cachePath))
				$this->_cachePath = PRIM_TEMPLATE_PATH.CACHE_PATH.'merge/';
			
			$xml = file_get_contents(PRIM_TEMPLATE_PATH.$file.'.html');
			/* Purge des caractères de retour chariot pour pas bousiller les inline-block */
			return $this->_purgeHiddenChar($xml);
		}
		elseif(file_exists(SECO_TEMPLATE_PATH.$file.'.html'))
		{
			$this->_includes[] = $file;
			
			if(empty($this->_cachePath))
				$this->_cachePath = SECO_TEMPLATE_PATH.CACHE_PATH.'merge/';
			
			$xml = file_get_contents(SECO_TEMPLATE_PATH.$file.'.html');
			/* Purge des caractères de retour chariot pour pas bousiller les inline-block */
			return $this->_purgeHiddenChar($xml);
		}
		
		\He\Trace::addTrace('Le template "'.$file.'" n\'existe pas !', get_called_class(), -2);
		throw new \He\Exception('Le template "'.$file.'" n\'existe pas !');
		return false;
	}
	
	/**
	 * Supprime les tabulation et retours chariot de la chaine de caractère
	 * @param string $string
	 * @return string 
	 */
	private function _purgeHiddenChar($string)
	{
		return str_replace(array("\t", "\n", "\r"), "", $string);
	}

    /**
     * Découpe le XML en tableaux de node etc ...
     * @access	private
     */
    private function _registerNodes(&$node) {
		/* Ajout des plugins */
		$this->_addPlugins($node['xml']);
	
		/* Récupération des noeuds à traiter */
		$pattern = '#{node::([^}]*)}(.*?){\/node::\1}#s';
		preg_match_all($pattern, $node['xml'], $matches);
	
		/* Si on à bien des nodes */
		if (!empty($matches[1]))
		{
		    /* Ajout du contenue pour chaque node trouvé */
		    foreach ($matches[1] AS $key => $nodeName)
			{
				/* Ajout du contenue */
				$node['node'][$nodeName]['xml'] = $matches[2][$key];

				/* On remplace le contenue du node par un flag */
				$node['xml'] = str_replace($matches[0][$key], '<flag::'.$nodeName.'>', $node['xml']);

				/* Enregistrement des nodes enfants */
				$this->_registerNodes($node['node'][$nodeName]);
		    }
		}
	
		/* Enregistrement des variables */
		$pattern = '#{\$([^}]*)}#s';
		preg_match_all($pattern, $node['xml'], $matches);
	
		/* Si on à bien des variables */
		if (!empty($matches[1]))
		{
		    /* Ajout de la variable dans le node */
		    foreach ($matches[1] AS $key => $varName)
			{
				$node['var'][$varName] = '';
		    }
		}
    }

    /**
     * Attribue une valeur à une variable. Si aucun node n'est précisé,
     * on commence l'attribution à la racine
     * @param	string	$varName    Nom de la variable
     * @param	mixed	$varValue   Valeur de la variable
     * @param	bool	$cascade    Remplacer dans les sous nodes ou non
     * @param	&	$node	    Référence vers le node à scanner, attention
     *							ce paramètre ne doit-être passé que par
     *							récursion ou un alias de cette methode
     */
    private function _setVar($varName, $varValue, $cascade = true, &$node = null) {
		/* Si on à pas passé de référence de node*/
		if (empty($node)) {$node = &$this->_findNode('');}
	
		/* Traitement des sous node */
		if (!empty($node['node']) && $cascade) {
		    /* Les références ne suivent pas $osef donc on s'en sert pas */
		    foreach ($node['node'] AS $nodeName => $osef) {
				/* On applique aux sous node */
				$this->_setVar($varName, $varValue, $cascade, $node['node'][$nodeName]);
		    }
		}
	
		/* Si la variable est présente dans le node, on lui donne sa valeur */
		if (isset($node['var'][$varName])) {$node['var'][$varName] = $varValue;}
    }

    /**
     * Alias de setVar avec argument pour préciser le nom du node où remplacer
     * @param	string	$varName    Nom de la variable
     * @param	mixed	$varValue   Valeur de la variable
     * @param	bool	$cascade    Remplacer dans les sous nodes ou non
     * @param	string	$nodeName   Nom du node
     */
    public function setVarToNode($varName, $varValue, $nodeName = null, $cascade = true) {
		/* Recherche du node */
		$node = &$this->_findNode($nodeName);
		/* Enregistrement de sa valeur */
		$this->_setVar($varName, $varValue, $cascade, $node);
    }

    /**
     * Attribue un tableau de variable à un node
     * @param	array	$varList    [$varName] = $varValue
     * @param	string	$nodeName   Nom du node où remplacer tout ça
     * @param	bool	$cascade    Remplacer dans les sous nodes ou non
	 * @return	$this
     */
    public function setArrayToNode($varList, $nodeName = null, $cascade = true) {
		/* Recherche du node */
		$node = &$this->_findNode($nodeName);
	
		/* enregistrement de toute les variables */
		foreach ($varList AS $varName => $varValue) {
		    $this->_setVar($varName, $varValue, $cascade, $node);
		}
		
		return $this;
    }
    
	/**
	 * Déploye les données du tableau envoyé dans le node spécifié automatiquement
	 * Les données doivent être sous la forme d'un tableau de variable ou de
	 * tableau.
	 * @param array $varList liste des données
	 * @param string $nodeName nom du node spécifié
	 * @param bool $killNode si on ferme ou non le tableau finale
	 * @param bool $cascade Stop ou non la propagation de données
	 * @return bool 
	 */
    public function autoSetArray($varList, $nodeName, $killNode = true, $cascade = true)
	{
		if(empty($nodeName))
		{
			\He\Trace::addTrace('Le node spécifié est vide !', get_called_class());
			throw new \He\Exception('Erreur fatale : Le node spécifié est vide !');
			return false;
		}
		
		if(is_array($varList) && count($varList) > 0)
		{
			\He\Trace::addTrace('Auto set array valide, déployement ...', get_called_class());
			foreach($varList AS $needle => $list)
			{
				if(!is_array($list))
					$this->setVarToNode($needle, $list, $nodeName, $cascade);
				else
					$this->setArrayToNode($list, $nodeName, $cascade);

				$this->copyNode($nodeName);
			}
			
			if($killNode)
				$this->killNode($nodeName);
			
			return true;
		}
		else
		{
			\He\Trace::addTrace('La variable envoyé n\'est pas un tableau valide !', get_called_class(), -2);
			return false;
		}
	}

    /**
     * Duplique un node
     * @param	string	$nodeName   Nom du node à dupliquer
     * @return	$this
     */
    public function copyNode($nodeName) {
		/* recherche du parent du node */
		$parent = &$this->_findParentNode($nodeName);
	
		if(!empty($parent)){
		    /* Si le parent n'est pas finalisé, on le finalise */
		    if(empty($parent['final'])) {$parent['final'] = $parent['xml'];}
	
		    /* Rebuild du node à dupliquer */
		    $this->_rebuildXML($parent['node'][$nodeName]);
	
		    /* Ajout des données du node à dupliquer dans son parent et rajout du flag */
		    $parent['final'] = str_replace(
					'<flag::'.$nodeName.'>',
					$parent['node'][$nodeName]['final'].'<flag::'.$nodeName.'>',
					$parent['final']);
	
		    /* Destruction de la finalisation du node */
		    unset($parent['node'][$nodeName]['final']);
			
			return $this;
		} else {
		     /* Pas de parent, on renvoit faux */
		    return FALSE;
		}
    }

    /**
     * Supprime l'adressage d'un noeud dans son parent
     * @param	string	$nodeName   Nom du node à détruire
     * @return	$this
     */
    public function killNode($nodeName)
	{
		/* recherche du parent du node */
		$parent = &$this->_findParentNode($nodeName);
	
		if(!empty($parent)){
		     /* Si le parent n'est pas finalisé, on le finalise */
		    if(empty($parent['final'])) {$parent['final'] = $parent['xml'];}
	
		    /* supression des données du node dans son parent */
		    $parent['final'] = str_replace('<flag::'.$nodeName.'>', '', $parent['final']);
			return $this;
		}
		else
		{
			\He\Trace::addTrace('Impossible de trouver le node recherché !', get_called_class());
			return $this;
		}
    }
	
	/**
	 * Copy et tue le node ciblé. Revient à exécuter copyNode et killNode
	 * succesivement.
	 * @param string $nodeName
	 * @return $this
	 */
	public function closeNode($nodeName)
	{
		$this->copyNode($nodeName);
		$this->killNode($nodeName);
		return $this;
	}

    /**
     * Trouve et retourne le node avec le nom spécifié
     * @param	string	$searchName Nom du node à trouver
     * @param	array	$node	    Node de référence pour débuter la recherche
     * @return	&		    pointeur vers le node correspondant
     * @access	private
     */
    private function &_findNode($searchName, &$node = NULL) {
		/* Si $searchName est vide ou vaut 'root' on renvoit la racine */
		if (empty($searchName) || strtolower($searchName) == 'root') {
		    $node = &$this->_tmpTemplate;
		    return $node;
		}
	
		/**
		 * Si on spécifier un node de référence on le passe en paramètre,
		 * sinon on part de la racine
		 */
		if(empty($node))
		    $node = &$this->_tmpTemplate;
	
		/* Si la racine donnée à bien des sous node */
		if(is_array($node['node']))
		    /* Parcourt de l'arboressence */
		    foreach ($node['node'] AS $nodeName => $nodeDetails) {
	
		/* Si c'est le bon, on le renvoit directement */
		if ($searchName == $nodeName)
		    $result = &$node['node'][$nodeName];
		/* Sinon si il y a des sous nodes, on les analyses */
		else if (isset($nodeDetails['node']))
		    $result = &$this->_findNode($searchName, $node['node'][$nodeName]);
	
		/* Si on à un résultat, on stop la recherche */
		if (!empty($result))
		    break;
	    }
	
		/* On renvoit le résultat */
		return $result;
    }

    /**
     * Trouve et retourne le node avec le nom spécifié
     * @param	string	$searchName Nom du node à trouver
     * @param	array	$node	    Node de référence pour débuter la recherche7
     * @return	&		    pointeur vers le node correspondant
     * @access	private
     */
    private function &_findParentNode($searchName, &$node = NULL) {
		/**
		 * Si on spécifier un node de référence on le passe en paramètre,
		 * sinon on part de la racine
		 */
		if(empty($node))
		    $node = &$this->_tmpTemplate;
	
		/* Si la racine donnée à bien des sous node */
		if(is_array($node['node']))
		    /* Parcourt de l'arboressence */
		    foreach ($node['node'] AS $nodeName => $nodeDetails) {
	
			/* Si c'est le bon, on le renvoit directement */
			if ($searchName == $nodeName)
			    $result = &$node;
			/* Sinon si il y a des sous nodes, on les analyses */
			else if (isset($nodeDetails['node']))
			    $result = &$this->_findParentNode($searchName, $node['node'][$nodeName]);
	
			/* Si on à un résultat, on stop la recherche */
			if (!empty($result))
			    break;
		    }
	
		/* On renvoit le résultat */
		return $result;
    }

    /**
     * Recrée l'arboressence du XML
     * @param	array	$node	node à recréer
     * @access	private
     */
    private function _rebuildXML(&$node)
	{
		/* On finalise notre node si c'est pas déjà fait */
		if(empty($node['final'])) {$node['final'] = $node['xml'];}
	
		/* Si on à des sous node */
		if (isset($node['node']))
		{
		    /* Les références ne suivent pas $osef donc on s'en sert pas */
		    foreach ($node['node'] AS $nodeName => $osef)
			{
				/* on finalise les sous nodes */
				$this->_rebuildXML($node['node'][$nodeName]);

				/* On remplace les flags du node par leur valeur */
				$node['final'] = str_replace(
					'<flag::'.$nodeName.'>',
					$node['node'][$nodeName]['final'],
					$node['final']
				);

				/* On détuit la finalisation du sous node */
				unset($node['node'][$nodeName]['final']);
		    }
		}
	
		/* Ecriture des variables */
		if (!empty($node['var'])) {
		    foreach ($node['var'] AS $varName => $varValue) {
				/* Inscription des valeurs */
				$node['final'] = str_replace('{$'.$varName.'}', $varValue, $node['final']);
				/* Réinitialisation de la variable */
				$node['var'][$varName] = '';
		    }
		}
	}

    /**
     * Ajoute les plugins enregistrés dans les différents XML chargés
     * @param	&		$xml	    Référence de la chaine à traiter
     * @access	private
     */
    private function _addPlugins(&$xml) {
		/* Recherche des balises head */
		$pattern = '#<head>(.*?)<\/head>#s';
		preg_match_all($pattern, $xml, $matches);
	
		/* Si on à bien des résultats */
		if (!empty($matches[1]) && !empty($this->_plugins)) {
			Trace::addTrace('Ajout de plugin dans le template', get_called_class());
		    /* Pour chaque head (normalement un seul) */
		    foreach ($matches[1] AS $key => $head) {
				/* replacement de head */
				$xml = str_replace($matches[0][$key], $head.$this->_plugins, $xml);
		    }
		}
    }
    
    /**
     * Vide le cache du thème
     * @access public	
     */
    public static function flushCache()
	{
		Trace::addTrace('Destruction du cache de template', get_called_class());
		HeDir::clear(CACHE_PATH);
    }
	
	private function _makeAbsoluteURL(&$xml)
	{
		/* Récupération des sources */
		$pattern = '#src=["|\'](.*?)["|\']#s';
		preg_match_all($pattern, $xml, $matches);
		
		/* Si on à bien des sources */
		if (!empty($matches[1]))
		{
		    /* Réecriture des sources */
		    foreach ($matches[1] AS $key => $src)
			{
				if(substr($src, 0, 3) !=  'htt' && substr($src, 0, 3) !=  'ftp')
				{
					$xml = str_replace($matches[0][$key], 'src="'.SERVER_NAME.$src.'"', $xml);
				}
		    }
		}
		
		/* Récupération des liens */
		$pattern = '#href=["|\'](.*?)["|\']#s';
		preg_match_all($pattern, $xml, $matches);
		
		/* Si on à bien des liens */
		if (!empty($matches[1]))
		{
		    /* Réecriture des liens */
		    foreach ($matches[1] AS $key => $src)
			{
				if(substr($src, 0, 3) !=  'htt' && substr($src, 0, 3) !=  'ftp')
				{
					$xml = str_replace($matches[0][$key], 'href="'.SERVER_NAME.$src.'"', $xml);
				}
		    }
		}
		
		/* Récupération des actions de formulaires */
		$pattern = '#action=["|\'](.*?)["|\']#s';
		preg_match_all($pattern, $xml, $matches);
		
		/* Si on à bien des liens */
		if (!empty($matches[1]))
		{
		    /* Réecriture des liens */
		    foreach ($matches[1] AS $key => $src)
			{
				if(substr($src, 0, 3) !=  'htt' && substr($src, 0, 3) !=  'ftp')
				{
					$xml = str_replace($matches[0][$key], 'action="'.SERVER_NAME.$src.'"', $xml);
				}
		    }
		}
	}

    /**
     * Génère l'html complet puis récupère les clefs de localisation à charger
     * et enfin affiche la page
     * @param	bool	$return	    Renvoit le template sous forme de string
     * @access	public
     */
    public function display($return = false) {
		$this->_rebuildXML($this->_tmpTemplate);
		
		/* Génération des url absolues */
		$this->_makeAbsoluteURL($this->_tmpTemplate['final']);
		
		if($return)
		    return $this->_tmpTemplate['final'];
		else
		{
		    static::$_ready .= $this->_tmpTemplate['final'];
		}
	}
	
	/**
	 * Finalise et extrait un node du template
	 * TODO faire en sorte que ça marche
	 * @param string $nodeName
	 * @return string 
	 */
	public function extractNode($nodeName = '')
	{
		$node = $this->_findNode($nodeName);
		
		$this->_rebuildXML($node);
		/* Génération des url absolues */
		$this->_makeAbsoluteURL($node['final']);
		return $node['final'];
	}
	
	/**
	 * Transforme la chaine a afficher en text brut pour les sorties ajax
	 */
	public static function bindAjaxHeader()
	{
		header('Content-Type:text/plain');
	}
	
	/**
	 * Ajoute les headers pour interdire la mise en cache du client
	 */
	public static function bindDefaultHeader()
	{
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1980 00:00:00 GMT");
	}
	
	/**
	 * Affiche les templates stockés
	 */
	public static function draw()
	{
		\He\Trace::addTrace('Envoi du rendu au client', get_called_class(), 2);
		echo static::$_ready;
	}
}