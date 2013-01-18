<?php
/**
 * Stock et renvois les requètes en attente
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\Db;

final class Pending
{
	/**
	 * [bdd][table][job]
	 * @var array
	 */
	private static $_pending = array();
	
	private static $_cursorLimit = 2000;
	
	/**
	 * Retourne les objets STH pré cahrgé pour une table précise
	 * @param string $bdd
	 * @param string $table
	 * @return array | null
	 */
	public static function &getCursor($bdd, $table)
	{
		return static::$_pending[$bdd][$table];
	}
	
	public static function getLimit()
	{
		return static::$_cursorLimit;
	}
}