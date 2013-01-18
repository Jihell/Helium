<?php
/**
 * class de gestion de chargement automatique de class.
 * 
 * =============================================================================
 * ATTENTION :
 * =============================================================================
 * Pour rajouter des appels en autoload, passez par le spl et non par la 
 * fonction magique __autoload() !
 * 
 * @author Joseph Lemoine - Lemoine.joseph@gmail.com
 * @version 3
 */
namespace He;

final class Autoload
{
	/**
	 * Chargement des class interne d'helium
	 * \He\Template\Node
	 * \He\DB
	 * @param string $className
	 * @return bool 
	 */
	public static function loadCore($className)
	{
		$className = str_replace('\\', '/', $className);
		$className = strtolower($className);
		
		$class_path = explode('/', $className);
		$root = array_shift($class_path);
		
		if($root == 'he')
		{
			$path .= ROOT.'/he/class/'.implode('/', $class_path);

			if(is_file($path.'.php'))
				include($path.'.php');
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Chargement des modules interne d'helium, exemple :
	 * \He\Module\Su\Main
	 * \He\Module\Su\Ajax
	 * @param string $className
	 * @return bool 
	 */
	public static function loadCoreModule($className)
	{
		$className = str_replace('\\', '/', $className);
		$className = strtolower($className);
		
		$class_path = explode('/', $className);
		$root = array_shift($class_path);
		
		if($root == 'he' &&
			$class_path[0] == 'module')
		{
			$path .= ROOT.'/he/module/'.$class_path[1].'/'.$class_path[2].'.php';

			if(is_file($path))
				include($path);
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Chargement des modules, exemple :
	 * \Module\Index\Main
	 * \Module\Home\Ajax
	 * @param string $className
	 * @return bool
	 */
	public static function loadModule($className)
	{
		$className = str_replace('\\', '/', $className);
		$className = strtolower($className);
		
		$class_path = explode('/', $className);
		$root = array_shift($class_path);
		
		if($root == 'module')
		{
			$path .= ROOT.'/module/'.$class_path[0].'/'.$class_path[1].'.php';
			
			if(is_file($path))
				include($path);
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Chargement des class externes, exemple :
	 * \External\PHPMailer
	 * @param string $className
	 * @return bool
	 */
	public static function loadExternal($className)
	{
		$className = str_replace('\\', '/', $className);
		$className = strtolower($className);
		
		$class_path = explode('/', $className);
		$root = array_shift($class_path);
		
		if($root == 'external')
		{
			$path .= ROOT.'/class/external/'.$class_path[0].'/'.$class_path[0].'.php';
			
			if(is_file($path))
				include($path);
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Chargement des class de job, exemple
	 * \Job\Math
	 * @param string $className
	 * @return bool
	 */
	public static function loadJob($className)
	{
		$className = str_replace('\\', '/', $className);
		$className = strtolower($className);
		
		$class_path = explode('/', $className);
		$root = array_shift($class_path);
		
		if($root == 'job')
		{
			$path .= ROOT.'/class/job/'.$class_path[0].'.php';
			
			if(is_file($path))
				include($path);
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Chargement des class hérité de find, exemple
	 * \He\DB\Find\Matable
	 * @param string $className
	 * @return bool
	 */
	public static function loadFind($className)
	{
		$className = str_replace('\\', '/', $className);
		$className = strtolower($className);
		
		$class_path = explode('/', $className);
		$root = array_shift($class_path);
		
		if($root == 'he' &&
			$class_path[0] == 'db' && 
			$class_path[1] == 'find')
		{
			$path .= ROOT.'/class/find/'.$class_path[2].'.php';
			
			if(is_file($path))
				include($path);
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Chargement des class hérité de find, exemple
	 * \He\DB\Find\Matable
	 * @param string $className
	 * @return bool
	 */
	public static function loadTableRef($className)
	{
		$className = str_replace('\\', '/', $className);
		$className = strtolower($className);
		
		$class_path = explode('/', $className);
		$root = array_shift($class_path);
		
		if($root == 'he' &&
			$class_path[0] == 'db' && 
			$class_path[1] == 'row' &&
			$class_path[2] == 'ref')
		{
			$path .= ROOT.'/class/table/ref/'.$class_path[3].'.php';
			
			if(is_file($path))
				include($path);
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Chargement des class hérité de row, exemple :
	 * \He\DB\Row\Matable
	 * @param string $className
	 * @return bool
	 */
	public static function loadTable($className)
	{
		$className = str_replace('\\', '/', $className);
		$className = strtolower($className);
		
		$class_path = explode('/', $className);
		$root = array_shift($class_path);
		
		if($root == 'he' &&
			$class_path[0] == 'db' && 
			$class_path[1] == 'row')
		{
			$path .= ROOT.'/class/table/'.$class_path[2].'.php';
			
			if(is_file($path))
				include($path);
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Charge les méthodes dans le SPL
	 */
	public static function init()
	{
		spl_autoload_register('He\Autoload::loadCore');
		spl_autoload_register('He\Autoload::loadCoreModule');
		spl_autoload_register('He\Autoload::loadModule');
		spl_autoload_register('He\Autoload::loadExternal');
		spl_autoload_register('He\Autoload::loadJob');
		spl_autoload_register('He\Autoload::loadFind');
		spl_autoload_register('He\Autoload::loadTableRef');
		spl_autoload_register('He\Autoload::loadTable');
	}
}