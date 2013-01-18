<?php

/**
 * Description of cstring
 *
 * @author  Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 2
 */
namespace He\Cast;

class CString extends CCore
{	
	/**
	 * cast la valeur dans la variable $_val en float
	 */
	protected function cast()
	{
		$this->_val = (string) $this->_val;
	}
	
	/**
	 * Renvoit la valeur avec les caractère HTML échapés
	 * @return string
	 */
	public function safeString()
	{
		return htmlspecialchars($this->exact());
	}
	
	/**
	 * Renvoit la valeur avec les caractère HTML échapés
	 * @return string
	 */
	public function exact()
	{
		return $this->_val;
	}
	
	/**
	 * Renvoi la valeur stocké dans $_val
	 * @return mixed 
	 */
	public function export()
	{
		return $this->safeString();
	}
	
	/**
	 * Test la valeur envoyé et répond vrai ou faux
	 * @return bool
	 */
	public static function test($val)
	{
		return true;
	}
}