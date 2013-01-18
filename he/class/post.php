<?php
/**
 * Analyse une chaine de caractère envoyé par HeTemplate et réecrit les formulaires
 * pour préparer les analyse lors de l'envoi par l'utilisateur
 *
 * Attention, utiliser cette class vide la variable $_POST de son contenue
 * Attention, la variable de session HePOST sera réservé pour la trasmition
 * Attention, Les session DOIVENT être initialisé AVANT cet objet
 * ============================================================================
 * Common usage
 * ============================================================================
 * 
 * Pour tester si le formulaire correspond aux attentes :
 * HePOST::test($xml) => bool
 * 
 * Analyse le formulaire indiqué dans form/monformulaire ou un XML déjà
 * prêt.
 * 
 * Aucun message d'erreur n'est renvoyé, les test DOIVENT être effectués par
 * Javascript avant l'envoi du formulaire. Si un formulaire faux parvient ICI
 * c'est signe d'une tentative de hack. Une note est ajouté dans Trace
 * 
 * Balises prise en charge dans le xml :
 * <input type="mail" />	: Restreint aux types de mails
 * <input type="int" />		: Restreint aux intergers uniquement
 * <input type="float" />	: Restreint aux flotants uniquement
 * <input type="date" />	: Restreint aux dates, sont supporté les formats FR 
 * et US selon la langue de l'utilisateur
 * <input type="text" />	: Restreint aux string ... si on peu dire restreint
 * <input type="password" />	: Restreint aux MDP, un second juste après créer
 * un champ de confirmation de MDP
 * <input type="hidden" />	: Champ caché habituel
 * <input type="token" />	: Impose l'exactitude avec le token en session
 * required					: Rend le champ obligatoire (non null, non vide)
 * <option value="">		: fonctionne de la même manière que les type ci-dessus
 * <radio name="" value=""> : fonctionne de la même manière que les type ci-dessus
 * <textarea>				: Lu pour maxlength et required
 * 
 * Les champs text et textarea peuvent recevoir un paramètre maxlength, les
 * autres aussi mais ça ne sert pas à grand chose
 * 
 * ============================================================================
 * Note
 * ============================================================================
 * 
 * - La conséquence de l'utilisation de cette class est que pour déterminer
 * le traitement post envoi dans le controleur il faut modifier le template
 * associé
 * 
 * - Quelque soit le nombre de paramètre présent en post, on ne renvoi QUE les
 * champs présent dans le formulaire, on peu donc utliser des boucle sans 
 * crainte.
 * 
 * @author Joseph Lemoine - joseph.lemoine@gmail.com
 * @version 4
 */
namespace He;

class POST
{
	/**
	 * Tableau de champs tel que présent dans le formulaire
	 * @var type 
	 */
	private static $_node = array();
	
	private static $_comparePSW = array();
	
	private static $_jokerName = array();
	
	private function __construct() {}
	private function __clone() {}
	
	/**
	 * Test un formulaire reçu via post. On peu envoyer un chemin vers le
	 * template de formulaire à tester ou un template préparé.
	 * @param string $xml formulaire préparé
	 * @return bool
	 */
	public static function test($file)
	{
		/* Un formulaire vide est forcément faux */
		if(empty($_POST))
			return false;
		
		/* Chargement du tempalte ... ou pas si c'en est déjà un */
		\He\Trace::addTrace('Test pour savoir si on nous envoi un template ou non', get_called_class());
		if(\He\Template::isTemplate($file))
			$xml = \He\Template::extract($file);
		else
			$xml = $file;
		
		if(empty($xml))
		{
			throw new \He\Exception('Le template de formulaire envoyé est vide !');
		}
		
		/* Analyse sémantique du formulaire et création des objets leaf */
		static::$_node = new POST\Walk("root");
		static::_analyseName($xml, static::$_node);
		static::_parseXML($xml);
		
		/* Analyse du formulaire et renvoi dans $_POST */
		if($send = static::_verify(static::$_node->getTree(), $_POST))
			$_POST = static::$_node->getPOST();
		else
			unset($_POST);
		
		/* Renvoi le résultat de l'analyse */
		return $send;
	}
	
