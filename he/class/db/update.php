<?php
/**
 * Class de listage par défaut d'une table, à hériter aux classes de listage.
 * COntient les méthodes de sélection basique.
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\DB;

class Update
{
	/**
	 * Liste des paramètes de la table
	 * @var \He\DB\Param
	 */
	protected $_param;
	
	/**
	 * Active les paramètres de la table
	 * @param \He\DB\Param $param paramètre de la DAO
	 */
	public function __construct(\He\DB\Param $param)
	{
		\He\Trace::addTrace('Contruction de l\'updater de : '.$param->alias, get_called_class());
		$this->_param = $param;
		$this->_param->setActive();

		$this->_sth = &\He\DB\Sth::getCursor($param->bdd, $param->table);
	}
	
	/**
	 * Crée une requète à partir de la liste de champs $fields à appliquer si
	 * les conditions de $selector sont remplis
	 * @param array $fields
	 * @param string $selector
	 * @return string
	 */
	protected function _buildRequest($fields, $selector = '')
	{
		if(empty($fields))
			return false;
		
		$sql = 'UPDATE '.$this->_param->table.' SET ';
		foreach($fields AS $f)
			$sql .= $f.' = :'.$f.', ';
		
		$sql = substr($sql, 0, -2);
		
		if(!empty($selector))
			$sql .= ' WHERE '.$selector;
		
		return $sql;
	}
}