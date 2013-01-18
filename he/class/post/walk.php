<?php
/**
 * Class à utiliser en synergie avec HePOST, les objets créer ici servent à
 * enregistrer les paramètres des différents champs des formulaires
 * On peu renvoyer sous forme de tableau les différents objets listés ici
 * grace à getTree()
 *
 * @author frjle
 */
namespace He\POST;

class Walk {
	
	/**
	 * Nom du champ
	 * @var string
	 */
	private $_name = '';
	
	/**
	 * Nom du type de donnée attendu
	 * @var string
	 */
	private $_type;
	
	/**
	 * Le champ est-il obligatoiremetn rempli ?
	 * @var bool
	 */
	private $_required = false;
	
	/**
	 * Longeur maximum du champ, 0 pour pas de limite
	 * @var type 
	 */
	private $_maxlength = 0;
	
	/**
	 * Liste de valeurs possible du champ, par exemple pour un select
	 * @var array
	 */
	private $_options = array();
	
	/**
	 * Si c'est une branche, les feuilles sont stocké ici (design pattern composite)
	 * @var type 
	 */
	private $_child = array();
	
	/**
	 * Valeur reçu en POST
	 * @var mixed
	 */
	private $_value = null;
	
	/**
	 * Après les tests, dit si la valeur est conforme aux attentes
	 * @var bool 
	 */
	private $_isValid = true;
	
	/**
	 * Définie le nom de la branche
	 * @param string $name 
	 */
	public function __construct($name)
	{
		$this->_name = $name;
	}
	
	/**
	 * Analyse de la valeur envoyé en post
	 * @param mixed $value 
	 */
	public function analyse($value)
	{
		/* Attribution pour l'export vers POST */
		$this->_value = $value;
		
		/* Séquence d'analyse */
		$this->_analyseRequired($value);
		$this->_analyseType($value);
		$this->_analyseMaxlength($value);
		
		if($this->_isValid)
			\He\Trace::addTrace('Champ correcte : '.$this->_name, get_called_class(),1);
		else
			\He\Trace::addTrace('Champ faux : '.$this->_name, get_called_class(),-1);
		
		return $this->_isValid;
	}
	
	/**
	 * Analyse le type de donnée envoyé
	 * @param mixed $value 
	 */
	public function _analyseType($value)
	{
		switch($this->_type)
		{
			case 'hidden':
				if(!\He\Cast\CString::test($value))
				{
					\He\Trace::addTrace($this->_name.' : Type faux, ce n\'est pas un string !', get_called_class(),-1);
					$this->_isValid = false;
				}
				break;
			case 'text':
				if(!\He\Cast\CString::test($value))
				{
					\He\Trace::addTrace($this->_name.' : Type faux, ce n\'est pas un string !', get_called_class(),-1);
					$this->_isValid = false;
				}
				break;
			case 'password':
				if(!\He\Cast\CString::test($value))
				{
					\He\Trace::addTrace($this->_name.' : Type faux, ce n\'est pas un string !', get_called_class(),-1);
					$this->_isValid = false;
				}
				break;
			case 'mail':
				if(!\He\Cast\CMail::test($value))
				{
					\He\Trace::addTrace($this->_name.' : Type faux, ce n\'est pas un mail !', get_called_class(),-1);
					$this->_isValid = false;
				}
				break;
			case 'tel':
				if(!\He\Cast\CTel::test($value))
				{
					\He\Trace::addTrace($this->_name.' : Type faux, ce n\'est pas un numéro de téléphone !', get_called_class(),-1);
					$this->_isValid = false;
				}
				break;
			case 'number':
				if(!is_numeric($value))
				{
					\He\Trace::addTrace($this->_name.' : Type faux, ce n\'est pas un nombre !', get_called_class(),-1);
					$this->_isValid = false;
				}
				break;
			case 'array':
				/* Si c'est un select multiple */
				if(is_array($value))
				{
					foreach($value AS $val)
					{
						if(!in_array($val, $this->_options) || empty($val))
						{
							\He\Trace::addTrace($this->_name.' : valeur "'.  htmlspecialchars(print_r($val, 1)).'" fausse, Elle ne fait pas partie des valeurs permises !', get_called_class(),-1);
							$this->_isValid = false;
						}
					}
				}
				elseif(!in_array($value, $this->_options))
				{
					\He\Trace::addTrace($this->_name.' : valeur "'.  htmlspecialchars(print_r($value, 1)).'" fausse, Elle ne fait pas partie des valeurs permises !', get_called_class(),-1);
					$this->_isValid = false;
				}
				break;
			case 'checkbox':
				if(strtolower($value) != 'on' && !in_array($value, $this->_options) && !empty($value))
				{
					\He\Trace::addTrace($this->_name.' : valeur '.$value.' fausse, Elle ne fait pas partie des valeurs permises et n\'est pas à on !', get_called_class(),-1);
					$this->_isValid = false;
				}
				break;
			default:
				\He\Trace::addTrace($this->_name.' : Type de donnée inconnu : "'.$this->_type.'" !', get_called_class(),-2);
				$this->_isValid = false;
				break;
		}
	}
	
