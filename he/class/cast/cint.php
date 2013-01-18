<?php

/**
 * Description of oint
 *
 * @author  Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 2
 */
namespace He\Cast;
 
class CInt extends CCore
{
	/**
	 * Masque à utliser pour les test de type
	 * @var string
	 */
	protected static $_masque = '#^[0-9 ]+$#';
	
	/**
	 * cast la valeur dans la variable $_val en float
	 */
	protected function cast()
	{
		$this->_val = (int) $this->_val;
	}
	
	/**
	 * Renvoit la valeur formaté
	 * @return string
	 */
	public function numberFormat()
	{
		return number_format($this->export(), 0, ".", " ");
	}
}