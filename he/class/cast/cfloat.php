<?php
/**
 * Surtypage pour les valeur de type FLOAT
 * La valeur enregistré dans cette class peut se voir apliquer des méthodes
 * simplifiant sa manipulation
 * @author  Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 2
 */
namespace He\Cast;

class CFloat extends CCore
{
	/**
	 * Masque à utliser pour les test de type
	 * @var string
	 */
	protected static $_masque = '#^[0-9 ]+[\.|,]+[0-9]+$#';
	
	/**
	 * cast la valeur dans la variable $_val en float
	 */
	protected function cast()
	{
		$this->_val = (float) $this->_val;
	}
	
	/**
	 * Renvoit la valeur formaté
	 * @return string
	 */
	public function numberFormat()
	{
		return number_format($this->export(), 2, ".", " ");
	}
}