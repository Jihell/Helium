<?php

/**
 * Description of odate
 *
 * @author  Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 2
 */
namespace He\Cast;

class CDate extends CString
{
	/**
	 * retourne la date sous un format fr ou us selon les variables de session
	 */
	protected function dateFormat()
	{
		// TODO créer l'algo qui fait ça bien
		return $this->_val;
	}
}