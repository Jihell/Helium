<?php
/**
 * Class étendu de \He\DB\Row
 * Ne touchez pas à cette class, elle sera régénéré lors de la purge du cache !
 * Représentation objet d'une ligne de la table HE/cat (cat) 
 * 
 * @author: He@He\DB\DAO
 * @version: 1
 */
namespace He\DB\Row\Ref;

abstract class Cat extends \He\DB\Row
{
	/**
	 * Renvoi la valeur courante du champ id
	 * @return int
	 */
	public function getId()
	{
		return $this->_get('id');
	}

	/**
	 * Renvoi la valeur courante du champ label
	 * @return string
	 */
	public function getLabel()
	{
		return $this->_get('label');
	}

	/**
	 * Renvoi la valeur courante du champ id
	 * @param int $value
	 * @return $this
	 */
	public function setId($value)
	{
		$this->_set('id', $value);
		return $this;
	}

	/**
	 * Renvoi la valeur courante du champ label
	 * @param string $value
	 * @return $this
	 */
	public function setLabel($value)
	{
		$this->_set('label', $value);
		return $this;
	}

	/**
	 * Dépendance vers la table test
	 * @return array(\He\DB\Row\test)
	 */
	public function listTest()
	{
		return $this->_list('test');
	}

}