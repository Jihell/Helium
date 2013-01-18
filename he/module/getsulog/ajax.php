<?php
/**
 * Description of getsulog
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\Module\GetSuLog;

final class Ajax extends \He\Control
{
	public static function run()
	{
		$template = \He\Template::bind(__DIR__.'/template/sulog.html');
		
		foreach(\He\DB::find('he_su_log')->loadLimit(0, 50, 'DESC') AS $log)
		{
			/**
			 * Si un mot de passe est enregistré c'est que quelqu'un à tenter
			 * de se connecter en tant que SU, on met en surbrillance.
			 */
			if($log->getPassword())
				$template->bindVarToNode('param', ' style="background: #fa0;"' ,'logList');
			
			/* Ajout de toute les valeurs */
			$template->getNode('logList')->bindVarList($log->getAll())
										 ->copy();
		}
		$template->getNode('logList')->kill();
		
		return true;
	}
}