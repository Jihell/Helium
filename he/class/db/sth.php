<?php
/**
 * Stock et renvois les singletons de Sth pour le chargement etc des tables
 * TODO : se débarasser de cette class (on passera les STH via He\PDO
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\DB;

final class Sth
{
	/**
	 * [bdd][table][job]
	 * @var array
	 */
	private static $_sth = array();
	
	/**
	 * Retourne les objets STH pré cahrgé pour une table précise
	 * @param string $bdd
	 * @param string $table
	 * @return array | null
	 */
	public static function &getCursor($bdd, $table)
	{
		return static::$_sth[$bdd][$table];
	}
}