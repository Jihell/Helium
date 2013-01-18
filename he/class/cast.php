<?php
/**
 * Class factory vers les class de surtypage
 * Selon le paramètre envoyé, la class retourne un objet de la class
 * adéquat.
 * @author  Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He;

class Cast
{
	/**
	 * Cette class ne DOIT pas être instancié
	 */
	private function __construct() {}
	
	/**
	 * Retourne un objet de surtypage correspondant au type Mysql envoyé
	 * @param string $type
	 * @param mixed $val
	 * @return OBJ extends Cast
	 */
	public static function build($type, $val = null)
	{
		$needed_type = explode("(", $type);
		
		switch ($needed_type[0])
		{
			case "smallint":
				return new Cast\CInt($val);
				break;
			case "mediumint":
				return new Cast\CInt($val);
				break;
			case "int":
				return new Cast\CInt($val);
				break;
			case "bigint":
				return new Cast\CDouble($val);
				break;
			case "float":
				return new Cast\CFloat($val);
				break;
			case "real":
				return new Cast\CFloat($val);
				break;
			case "tinyint":
				return new Cast\CBool($val);
				break;
			case "varchar":
				return new Cast\CString($val);
				break;
			case "char":
				return new Cast\CString($val);
				break;
			case "text":
				return new Cast\CString($val);
				break;
			case "datetime":
				return new Cast\CDate($val);
				break;
			case "timestamp":
				return new Cast\CDate($val);
				break;
			/**
			 * Surtypage pour formulaire
			 */
			case "date":
				return new Cast\CDate($val);
				break;
			case "mail":
				return new Cast\CMail($val);
				break;
			case "url":
				return new Cast\CUrl($val);
				break;
			case "select":
				return new Cast\CUrl($val);
				break;
			

			default:
				return NULL;
				break;
		}
	}
	
	public static function test($type, $val)
	{
		$type = explode("(", $type);
		
		switch($type[0])
		{
			case "mail":
				return Cast\Cmail::test($val);
			case "text":
				return Cast\Cstring::test($val);
		}
	}
}