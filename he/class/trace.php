<?php
/**
 * Eregistre les séquences d'appel et leur date d'exécution
 *
 * @author  Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 3
 */
namespace He;

class Trace 
{
	/**
	 * Instance de class (singleton)
	 * @var type 
	 */
	private static $_instance;
	
	/**
	 * Indique si on doit bloquer l'ajout de traces
	 * @var bool
	 */
	private static $_silent = false;
	
	/**
	 * Indique si on affiche ou pas les trace au destruct
	 * @var bool
	 */
	private static $_noTrace = false;
	
	/**
	 * Instant d'initialisation de l'objet, et donc de la page
	 * @var string
	 */
	private $_iniTime = 0;
	
	/**
	 * Liste des messages
	 * @var array	[mircrotime] = message
	 */
	private $_trace = array();
	
	/**
	 * Liste des trace des objets
	 * \He\PDO ou \He\PDOStatement
	 * @var type 
	 */
	private $_traceBdd = array();
	
	/**
	 * Liste des trace de l'objet \He\Dispatch
	 * @var type 
	 */
	private $_traceDispatch = array();

	
	/**
	 * Compte le nombre de requètes exécutés pour générer la page
	 * @var int
	 */
	private $_queryCount = 0;
	
	/**
	 * Taile maximum du sript en ram
	 * @var float
	 */
	private $_maxRam = 0;
	
	/**
	 * Coloration en fonction de la gravité du message
	 * @var array 
	 */
	private static $_degreCode = array( -2	=> '255,0,0',
										-1	=> '255,160,0',
										0	=> '0,0,80',
										1	=> '128,224,128',
										2	=> '128,192,224');
	
	/**
	 * Cette class doit être appellé en static uniquement
	 */
	private function __construct() {}
	private function __clone() {}
	
	private function _getInstance()
	{
		if(!self::$_instance)
			self::$_instance = new static();
			
		return self::$_instance;
	}
	
	/**
	 * Test si on peu écrire un trace
	 * @return bool 
	 */
	private static function _canTrace()
	{
		if((TRACE || $_SESSION['SU']) && !self::$_noTrace)
			return true;
		else
			return false;
	}
	
	/**
	 * Passe la variable static $_silent à true
	 * @param void
	 * @return void
	 */
	public static function mute()
	{
		self::$_silent = true;
	}
	
	/**
	 * Passe la variable static $_silent à true
	 * @param void
	 * @return void
	 */
	public static function hide()
	{
		self::$_noTrace = true;
	}
	
	/**
	 * Initialise la pile de messages si on est en débug
	 */
	public static function init()
	{
		if(static::_canTrace())
		{
			self::_getInstance()->_iniTime = microtime();
			self::addTrace('Hello', 'Init', -2);
		}
	}
	
	/**
	 * Ajoute 1 à la variable de benchmark des requètes
	 * @param VOID
	 * @return VOID
	 */
	public static function addCount()
	{
		if(static::_canTrace())
		{
			self::_getInstance()->_queryCount++;
		}
	}
	
	/**
	 * Ajoute un évènement dans la pile d'appel si on est en débug
	 * @param string $msg
	 * @param string $type
	 * @param int $degre	Colorisation de la ligne dans l'interface de bug
	 */
	public static function addTrace($msg, $type, $degre = 0)
	{
		if(static::_canTrace())
		{
			$ist = microtime();
			self::_getInstance()->_trace[$ist]['type'] = $type;
			self::_getInstance()->_trace[$ist]['msg'] = $msg;
			self::_getInstance()->_trace[$ist]['degre'] = static::$_degreCode[$degre];
			self::_getInstance()->_trace[$ist]['time'] = self::_instant();
			self::_getInstance()->_trace[$ist]['ram'] = self::_getRam();
			
			if($type == 'He\Dispatch' || 
				$type == 'Dump' || 
				$type == 'Init')
			{
				self::_getInstance()->_traceDispatch[$ist] = &self::_getInstance()->_trace[$ist];
			}
			if($type == 'He\PDO' ||
				$type == 'He\PDOStatement' ||
				$type == 'Dump' || 
				$type == 'Init')
			{
				self::_getInstance()->_traceBdd[$ist] = &self::_getInstance()->_trace[$ist];
			}
		}
	}
	
	/**
	 * Récupère le temps écoulé depuis l'initialisation de l'objet
	 * ainsi que l'utilisation mémoire
	 * @return string	Message avec temps écoulé + RAM 
	 */
	private static function _instant($fromTime = null, $toTime = null)
	{
		$refTime = (empty($fromTime) ? self::_getInstance()->_iniTime : $fromTime);
		$newTime = (empty($toTime) || empty($fromTime) ? microtime() : $toTime);
		
		/* on sépare les secondes et les millisecondes */
		list($micro1, $time1) = explode(' ', $refTime);
		list($micro2, $time2) = explode(' ', $newTime);
		
		/* on calcule le nombre de secondes qui séparent les 2 */
		$time = $time2 - $time1;
		
		/* On cast, au cas où */
		$micro1 = (FLOAT)$micro1;
		$micro2 = (FLOAT)$micro2;
		
		/* si le nombre de millisecondes du 1° temps est supérieur au 2°, 
		 * C'est qu'il s'est écoulé au moins une seconde compète
		 */
		if ($micro1 > $micro2)
		{
			$time--;
			$micro = 1.0 + $micro2 - $micro1;
		}
		/* sinon, on fait juste la différence */
		else
		{
			$micro = $micro2 - $micro1;
		}
		
		/* On fait la somme du temps transformé en float */
		return number_format(($micro + $time)* 1000, 0);
	}
	