	/**
	 * Analyse la longeur de la valeur envoyé
	 * @param type $value 
	 */
	private function _analyseMaxlength($value)
	{
		if($this->_maxlength > 0)
		{
			/* Si c'est une valeur numérique */
			if(($this->_type == 'number' ||
				$this->_type == 'float') &&
				$value > $this->_maxlength)
			{
				\He\Trace::addTrace($this->_name.' : Valeur numérique trop grande !', get_called_class(),-1);
				$this->_isValid = false;
			}
			else if(strlen($value) > $this->_maxlength)
			{
				\He\Trace::addTrace($this->_name.' : Chaine de caractère trop grande !', get_called_class(),-1);
				$this->_isValid = false;
			}
		}
	}
	
	/**
	 * Test si le champ est correctement rempli
	 * @param mixed $value
	 */
	private function _analyseRequired($value)
	{
		/* Si on est pas obligé de remplir ce champ, osef */
		if($this->_required)
		{
			/* Si il est requis et qu'on est dans un type numérique */
			if(($this->_type == 'number' ||
				$this->_type == 'float') &&
				!is_numeric($value))
			{
				\He\Trace::addTrace($this->_name.' : Valeur numérique requise mais vide !', get_called_class(),-1);
				$this->_isValid = false;
			}
			// TODO Ajouter les types radio & select; ie. appartient à une liste !
			/* Sinon, peu importe le type */
			else if(empty($value))
			{
				\He\Trace::addTrace($this->_name.' : champ vide !', get_called_class(),-1);
				$this->_isValid = false;
			}
		}
	}
	
	/**
	 * Ajoute une feuille à cet objet (design pattern composite) si le tableau
	 * indique qu'on est à la fin de la branche, sinon donne la commande à la
	 * branche correspondante
	 * @param array $leafPath Chemin vers la branche
	 * @return array
	 */
	public function addChild($leafPath)
	{
		$leaf = array_shift($leafPath);
		
		if(!empty($leaf))
		{
			if(empty($this->_child[$leaf]))
				$this->_child[$leaf] = new static($leaf);

			if(count($leafPath) > 0 && !empty($leaf))
				$this->_child[$leaf]->addChild($leafPath);
		}
		
		return $leaf;
	}
	
	/**
	 * Récupère la feuille indiqué
	 * @param string $name
	 * @return \He\POST\Walk
	 */
	public function getChild($name)
	{
		return $this->_child[$name];
	}
	
	/**
	 * Recrée l'arboressence des objets enfant sous forme de tableau d'objet
	 * @return Array 
	 */
	public function getTree()
	{
		$branch = array();
		
		if(!empty($this->_child))
			foreach($this->_child AS $name => $obj)
				$branch[$name] = $obj->getTree();
		else
			$branch = $this;
		
		return $branch;
	}
	
	/**
	 * Recrée l'arboressence des objets enfant sous forme de tableau de valeur
	 * @return Array 
	 */
	public function getPOST()
	{
		$branch = array();
		
		if(!empty($this->_child))
			foreach($this->_child AS $name => $obj)
				$branch[$name] = $obj->getPOST();
		else
			$branch = $this->_value;
		
		return $branch;
	}
	
