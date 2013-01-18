<?php
/**
 * Gestion des sessions
 * 
 * Permet de gérer les sessions en base de donnée comme en fonctionnement
 * habituel.
 * 
 * =============================================================================
 * USAGE COURANT
 * =============================================================================
 * 
 * He\Session::init();
 * 
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 2
 */
namespace He;

final class Session
{
	 const useBDD = false;
     const use_only_cookies = true;
     const gc_maxlifetime = 7200;
	
	/**
	 * Constructeur et clone privé, pour ne pas instancier cette class
	 */
	private function __construct() {}
	private function __clone() {}
	
	/**
	 * Initialise the PDO session handler
	 * @param PDO PDO instance to use for database
	 */
	public static function init()
	{
		// Add the session handlers if we have to use BDD
		if(static::useBDD)
			session_set_save_handler('HeSession::open', 'HeSession::close', 
									 'HeSession::read', 'HeSession::write', 
									 'HeSession::destroy', 'HeSession::garbageCollect');
		
		ini_set('session.use_only_cookies', static::use_only_cookies);
		ini_set('session.gc_maxlifetime', static::gc_maxlifetime);
		session_start();
	}

	/**
	 * Session open handler
	 * @param string Path to save session to
	 * @param string Name of the session
	 */
	public static function open($save_path, $session_name)
	{
		// Nothing
		return true;
	}

	/**
	 * Session close handler
	 */
	public static function close()
	{
		// Nothing
		return true;
	}

	/**
	 * Session load handler. Load the session
	 * @param string Session ID
	 */
	public static function read($session_id)
	{
		// Load the session data from the database
		$query = PDO::getInstance()->prepare('
			SELECT data
			FROM he_sessions
			WHERE session_id = :session_id');
		$query->execute(array(':session_id' => $session_id));

		return $query->fetchColumn();
	}

	/**
	 * Session save handler. Save the session
	 * @param string Session ID
	 * @param string Data to save to session
	 */
	public static function write($session_id, $data)
	{
		PDO::getInstance()
			->prepare('
				REPLACE INTO he_sessions
					(session_id, data, last_activity)
				VALUES
					(:session_id, :data, :last_activity)')
			->execute(array(
				':session_id' => $session_id,
				':data' => $data,
				'last_activity' => time())
			);
	}

	/**
	 * Session delete handler. Delete the session from the database
	 * @param string Session ID
	 */
	public static function destroy($session_id)
	{
		PDO::getInstance()
			->prepare('
				DELETE FROM he_sessions
				WHERE session_id = :session_id')
			->execute(array(':session_id' => $session_id));
	}

	/**
	 * Session garbage collector. Delete any old expired sessions
	 * @param int How many seconds do sessions last for?
	 */
	public static function garbageCollect($lifetime)
	{               
		PDO::getInstance()
			->prepare('
				DELETE FROM he_sessions
				WHERE last_activity < :min_time')
			->execute(array(':min_time' => time() - $lifetime));
	}
}