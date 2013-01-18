<?php
/**
 * Retourne la liste des DAO pré chargés, si on envoi la confirmation de
 * supression, on détruit la liste des DAO.
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\Module\GetDAOList;

final class Ajax extends \He\Control
{
	public static function run()
	{
		/* Vérification de sécurité */
		if(!$_SESSION['SU'])
			return false;
		
		if(!empty($_POST['file']))
		{
			$template = \He\Template::bind(ROOT.'/he/template/div.html');
			$template->bindVar('var', nl2br(file_get_contents(DAO_CACHE_PATH.'/'.$_POST['file'])));
			
			return true;
		}
		
		/* Si on demande pas l'effacement */
		if(!$_POST['confirm'])
		{
			$template = \He\Template::bind(__DIR__.'/template/clearDao.html');
			
			$varList = \He\Dir::listFile(DAO_CACHE_PATH, 'daoName');
			if(!empty($varList))
				$template->getNode('daolist')->autoBinding($varList)->kill();
			else
				$template->getNode('daolist')->kill();
			
			return true;
		}
		/* Demande d'effacement des caches de DAO */
		else
		{
			\He\Dir::clear(DAO_CACHE_PATH, true);
			\He\Dir::clear(DAO_EXTENDS_PATH.'/ref/', true);
			$template = \He\Template::bind(__DIR__.'/template/clearDaoOk.html');
			
			return true;
		}
	}
}