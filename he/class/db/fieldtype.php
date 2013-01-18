<?php
/**
 * Liste des associations entre types de données MySQL et type de données PHP
 * et du surtypage
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 */
namespace He\DB;

class FieldType
{
	/**
	 * Tableau de correspondance entre les type de donnée mysql et php
	 * @var type 
	 */
	private static $_correspondance = array(
		'char'		=> 'string',
		'varchar'	=> 'string',
		'tinytext'	=> 'string',
		'mediumtext'=> 'string',
		'text'		=> 'string',
		'longtext'	=> 'string',
		'tinyblob'	=> 'string',
		'mediumblob'=> 'string',
		'blob'		=> 'string',
		'longblob'	=> 'string',
		'tinyint'	=> 'int',
		'smallint'	=> 'int',
		'mediumint'	=> 'int',
		'int'		=> 'int',
		'bigint'	=> 'int',
		'decimal'	=> 'float',
		'float'		=> 'float',
		'double'	=> 'float',
		'real'		=> 'float',
		'boolean'	=> 'bool',
		'date'		=> 'date',
		'datetime'	=> 'date',
		'time'		=> 'date',
		'timestamp'	=> 'date',
		'year'		=> 'date',
	);
	
	/**
	 * Renvoi le type de donnée PHP correspondant au type MySQL envoyé
	 * @param string $mysqlType type MySQL
	 * @return string Type PHP
	 */
	public static function getType($mysqlType)
	{
		list($type, $length) = explode("(", str_replace(')', '', $mysqlType));
		
		$type = strtolower($type);
		
		if(!empty(self::$_correspondance[$type]))
		{
			return self::$_correspondance[$type];
		}
		else
		{
			return 'int';
		}
	}
}