<?php

/**
 * Description of login
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace Module\Layout;

final class Login extends \He\Control
{
	public static function run()
	{
		$template = \He\Template::bind(__DIR__.'/template/login.html');
		
		return true;
	}
}