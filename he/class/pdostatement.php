<?php
/**
 * Class étendu de PDOStatement pour permettre les bench de requète et 
 * d'exécution / fetching etc.
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 */
namespace He;

final class PDOStatement extends  \PDOStatement
{
	/**
	 * Surcharge de PDOStatement::execute() pour permettre la gestion des erreur
	 * et l'ajout de traces / benchmark
	 * @param type $input_parameters 
	 */
	public function execute($input_parameters = array())
	{
		\He\Trace::addTrace('Execution de la requète "'.$this->queryString.'" | '.print_r($input_parameters, 1), get_called_class(), 2);
		\He\Trace::addCount();
		try
		{
			return parent::execute($input_parameters);
		}
		catch(\PDOException $e)
		{
			throw new \He\Exception($e->getMessage());
		}
	}
}