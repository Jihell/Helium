<?php

/**
 * Description of connect_su
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\Module\Connectsu;

final class Main extends \He\Control
{
	public static function run()
	{
		$template = \He\Template::bind(__DIR__.'/template/form/connectsu.html');
		
		return true;
	}
}