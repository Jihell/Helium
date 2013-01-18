<?php
/**
 * Analyse la variable $_FILE et traite les différents fichiers s'y trouvant.
 * Extention de PHPUpload
 *
 * @author  Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 2
 */
namespace He;

class Upload extends \External\Upload
{
	public function __construct($file, $lang = 'en_GB')
	{
		\He\Trace::addTrace('Upload d\'un nouveau fichier', get_called_class());
		parent::__construct($file, $lang = 'en_GB');
	}
}