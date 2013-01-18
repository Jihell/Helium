<?php
/**
 * Class abstraite pour les controleur.
 * Définie quelques méthodes utile pour précompiler les pages ainsi que
 * le fait que la class doit rester statique
 *
 * @author joseph lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He;

abstract class Control
{
	protected function __construct() {}
	protected function __clone() {}
	
	abstract public static function run();
	
	protected static function compile()
	{
		echo 'Compilation de '.__CLASS__.'<br/>';
	}
}