<?php
/**
 * Aiguillage vers le bon controleur en fonction de l'uri reçu
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He;

class Dispatch
{
	/**
	 * uri que l'on vas exploiter dans le script, a différencier de l'uri reçu !
	 * @var string
	 */
	protected static $_uri = '';
	
	/**
	 * nom du controleur final
	 * @var string
	 */
	protected static $_controler = '';
	
	/**
	 * Indique si on cherche à se connecter en tant que SU ou non
	 * @var string
	 */
	protected static $_askSU = false;
	
	/**
	 * Liste des arguments demandé dans l'uri
	 * @var array
	 */
	protected static $_args = array();
	
	/**
	 * Chaine de controleur à appeler
	 * @var array
	 */
	protected static $_chain = array();
	
	/**
	 * Chaine de controleurs à appeler au début de la séquence
	 * @var array
	 */
	protected static $_chainBegin = array();
	
	/**
	 * Chaine de controleurs à appeler a la fin de la séquence
	 * @var array
	 */
	protected static $_chainLast = array();
	
	/**
	 * Indique si nous somme ou non dans une procédure ajax
	 * @var bool
	 */
	protected static $_onAjax = false;
	
	/**
	 * Cherche le controleur à appelé et commence la boucle de controle.
	 * Si on détecte /su/ en début d'URI on propose la connection en tant que SU
	 * si on est déjà connecté en tant que SU on affiche le panneau de controle
	 */
	public static function run()
	{
		static::$_uri = $_SERVER['REQUEST_URI'];
		if(preg_match('#^\/su\/(.*?)$#', static::$_uri))
		{
			if(!$_SESSION['SU'])
			{
				static::$_askSU = true;

				/* On force le log */
				if(!TRACE)
					\He\Trace::init();

				\He\Trace::addTrace('On demande une connection en tant que super admin !', get_called_class(), -1);
			}
			
			static::$_uri = substr(static::$_uri, 3);
			\He\Trace::addTrace('On a comme uri '.static::$_uri, get_called_class(), 1);
		}
		
		/* Supression du premier slash */
		static::$_uri = substr(static::$_uri, 1);
		if(static::$_uri{strlen(static::$_uri) - 1} == '/')
			static::$_uri = substr(static::$_uri, 0, -1);
		
		/* Récupération des arguments */
		static::$_args = explode('/', static::$_uri);
		
		/* Récupération du controleur */
		$controler = array_shift(static::$_args);
		
		/* Réecriture du controleur */
		$controler = static::_rewriteControl($controler);
		
		/* Création de la séquence de controle */
		static::_buildSequence($controler, static::$_askSU);
		
		/* Exécution de la séquence de controle */
		static::_chainControl();
		
		/* Affichage su template complet */
		if(static::$_onAjax)
			\He\Template::sendAjaxHeader();
		else
			\He\Template::sendDefaultHeader();
		
		\He\Template::draw();
	}
	
	/**
	 * Réecrit le nom du controleur si c'est un controleur spécial type ajax
	 * ou controleur interne au framework
	 * @param type $controler
	 * @return type 
	 */
	protected static function _rewriteControl($controler)
	{
		if(empty($controler))
			return '\Module\Index\Main';
		
		/**
		 * Ajax interne au projet
		 */
		if($controler == 'ajax')
		{
			\He\Trace::mute();
			static::$_onAjax = true;
			$target = array_shift(static::$_args);
			$controler = '\Module\\'.ucfirst($target).'\Ajax';
		}
		
		/**
		 * Controleur spécific au framework
		 */
		elseif($controler == 'he')
		{
			$target = ucfirst(array_shift(static::$_args));
			
			/**
			 * Si c'est une controleur ajax du framework
			 */
			if($target == 'Ajax')
			{
				\He\Trace::hide();
				static::$_onAjax = true;
				$controler = 'He\Module\\'.ucfirst(array_shift(static::$_args)).'\Ajax';
			}
			/**
			 * Sinon c'est que c'est un controleur interne au framework
			 */
			else
			{
				$controler = 'He\Module\\'.$target.'\Main';
			}
		}
		else
		{
			/**
			 * Sinon c'est un controleur standard du projet
			 */
			$controler = '\Module\\'.ucfirst($controler).'\Main';
		}
		
		if(!class_exists($controler))
			$controler = '\Module\Index\Main';
		
		return $controler;
	}
	
	/**
	 * Contruit la séquence d'affichage et d'appel aux différents controleurs.
	 * Cette séquence est sensé être le plus généraliste possible.
	 * @param string $controler
	 */
	protected static function _buildSequence($controler, $add_su_connect = false)
	{
		/* Chain d'appel de départ */
		foreach(static::$_chainBegin AS $needle => $class)
		{
			if(!static::$_onAjax || $class['ajax'])
				static::bindControl($class['name']);
		}
		
		/* Ajout du formulaire de connection en tant que SU si non ajax */
		if(!static::$_onAjax && $add_su_connect)
			static::bindControl('He\Module\Connectsu\Main');
		
		/* Appel standar au controleur */
		static::bindControl($controler);
		/* On réecrit le controleur en nom de fichier */
		$controler = str_replace('\\', '/', $controler);
		if($controler{0} == '/')
			$controler = substr($controler, 1);
		static::$_controler = $controler;
		
		/* Affichage du panneau SU sauf si ajax */ 
		if($_SESSION['SU'] && !static::$_onAjax)
		{
			static::bindControl('He\Module\SuPanel\Main');
			static::bindControl('He\Module\SuTrace\Main');
		}
		
		/* Chain d'appel de fin */
		foreach(static::$_chainLast AS $needle => $class)
			if(!static::$_onAjax || $class['ajax'])
				static::bindControl($class['name']);
	}
	
	/**
	 * Ajoute le controleur spécifié à la chaine de départ de la pile d'appel.
	 * Passer loadOnAjax à true pour afficher le rendu même en ajax
	 * @param string $controler
	 * @param bool $loadOnAjax 
	 */
	public static function bindAtBegin($controler, $loadOnAjax = false)
	{
		\He\Trace::addTrace('Ajout du controleur "'.$controler.'" au début de la pile d\'appels', get_called_class(), 1);
		static::$_chainBegin[] = array('name' => $controler, 'ajax' => $loadOnAjax);
	}
	
	/**
	 * Ajoute le controleur spécifié à la chaine de fin de la pile d'appel.
	 * Passer loadOnAjax à true pour afficher le rendu même en ajax
	 * @param string $controler
	 * @param bool $loadOnAjax 
	 */
	public static function bindAtLast($controler, $loadOnAjax = false)
	{
		\He\Trace::addTrace('Ajout du controleur "'.$controler.'" a la fin de la pile d\'appels', get_called_class(), 1);
		static::$_chainLast[] = array('name' => $controler, 'ajax' => $loadOnAjax);
	}
	
	/**
	 * Ajoute le controleur à la suite de la séquence à exécuter
	 * @param string $controler Nom de la class controleur à ajouter à la séquence
	 */
	public static function bindControl($controler)
	{
		\He\Trace::addTrace('Ajout du controleur "'.$controler.'" a la pile d\'appels', get_called_class());
		static::$_chain[] = $controler;
	}
	
	/**
	 * Enchaine l'exécution des controleurs demandés
	 */
	protected static function _chainControl()
	{
		\He\Trace::addTrace('Début chain control ', get_called_class(), -1);
		$control = array_shift(static::$_chain);
		do
		{
			static::_callControl($control);
		}
		while($control = array_shift(static::$_chain));
	}
	
	/**
	 * cherche le controleur spécifié et retourne le fichier correspondant.
	 * Si le fichier demandé est introuvable, on renvoi sur la page d'accueil.
	 * 
	 * @param string $controler nom du controleur
	 */
	protected static function _callControl($controler)
	{
		/**
		 * Si le controleur spécifié existe, on le charge
		 */
		if(class_exists($controler, true))
		{
			\He\Trace::addTrace('Appel du controleur "'.$controler.'"', get_called_class(), 1);
			if(!$controler::run())
			{
				\He\Trace::addTrace('ERREUR : Exécution du controleur '
						.$controler.' anormale !', get_called_class(), -2);
			}
		}
		/**
		 * Sinon chargement du controleur par défaut, pour éviter les erreurs 404
		 */
		else
		{
			\He\Trace::addTrace('Le controleur "'.$controler.'" est introuvable !', get_called_class(), -2);
		}
	}
	
	/**
	 * Affiche le panneau de control super admin
	 * @return true
	 */
	protected static function _showSuPanel()
	{
		$template = new \He\Template('he/su');
		$template->display();
		return true;
	}
	
	/**
	 * Retourne le paramètre passé en position x
	 * @param int $position
	 * @return mixed 
	 */
	public static function getArg($position)
	{
		if(!empty(static::$_args[$position]))
		{
			return static::$_args[$position];
		}
		else
		{
			\He\Trace::addTrace('L\'argument demandé en position '.$position.' n\'existe pas !', get_called_class(), -1);
			return null;
		}
	}
	
	/**
	 * Retourne la valeur de l'URI courante
	 * @return string
	 */
	public static function getUri()
	{
		if(!empty(static::$_uri))
			return static::$_uri;
		else
			return 'index';
	}
	
	/**
	 * Retourne le nom du controleur
	 * @return string
	 */
	public static function getControler()
	{
		if(!empty(static::$_controler))
			return static::$_controler;
		else
			return 'index';
	}
	
	/**
	 * Indique si on cherche à se connecter en tant que SU ou non
	 * @return bool
	 */
	public static function getAskSU()
	{
		return static::$_askSU;
	}
}