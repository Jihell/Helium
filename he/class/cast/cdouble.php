<?php

/**
 * Description of CDouble
 *
 * @author  Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 2
 */
namespace He\Cast;

class CDouble extends CInt
{
	/**
	 * cast la valeur dans la variable $_val en float
	 */
	protected function cast()
	{
		$this->_val = (double) $this->_val;
	}
}