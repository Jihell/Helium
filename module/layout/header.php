<?php
/**
 * Description of Header
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace Module\Layout;

final class Header extends \He\Control
{
	public static function run()
	{
		$template = \He\Template::bind(__DIR__.'/template/head.html');
		
		return true;
	}
}