	/**
	 * Récupère la mémoire utilisé par le script
	 * @return string
	 */
	private static function _getRam()
	{
		$ram = number_format((memory_get_usage() / 1024 / 1024), 3);
		if($ram > self::_getInstance()->_maxRam)
			self::_getInstance()->_maxRam = $ram;
		
		return $ram;
	}
	
	/**
	 * Affiche les évènements
	 */
	public static function dump($echo = false)
	{
		/* Ajout de GoodBye*/
		self::addTrace('GoodBye', 'Dump', -2);
		self::mute();
		
		$hetrace = \He\Template::makeNode(ROOT.'/he/module/sutrace/template/trace.html');
		/* Alias */
		$hetrace = $hetrace->getNode('trace');
		
		/* Ajout de la pile d'appel générale */
		$prevMt = null;
		foreach(self::_getInstance()->_trace AS $mt => $varList)
		{
			$between = empty($prevMt) ? number_format(0, 5) : self::_instant($prevMt, $mt);
			$hetrace->bindVarListToNode($varList, 'traceTous');
			$hetrace->bindVarToNode('between', $between, 'traceTous');
			$hetrace->getNode('traceTous')->copy();
			$prevMt = $mt;
		}
		$hetrace->getNode('traceTous')->kill();
		
		/* Ajout de la pile d'appel bdd */
		$prevMt = null;
		foreach(self::_getInstance()->_traceBdd AS $mt => $varList)
		{
			$between = empty($prevMt) ? number_format(0, 5) : self::_instant($prevMt, $mt);
			$hetrace->bindVarListToNode($varList, 'traceBdd');
			$hetrace->bindVarToNode('between', $between, 'traceBdd');
			$hetrace->getNode('traceBdd')->copy();
			$prevMt = $mt;
		}
		$hetrace->getNode('traceBdd')->kill();
		
		/* Ajout de la pile d'appel Dispatcher */
		$prevMt = null;
		foreach(self::_getInstance()->_traceDispatch AS $mt => $varList)
		{
			$between = empty($prevMt) ? number_format(0, 5) : self::_instant($prevMt, $mt);
			$hetrace->bindVarListToNode($varList, 'traceDispatcher');
			$hetrace->bindVarToNode('between', $between, 'traceDispatcher');
			$hetrace->getNode('traceDispatcher')->copy();
			$prevMt = $mt;
		}
		$hetrace->getNode('traceDispatcher')->kill();
		
		/* Détail des variables en session */
		foreach($_SESSION as $name => $var)
		{
			$hetrace->bindVarToNode('name', $name, 'traceSession', true);
			$hetrace->bindVarToNode('value', static::var_dump($var), 'traceSession', true);
			$hetrace->getNode('traceSession')->copy();
		}
		$hetrace->getNode('traceSession')->kill();
		
		/* Détail des variables en cookies */
		foreach($_COOKIE as $name => $var)
		{
			$hetrace->bindVarToNode('name', $name, 'traceCookie', true);
			$hetrace->bindVarToNode('value',  static::var_dump($var), 'traceCookie', true);
			$hetrace->getNode('traceCookie')->copy();
		}
		$hetrace->getNode('traceCookie')->kill();
		
		/* Ajout des benchs */
		$hetrace->bindVarToNode('queryCount', self::_getInstance()->_queryCount, 'bench');
		$hetrace->bindVarToNode('totalTime', self::_instant(self::_getInstance()->_iniTime, $mt), 'bench');
		$hetrace->bindVarToNode('maxRam', self::_getInstance()->_maxRam, 'bench');
		$hetrace->bindVarToNode('requestedUri', \He\Dispatch::getUri(), 'bench');
		
		$prev_trace = \He\DB::find('he_trace_log')->getMax();
		$hetrace->bindVarToNode('previd', $prev_trace, 'bench');
		$hetrace->bindVarToNode('id', $prev_trace + 1, 'bench');
		$hetrace->bindVarToNode('nextid', $prev_trace + 2, 'bench');
		
		/* Envoi */
		if(!self::$_noTrace)
		{	
			/* Log de la trace ~déplayé */
			\He\DB::he_trace_log()->setInstant('NOW()')
								  ->setContent($hetrace->getContent())
								  ->stor(true);
		}
		
		/* Affiche les traces en cas de crash */
		if($_SESSION['SU'] && $echo)
		{
			$hetrace->bindVar('forceDisplay', ' style="display: block;"');
			echo $hetrace->getContent();
			exit();
		}
		else
		{
			\He\Template::addContent($hetrace->getContent());
		}
	}
	
	/**
	 * Renvoi un var dump
	 * @param mixed $var
	 * @return string 
	 */
	public static function var_dump($var)
	{
		ob_start(); 
		var_dump($var);
		return ob_get_clean();
	}
	
	/**
	 * Affiche le dump automatiquement
	 */
	public function __destruct()
	{
		if(static::_canTrace())
			self::dump();
	}
}