<?php
/**
 * Description of main
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace Module\Index;

class Main extends \He\Control
{
	public static function run()
	{
		$main = \He\Template::bind(__DIR__.'/template/testhepost.html');
		
		if(\He\POST::test('form/test'))
			$main->bindVarToNode('comment', 'Formulaire OK', 'comment');
		else
			$main->bindVarToNode('comment', 'Formulaire faux', 'comment');
		
		return true;
	}
}