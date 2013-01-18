<?php
/**
 * Description of Node
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\Template;

class Node
{
	/**
	 * Contenu brut du template
	 * @var string 
	 */
	protected $_xml = '';
	
	/**
	 * Contenu finalisé du template
	 * @var string 
	 */
	protected $_final = '';
	
	/**
	 * Contenu temporaire du node
	 * @var string 
	 */
	protected $_copy = '';
	
	/**
	 * Liste des variables présente dans ce noeud
	 * @var array 
	 */
	protected $_var = array();
	
	/**
	 * Valeur courante des variables
	 * @var array 
	 */
	protected $_varValue = array();
	
	/**
	 * Liste des clefs de traduction de ce noeud
	 * @var array 
	 */
	protected $_loc;
	
	/**
	 * Liste des noeuds enfant de celui-ci
	 * @var array
	 */
	protected $_node = array();
	
	/**
	 * Indique si ce node doit être finalisé
	 * @var bool 
	 */
	protected $_killed = false;
	
	/**
	 * Node parent de celui ci
	 * @var \He\Template\Node 
	 */
	protected $_parent;
	
	/**
	 * Indique si le noeud est celui qui est à la racine du template
	 * @var bool
	 */
	protected $_isRoot = false;
	
	/**
	 * Chemin menant au template étant à l'origine de ce node
	 * @var string 
	 */
	protected $_path = ROOT;
	
	/**
	 * Créer un noeud et lance l'analyse
	 * @param type $xml 
	 */
	public function __construct($xml, \He\Template\Node $parent = null, $path = ROOT)
	{
		$this->_xml = $xml;
		$this->_parent = $parent;
		if(empty($parent))
		{
			$this->_isRoot = true;
		}
		$this->_path = $path;
		
		$this->_analyse();
	}
	
	/**
	 * Séquence à exécuter lors de l'ajout de contenu au noeud
	 * @return $this;
	 */
	protected function _analyse()
	{
		$this->_purgeComment()
			 ->_loadDependencies()
			 ->_listNode()
			 ->_listVar()
			 ->_listLoc();
		
		return $this;
	}
	
	/**
	 * Ajoute du contenue à ce noeud, on recréer les éventuels enfants
	 * @param string $xml 
	 */
	public function addContent($xml, $path = '')
	{
		$this->_xml .= $xml;
		if(!empty($path))
		{
			$this->_path = $path;
		}
		
		$this->_analyse();
	}
	
	/**
	 * Ajoute un node enfant à ce node.
	 * @param string $name nom du node
	 * @param string $xml contenue du node
	 */
	protected function _addNode($name, $xml)
	{
		if(!empty($this->_node[$name]))
		{
			throw new \He\Exception('Impossible d\'ajouter deux nodes ayant le même nom : '.$name.' !');
		}
		
		$this->_node[$name] = new static($xml, $this);
	}
	
	/**
	 * Charge les templates liés au xml chargé
	 * @param string $xml
	 * @return $this 
	 */
	private function _loadDependencies()
	{
		/* Récupération des templates à inclure */
		$pattern = '#{@([^}]*)}#s';
		preg_match_all($pattern, $this->_xml, $matches);

		/* Si on à bien des résultats */
		if (!empty($matches[1]))
		{
			foreach ($matches[1] AS $key => $fileName)
			{
				\He\Trace::addTrace('Chargement de la dépendance "'.$this->_path.'/'.$fileName.'"', get_called_class());
				/* Chargement et remplacement dans le template parent */
				$this->_xml = str_replace($matches[0][$key], \He\Template::getRaw($this->_path.'/'.$fileName), $this->_xml);
			}
			
			/* Si on a eu des résultas, on relance la boucle */
			$this->_loadDependencies();
		}
		
		return $this;
	}
	
	/**
	 * Liste les nodes enfant de ce node depuis l'xml en mémoire.
	 * @return $this
	 */
	protected function _listNode()
	{
		$pattern = '#{node::([^}]*)}(.*?){\/node::\1}#s';
		preg_match_all($pattern, $this->_xml, $matches);
		
		/* Si on à bien des nodes */
		if (!empty($matches[1]))
		{
		    /* Ajout du contenue pour chaque node trouvé */
		    foreach ($matches[1] AS $key => $nodeName)
			{
				/* On remplace le contenue du node par un flag pour plus tard */
				$this->_xml = str_replace($matches[0][$key], '{flag::'.$nodeName.'}', $this->_xml);
				
				/* Ajout du contenue */
				$this->_addNode($nodeName, $matches[2][$key]);
		    }
		}
		
		return $this;
	}
	
	/**
	 * Liste les variable présente dans ce node
	 * @return $this
	 */
	protected function _listVar()
	{
		$pattern = '#{\$([^}]*)}#s';
		preg_match_all($pattern, $this->_xml, $matches);
	
		/* Si on à bien des variables */
		if (!empty($matches[1]))
		{
		    /* Ajout de la variable dans le node */
		    foreach ($matches[1] AS $key => $varName)
			{
				if(!isset($this->_varValue[$varName]))
				{
					$this->_varModel[] = $matches[0][$key];
					$this->_var[$varName] = '';
					$this->_varValue[$varName] = '';
				}
		    }
		}
		
		return $this;
	}
	
	/**
	 * Liste les clefs de traduction présente dans ce node
	 * @return $this
	 */
	protected function _listLoc()
	{
		$pattern = '#{\%([^}]*)}#s';
		preg_match_all($pattern, $this->_xml, $matches);
	
		/* Si on à bien des variables */
		if (!empty($matches[1]))
		{
		    /* Ajout de la variable dans le node */
		    foreach ($matches[1] AS $key => $varName)
			{
				$this->_loc[$varName] = '';
		    }
		}
		
		return $this;
	}
	
	/**
	 * Supprime les commentaires spéciaux du xml
	 * @return $this
	 */
	protected function _purgeComment()
	{
		$pattern = '#{\*(.*?)\*}#s';
		preg_match_all($pattern, $this->_xml, $matches);

		/* Si on à bien des résultats */
		if (!empty($matches[1]))
		{
			$this->_xml = str_replace($matches[0], '', $this->_xml);
		}
		
		return $this;
	}
	
	/**
	 * Supprime les tabulations et retours chariot du xml en mémoire
	 * @return $this 
	 */
	protected function _purgeHiddenChar()
	{
		$this->_final = str_replace(array("\t", "\n", "\r"), '', $this->_final);
		return $this;
	}
	
	/**
	 * Remplace les valeurs courante des variables dans le node
	 * @return $this
	 */
	public function finalise()
	{
		if(!$this->_killed)
		{
			/* Ajout des variables dans le tampon */
			$temp = str_replace($this->_varModel, array_values($this->_varValue), $this->_xml);
			
			/* Ajout des sous nodes */
			$this->_setNode($temp);
		
			/* Ajout du contenu finalisé */
			$this->_final = $this->_copy.$temp;
		}
		else
		{
			$this->_killed = false;
		}
		
		return $this;
	}
	
	/**
	 * Remplace les drapeaux de sous node par leur contenu
	 * @param &string $xml variable de stockage
	 * @return $this 
	 */
	protected function _setNode(&$xml)
	{
		if(!empty($this->_node))
		{
			foreach($this->_node AS $name => $node)
			{
				$xml = str_replace('{flag::'.$name.'}', $node->getContent(), $xml);
			}
		}
		
		return $this;
	}
	
	/**
	 * Renvoi le contenue actuel du node
	 * @param bool $dont_purge // Purge ou non les caractères invisibles
	 * @return string 
	 */
	public function getContent($dont_purge = false)
	{
		$this->finalise();
		if(!$dont_purge)
			$this->_purgeHiddenChar();
		
		return $this->_makeAbsoluteURL($this->_final);
	}
	
	/**
	 * Renvoi le contenue brut actuel du node
	 * @return string 
	 */
	public function getRaw()
	{
		return $this->_xml;
	}
	
	/**
	 * Renvoi le contenue brut actuel du node de façon récursive, on obtient ainsi
	 * un template complet sans chargement de dépendance.
	 * @return string 
	 */
	public function getModel()
	{
		$model = $this->_xml;
		/* Récupération récursive des contenues enfants */
		if(!empty($this->_node))
		{
			foreach($this->_node AS $name => $node)
			{
				$model = str_replace(
							'{flag::'.$name.'}', 
							'{node::'.$name.'}'.$node->getModel().'{/node::'.$name.'}', 
							$model
						);
			}
		}
		
		return $model;
	}
	
	/**
	 * Donne la valeur $value à la variable de template $name. Si $propagation
	 * est à true, on envoi la commande bindVar aux node descendant.
	 * @param string $name
	 * @param string $value
	 * @param bool $propagation
	 * @return $this 
	 */
	public function bindVar($name, $value, $propagation = true)
	{
		if(isset($this->_var[$name]))
		{
			$this->_varValue[$name] = $value;
		}

		/* Proparagation le cas échéant */
		if($propagation && !empty($this->_node))
		{
			foreach($this->_node AS $node)
			{
				$node->bindVar($name, $value, $propagation);
			}
		}
		
		return $this;
	}
	
	/**
	 * Trouve le node spécifié par $nodeName et lui lande la méthode bindVar
	 * @param string $name nom de la variable
	 * @param mixed $value valeur à attribuer
	 * @param string $nodeName nom du node recherché
	 * @param bool $propagation
	 * @return $this
	 */
	public function bindVarToNode($name, $value, $nodeName = '', $propagation = true)
	{
		if(!empty($nodeName))
			$this->getNode($nodeName)->bindVar($name, $value, $propagation);
		else
			$this->bindVar($name, $value, $propagation);
		
		return $this;
	}
	
	/**
	 * Attribut toutes les variables contenues dans $varList. Si propagation est 
	 * à true, on envoi la liste aux descendant également.
	 * @param array $varList
	 * @param bool $propagation
	 * @return $this 
	 */
	public function bindVarList($varList, $propagation = true)
	{
		if(!is_array($varList))
			throw new \He\Exception('La variable '.$varList.' n\'est pas un tableau valide !');
		
		foreach($varList AS $name => $value)
		{
			$this->bindVar($name, $value, $propagation);
		}
		
		return $this;
	}
	
	/**
	 * Trouve le node spécifié par $nodeName et lui lande la méthode bindVar
	 * @param string $name nom de la variable
	 * @param mixed $value valeur à attribuer
	 * @param string $nodeName nom du node recherché
	 * @param bool $propagation
	 * @return $this
	 */
	public function bindVarListToNode($varList, $nodeName, $propagation = true)
	{
		$this->getNode($nodeName)->bindVarList($varList, $propagation);
		return $this;
	}
	
	/**
	 * Assigne less valeur des variables et duplique le node à chaque fois
	 * @param array $varList
	 * @param bool $propagation
	 * @return \He\Template\Node 
	 */
	public function autoBinding($varList, $propagation = true)
	{
		if(is_array($varList))
		{
			foreach($varList AS $list)
			{
				if(is_array($list))
				{
					$this->bindVarList($list, $propagation);
					$this->copy();
				}
				else
					throw new \He\Exception('ERREUR, le tableau est mal syntaxé !');
			}
			return $this;
		}
		else
		{
			throw new \He\Exception('ERREUR, ce n\'est pas un tableau !');
		}
	}
	
	/**
	 * Assigne les valeur des variables et duplique le node à chaque fois
	 * @param array $varList
	 * @param string $nodeName
	 * @param bool $propagation
	 * @return \He\Template\Node 
	 */
	public function autoBindingToNode($varList, $nodeName = '', $propagation = true)
	{
		if(is_array($varList))
		{
			if(!empty($nodeName))
			{
				$this->getNode($nodeName)->autoBinding($varList, $propagation);
				return $this;
			}
			else
			{
				throw new \He\Exception('ERREUR, Le node cible ne peut être null');
			}
		}
		else
		{
			throw new \He\Exception('ERREUR, ce n\'est pas un tableau !');
		}
	}
	
	/**
	 * Alias de $this->finalise()
	 * @return $this 
	 */
	public function copy()
	{
		$this->finalise()
			 ->_clearChild();
		
		$this->_copy = $this->_final;

		/* Purge des variables */
		$this->_varValue = $this->_var;
		
		return $this;
	}
	
	/**
	 * Force le retour d'une chaine vide lors de la prochaine finalisation du node
	 * @return $this 
	 */
	public function kill()
	{
		$this->_killed = true;
		
		if(!empty($this->_node))
		{
			foreach($this->_node AS $node)
			{
				$node->kill();
			}
		}
		
		return $this;
	}
	
	/**
	 * Enchaine une copie de ce node puis une fermeture
	 * @return Node 
	 */
	public function close()
	{
		$this->finalise();
		$this->kill();
		return $this;
	}
	
	/**
	 * Récupère le node parent de celui ci
	 * @return mixed
	 */
	public function getParent()
	{
		if(!empty($this->_parent))
			return $this->_parent;
		else
			return null;
	}
	
	/**
	 * Récupère le node spécifié par la chaine de caractère $name à travers
	 * les node enfant ce celui ci.
	 * @param string $name nom du node recherché du type nodeA/nodeB/nodeC
	 * @return Node
	 */
	public function getNode($name)
	{
		$path = explode('/', $name);
		if(count($path) > 0 && !empty($path) && !empty($name))
		{
			$next = array_shift($path);
			
			if(!empty($this->_node[$next]))
			{
				return $this->_node[$next]->getNode(implode('/', $path));
			}
			else
			{
				throw new \He\Exception('Le node '.$next.' n\'existe pas ('.$name.'), dispo : '.print_r(array_keys($this->_node), 1));
			}
		}
		else
		{
			return $this;
		}
	}
	
	/**
	 * Réinitialise l'objet.
	 * @param bool $propagation
	 * @return $this
	 */
	protected function _clear($propagation = true)
	{
		$this->_final = '';
		$this->_temp = '';
		$this->_killed = false;
		$this->_varValue = $this->_var;
		
		if($propagation)
			$this->_clearChild();
		
		return $this;
	}
	
	/**
	 * Réinitialise les objets enfants.
	 * @param bool $propagation
	 * @return $this
	 */
	protected function _clearChild($propagation = true)
	{
		if(!empty($this->_node))
		{
			foreach($this->_node AS $node)
			{
				$node->_clear($propagation);
			}
		}
		
		return $this;
	}
	
	/**
	 * Réecrit les adresses relative en adresse absolues
	 * @param string $xml 
	 * @return $xml
	 */
	protected function _makeAbsoluteURL(&$xml)
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
		
		return $xml;
	}
	
	/**
	 * Créer le fichier de template fusionné et le sauvegarde dans le chemin 
	 * de cache /merge dans un répertoire en fonction de l'uri envoyé.
	 * Exemple pour la page d'accueil avec les paramètres par défaut :
	 * ROOT/cache/merge/index.php
	 * @return bool
	 */
	public function _makeMergeFile()
	{
		$path = CACHE_PATH.'/merge/'.\He\Dispatch::getControler();
		$file = CACHE_PATH.'/merge/'.\He\Dispatch::getControler().'.php';
		
		\He\Dir::makePath($path);
		
		if(file_put_contents($file, $this->getModel()))
			return true;
		else
			return false;
	}
	
	/**
	 * Créer le fichier de template fusionné et le sauvegarde dans le chemin 
	 * de cache /merge dans un répertoire en fonction de l'uri envoyé.
	 * Exemple pour la page d'accueil avec les paramètres par défaut :
	 * ROOT/cache/merge/index.php
	 * @return bool
	 */
	public function _makeLocalisedFile()
	{
		$path = CACHE_PATH.'/localised/'.\He\Dispatch::getControler();
		$file = CACHE_PATH.'/localised/'.\He\Dispatch::getControler().'.php';
		
		\He\Dir::makePath($path);
		
		if(file_put_contents($file, $this->getModel()))
			return true;
		else
			return false;
	}
}