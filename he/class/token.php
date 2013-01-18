<?php
/**
 * Manage the diferents operations about the $_POST reception
 * Class MUST be call by the static method.
 * ============================================================================
 * Common usage :
 * In the view side
 * - <input type="hidden" name="token" value="<?php echo Token::extract() ?>" />
 * In the control side
 * - if($formIsValid && Token::check()) {doSomeStuff()}
 * ============================================================================
 * 
 * @author Joseph Lemoine - joseph.lemoine@gmail.com
 * @version	2
 */
namespace He;

class Token
{
	/**
	 * Store if there are any time where the token have been test obselete
	 * @var bool
	 */
	public static $obselete = false;
	/**
	 * Forbid making instances of this class, is must be call by static method
	 */
	private function __construct() {}
	private function __clone() {}
	
	/**
	 * Put a new token in session
	 */
	private static function _make()
	{
		$_SESSION["token"] = md5(uniqid());
	}
	
	/**
	 * Check if the token found in $_POST["token"] is the same as the one in
	 * the session. If it's the same, renew the token in session. and send true,
	 * else, send false
	 * @return	bool
	 */
	public static function check()
	{
		if($_SESSION["token"] == $_POST["token"])
		{
			self::$obselete = true;
			self::_make();
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Extract the token stored in the session var. If there isn't a token,
	 * build one.
	 * @return	string	Token who can be use in any form but Ajax
	 */
	public static function extract()
	{
		if(empty($_SESSION["token"]))
		{
			self::_make();
		}
		
		return $_SESSION["token"];
	}
}
?>
