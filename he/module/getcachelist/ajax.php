<?php
/**
 * Description of getCacheList
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\Module\GetCacheList;

final class Ajax extends \He\Control
{
	public static function run()
	{
		if(!$_SESSION['SU'])
			return false;
		
		if(!$_POST['confirm'])
		{
			$template = \He\Template::bind(ROOT.'/he/module/getcachelist/template/clearCache.html');
			
			static::_beginTree($template, \He\Dir::getTree(CACHE_PATH));
			
			$template->getNode('folderList/fileList')->kill();
			
			return true;
		}
		else
		{
			\He\Dir::clear(CACHE_PATH, true);
			$template = \He\Template::bind(ROOT.'/he/module/getcachelist/template/clearCacheOk.html');
			
			return true;
		}
	}
	
	/**
	 * Créer le début de l'arboressence de fichier et dossier dans le template 
	 * indiqué, enchaine sur la récursion.
	 * @param \He\Template $template
	 * @param string $path 
	 * @return bool
	 */
	private static function _beginTree(&$template, \He\Dir\Folder $folder)
	{
		$deep = \He\Template::makeNode(__DIR__.'/template/clearCacheTree.html');
		static::_makeTree($deep, $folder);
		$deep->getNode('folderList/fileList')->kill();
		$varList = array();
		$varList['objectType'] = 'HeFolder';
		$varList['file'] = $folder->getName();
		$varList['deep'] = $deep->getContent();
		
		/* Nettoyage */
		unset($deep);
		
		$template->getNode('folderList/fileList')->bindVarList($varList)
												 ->copy();
	}
	
	/**
	 * Créer une arboressence de fichier et dossier dans le template indiqué
	 * @param \He\Template $template
	 * @param string $path 
	 * @return bool
	 */
	private static function _makeTree(&$template, \He\Dir\Folder $folder)
	{	
		if($folder->isEmpty())
			return true;
		/* Arboressence des dossiers */
		if($folder->haveFolders()) 
		{
			$varList = array();
			foreach($folder->getFolders() AS $obj)
			{
				/* Récursion */
				$deep = \He\Template::makeNode(__DIR__.'/template/clearCacheTree.html');
				static::_makeTree($deep, $obj);
				$deep->getNode('folderList/fileList')->kill();

				$varList['objectType'] = 'HeFolder';
				$varList['file'] = $obj->getName();
				$varList['deep'] = $deep->getContent();

				/* Nettoyage */
				unset($deep);

				$template->getNode('folderList/fileList')->bindVarList($varList)
														 ->copy();
			}
		}

		/* Arboressence des fichiers */
		if($folder->haveFiles()) 
		{
			$varList = array();
			foreach($folder->getFiles() AS $obj)
			{
				$varList['objectType'] = 'HeFile';
				$varList['file'] = $obj->getName();

				$template->getNode('folderList/fileList')->bindVarList($varList)
														 ->copy();
			}
		}
	}
}