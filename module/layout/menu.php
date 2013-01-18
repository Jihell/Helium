<?php
/**
 * Description of Menu
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace Module\Layout;

final class Menu extends \He\Control
{
	public static function run()
	{
		$template = \He\Template::bind(__DIR__.'/template/menu.html');
		
		return true;
	}
}