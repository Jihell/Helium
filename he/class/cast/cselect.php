<?php

/**
 * Description of obool
 *
 * @author  Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 2
 */
namespace He\Cast;

class CBool extends CCore
{
	/**
	 * cast la valeur dans la variable $_val en float
	 */
	protected function cast()
	{
		$this->_val = (bool) $this->_val;
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