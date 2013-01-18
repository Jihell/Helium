<?php

/**
 * Description of sutrace
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\Module\SuTrace;

final class Main extends \He\Control
{	
	public static function run()
	{
		\He\Trace::dump();
		
		return true;
	}
}