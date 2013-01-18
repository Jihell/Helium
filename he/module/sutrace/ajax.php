<?php
/**
 * Description of gettracelog
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\Module\Sutrace;

final class Ajax extends \He\Control
{
	public static function run()
	{
		if(!$_SESSION['SU'])
			return false;
		
		\He\Trace::hide();
		
		if(empty($_POST['id']))
			$_POST['id'] = 1;
		
		if(is_numeric($_POST['id']))
		{
			$log = \He\DB::he_trace_log($_POST['id']);
			if(!$log->exist())
			{
				$last_id = \He\DB::find('he_trace_log')->getMax();
				$log = \He\DB::he_trace_log($last_id);
			}
			
			$template = \He\Template::bind(ROOT.'/he/template/empty.html');
			$template->bindVar('var', $log->getContent());
			
			return true;
		}
		
		return false;
	}
}