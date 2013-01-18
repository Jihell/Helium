<?php

/**
 * Description of supanel
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\Module\SuPanel;

final class Main extends \He\Control
{
	public static function run()
	{
		$template = \He\Template::bind(__DIR__.'/template/su.html');
		
		return true;
	}
}