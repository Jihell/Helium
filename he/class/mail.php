<?php
/**
 * Class étendu du project externe PHPMailer : http://phpmailer.worxware.com/
 *
 * @author Joseph Lemoine - joseph.lemoine@gmail.com
 * @version 1
 */
namespace He;

class Mail extends \External\PHPMailer
{
	public function __construct()
	{
		\He\Trace::addTrace('Création d\'un nouveau mail', get_called_class());
	}
}