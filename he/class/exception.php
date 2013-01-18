<?php

/**
 * Description of HeException
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 */
namespace He;

class Exception extends \Exception
{
    public function __construct($message, $code = null, $previous = null)
	{
		\He\Trace::addTrace($message, "FATAL ERROR !");
		\He\Trace::dump(true);
		parent::__construct($message, $code, $previous);
    }
}