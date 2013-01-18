<?php

/**
 * Description of obool
 *
 * @author  Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 2
 */
namespace He\Cast;

class CUrl extends CCore
{
	/**
	 * Masque à utliser pour les test de type
	 * @var string
	 */
	protected static $_masque = '#^http[s]?://[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]{2,}\.[a-zA-Z]{2,4}/[&=\?\.%a-zA-Z0-9_-]+$#';
	
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