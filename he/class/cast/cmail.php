<?php

/**
 * Description of obool
 *
 * @author  Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 2
 */
namespace He\Cast;

class CMail extends CCore
{
	/**
	 * Masque à utliser pour les test de type
	 * @var string
	 */
	protected static $_masque = '#^[a-zA-Z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#';
	
	/**
	 * cast la valeur dans la variable $_val en float
	 */
	protected function cast()
	{
		$this->_val = (string) $this->_val;
	}
	
	/**
	 * Retourne la valeur de l'objet si envoyé dans un echo 
	 * (peu de chance, mais au cas où ...)
	 * @return mixed
	 */
	public function __toString()
	{
		if($this->_val)
			return "true";
		else
			return "false";
	}
}