	/**
	 * Test si on à deux champs password, si oui on vérifie qu'ils sont identiques
	 * Ensuite on analyse les autres données récupérés
	 * @param array $tree
	 * @param array $deep 
	 * @return bool
	 */
	private static function _verify(&$tree, &$deep = array())
	{
		/* On ne test les MDP que si on a bien les bon type de données */
		if(static::_analysePOST($tree, $deep))
		{
			if(count(static::$_comparePSW) > 1)
			{
				\He\Trace::addTrace('Analyse des mots de passes', get_called_class());
				$prpevPSW = '';
				/* Tri inverse !*/
				rsort(static::$_comparePSW);
				
				foreach(static::$_comparePSW AS $path)
				{
					/* On récupère la valeur de la feuille */
					$pwd = static::$_node->getLeafValue(static::_nameToArray($path));
					
					if(!empty($prpevPSW) && $prpevPSW != $pwd || empty($pwd))
					{
						\He\Trace::addTrace('Les mots de passe ne coincident pas : "'.$pws.'" - "'.$prpevPSW.'"!', get_called_class(), -1);
						return false;
					}
					elseif(!empty($prpevPSW) && $prpevPSW == $pwd)
					{
						return true;
					}

					$prpevPSW = $pwd;
				}
				return true;
			}
			else
			{
				\He\Trace::addTrace('Pas de mots de passe à analyser', get_called_class());
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Analyse les données en post à partir de cette que le template nous indique
	 * Au terme de l'analyse, on renvois true ou false selon si tout les champs
	 * ont été correctement rempli ou non. Analyse en récursif.
	 * @param array	$tree Arboressence des champs
	 * @param array $deep profondeur d'analyse
	 */
	private static function _analysePOST(&$tree, &$deep = array())
	{
		if(is_array($tree) && !empty($tree))
		{
			foreach($tree AS $name => $branch)
			{
				$formValid = true;
				if(is_array($branch))
				{
					$formValid = static::_analysePOST($branch, $deep[$name]);
				}
				else if(!$branch->analyse($deep[$name]))
				{
					$formValid = false;
				}
				
				if(!$formValid)
				{
					Trace::addTrace('Formulaire faux !', get_called_class());
					return false;
				}
			}
			return true;
		}
		else
		{
			throw new Exception('On ne peu pas analyser autre chose qu\'un tableau d\'objets !');
		}
	}
	
	/**
	 * Recherche tout les champs qui seront envoyé par post dans le forumalaire
	 * et les stock dans $_node sous forme d'arboressence
	 * @param string	$form	formulaire à traiter
	 */
	private static function _analyseName(&$form, &$node)
	{
		Trace::addTrace("Enregistrement des noms de champs", get_called_class());
		/* Récupération des noms à traiter */
		$pattern = '#name=["|\'](.*?)["|\']#s';
		preg_match_all($pattern, $form, $matches);
		
		/* Si on à bien des nodes */
		if (!empty($matches[1]))
		{
			/* Tranformation du namePath en array */
			foreach($matches[1] AS $key => $namePath)
				$node->addChild(static::_nameToArray($namePath));
		}
	}
	
	/**
	 * Découpe le XML en arbre de \He\POST\Walk pret pour l'analyse
	 * @param string $xml 
	 */
	private static function _parseXML(&$xml)
	{
		Trace::addTrace("Analyse du formulaire", get_called_class());
		
		/**
		 * Récupération des valeurs possibles
		 */
		/* Recherche des inputs */
		$pattern = '#<input (.*?)/>#s';
		preg_match_all($pattern, $xml, $matches);

		if(!empty($matches[1]))
		{
			foreach($matches[1] AS $param)
			{
				static::_analyseData(static::$_node, $param);
			}
		}

		/* Recherche des textarea */
		$pattern = '#<textarea (.*?)>#s';
		preg_match_all($pattern, $xml, $matches);

		if(!empty($matches[1]))
		{
			foreach($matches[1] AS $param)
			{
				static::_analyseData(static::$_node, $param, "text");
			}
		}

		/* Recherche des select */
		$pattern = '#<select (.*?)>(.*?)</select>#s';
		preg_match_all($pattern, $xml, $matches);
		
		if(!empty($matches[1]))
		{
			foreach($matches[1] AS $param)
			{
				$selectPath = static::_analyseData(static::$_node, $param, "array");
			}
			foreach($matches[2] AS $param)
			{
				static::_analyseOption(static::$_node, $param, $selectPath);
			}
		}
	}
	
	/**
	 * Découpe les options et ajoute les différentes valeurs possible dans le
	 * tableau de l'objet Walk associé à ce nom
	 * @param He\POST\Walk $node
	 * @param string $param chaine à découper
	 * @param string $path chemin vers la branche
	 */
	private static function _analyseOption(&$node, $param, $path)
	{
		\He\Trace::addTrace('Analyse d\'un champ de type select : '.$path, get_called_class());
		
		/* Recherche des options */
		$pattern = '#<option (.*?)>(.*?)</option>#s';
		preg_match_all($pattern, $param, $matches);
		
		if(!empty($matches[1]))
		{
			$options = array();
			foreach($matches[1] AS $value)
			{
				$value = explode(" ", $value);
				/* Analyse des paramètres */
				foreach($value AS $cat)
				{
					if(substr($cat, 0, 5) == "value")
						$options[] = str_replace('"', "", str_replace('value=', "", $cat));
				}
			}
			
			$node->setLeafOptions(static::_nameToArray($path), $options);
		}
	}
	
	/**
	 * Analyse les paramètre d'un champ et les envoits à la feuille
	 * correspondante
	 * @param array $node	Tronc de base du formulaire
	 * @param string $param	Liste des paramètres séprarés par un espace
	 * @param type $forceType Type de champ forcé
	 */
	private static function _analyseData(&$node, $param, $forceType = null)
	{
		/* Séparation des différenst types de données */
		$param = explode(" ", $param);
		$param = str_replace(' ', '', $param);
		
		if(!empty($param))
		{
			/* Initialisation */
			$type = !empty($forceType) ? $forceType : "text";
			$required = false;
			$maxlength = 0;
			$name = "";
			$value = null;
			$multiple = false;
			
			/* Analyse des paramètres */
			foreach($param AS $cat)
			{
				if(substr($cat, 0, 4) == 'name')
				{
					$name = str_replace('"', "", str_replace('name=', "", $cat));
				}
				if(substr($cat, 0, 4) == 'type' && empty($forceType))
				{
					$type = str_replace('"', "", str_replace('type=', "", $cat));
					if($type == 'radio')
						$type = 'array';
				}
				if($cat == 'required')
				{
					$required = true;
				}
				if(substr($cat, 0, 9) == 'maxlength')
				{
					$maxlength = (INT)str_replace('"', "", str_replace('maxlength=', "", $cat));
				}
				/* Uniquement si c'est un tableau, sinon la valeur est perdue */
				if(substr($cat, 0, 5) == 'value')
				{
					$value = str_replace('"', "", str_replace('value=', "", $cat));
				}
				if(substr($cat, 0, 8) == 'multiple')
				{
					$multiple = true;
				}
			}
			
			if(!empty($name))
			{	
				/* Ajout des options si on est dans un radio */
				if($type == 'array')
				{
					$node->addLeafOptions(static::_nameToArray($name), $value);
				}
				/* Ajout de l'option si c'est une checkbox */
				if($type == 'checkbox' || $type == 'hidden' && !empty($value))
					$node->addLeafOptions(static::_nameToArray($name), $value);
				
				/* On prépare le cas où il faut entrer deux fois le mot de passe */
				if($type == 'password')
					static::$_comparePSW[] = $name;
				
				/* Ajout des propriété */
				$node->setLeafType(static::_nameToArray($name), $type);
				$node->setLeafRequired(static::_nameToArray($name), (BOOL)$required);
				$node->setLeafLength(static::_nameToArray($name), (INT)$maxlength);
				
				/* On renvoi le chemin pour l'anayse des select */
				return $name;
			}
		}
	}
	
	/**
	 * Découpe le nom d'un champ sous forme de tableau
	 * @param string $namePath	nom de la variable post sous forme name[sname][...]
	 * @return array
	 */
	private static function _nameToArray($namePath)
	{
		/* Transformation en chemin de type name/sname/ssname/... */
		return explode("/", str_replace("]", "", str_replace("[", "/", $namePath)));
	}
}