	/**
	 * Récupère la valeur de la feuille spécifié
	 * @param array $leafPath
	 * @return mixed 
	 */
	public function getLeafValue($leafPath)
	{
		if(count($leafPath) == 0 || empty($leafPath[0]))
		{
			return $this->_value;
		}
		else
		{
			$leaf = array_shift($leafPath);
			return $this->_callChild($leaf)->getLeafValue($leafPath);
		}
	}
	
	/**
	 * Définie le type de la feuille spécifié
	 * @param array $leafPath
	 * @param string $type 
	 */
	public function setLeafType($leafPath, $type)
	{
		if(count($leafPath) == 0 || empty($leafPath[0]))
		{
			if(empty($this->_child))
			{
				if(empty($this->_type) || $this->_type == 'array')
				{
					$this->_type = $type;
					
					if($type == 'array')
					{
						$this->_value = array();
					}
				}else
					throw new \He\Exception('Un formulaire ne peu avoir deux champs avec le même attribut name !');
			}
			else
			{
				throw new \He\Exception('Impossible d\'attribuer une valeur scalaire '.$type.' à un tableau dans une formulaire !');
			}
		}
		else
		{
			$leaf = array_shift($leafPath);
			$this->_callChild($leaf)->setLeafType($leafPath, $type);
		}
	}
	
	/**
	 * Définie si la feuille spécifié est requise
	 * @param array $leafPath
	 * @param bool $required 
	 */
	public function setLeafRequired($leafPath, $required)
	{
		if(count($leafPath) == 0 || empty($leafPath[0]))
		{
			$this->_required = $required;
		}
		else
		{
			$leaf = array_shift($leafPath);
			$this->_callChild($leaf)->setLeafRequired($leafPath, $required);
		}
	}
	
	/**
	 * Donne la longeur max de la feuille spécifié
	 * @param array $leafPath
	 * @param int $length 
	 */
	public function setLeafLength($leafPath, $length)
	{
		if(count($leafPath) == 0 || empty($leafPath[0]))
		{
			$this->_maxlength = $length;
		}
		else
		{
			$leaf = array_shift($leafPath);
			$this->_callChild($leaf)->setLeafLength($leafPath, $length);
		}
	}
	
	/**
	 * Ajoute un tableau d'options à la feuille spécifié
	 * @param array $leafPath
	 * @param array $options 
	 */
	public function setLeafOptions($leafPath, $options)
	{
		if(count($leafPath) == 0 || empty($leafPath[0]))
		{
			if(is_array($options))
			{
				$this->_options = $options;
			}
			else
			{
				\He\Trace::addTrace('L\'option actuelle qu\'on tente d\'ajouter n\'est pas un array !', get_called_class());
			}
		}
		else
		{
			$leaf = array_shift($leafPath);
			$this->_callChild($leaf)->setLeafOptions($leafPath, $options);
		}
	}
	
	/**
	 * Ajout une option à la feuille spécifié
	 * @param array $leafPath
	 * @param mixed $options 
	 */
	public function addLeafOptions($leafPath, $options)
	{
		if(count($leafPath) == 0 || empty($leafPath[0]))
		{
			$this->_options[] = $options;
		}
		else
		{
			$leaf = array_shift($leafPath);
			$this->_callChild($leaf)->addLeafOptions($leafPath, $options);
		}
	}
	
	/**
	 * Cherche la branche spécifié et la créer si elle n'existe pas
	 * @param string $name
	 * @return \He\POST\Walk 
	 */
	private function _callChild($name)
	{
		if(!empty($name))
		{
			if(empty($this->_child[$name]))
			{
				\He\Trace::addTrace('On crée un objet à la volée', get_called_class(), -1);
				$this->addChild(array($name));
			}
			
			return $this->_child[$name];
		}
		else
		{
			throw new \He\Exception('Nom vide pour la récupération de l\'objet enfant !');
		}
	}
}