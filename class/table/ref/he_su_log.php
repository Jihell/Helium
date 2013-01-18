<?php
/**
 * Class étendu de \He\DB\Row
 * Ne touchez pas à cette class, elle sera régénéré lors de la purge du cache !
 * Représentation objet d'une ligne de la table HE/he_su_log (he_su_log) 
 * 
 * @author: He@He\DB\DAO
 * @version: 1
 */
namespace He\DB\Row\Ref;

abstract class He_su_log extends \He\DB\Row
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
	 * Renvoi la valeur courante du champ date
	 * @return date
	 */
	public function getDate()
	{
		return $this->_get('date');
	}

	/**
	 * Renvoi la valeur courante du champ ip
	 * @return string
	 */
	public function getIp()
	{
		return $this->_get('ip');
	}

	/**
	 * Renvoi la valeur courante du champ host
	 * @return string
	 */
	public function getHost()
	{
		return $this->_get('host');
	}

	/**
	 * Renvoi la valeur courante du champ port
	 * @return int
	 */
	public function getPort()
	{
		return $this->_get('port');
	}

	/**
	 * Renvoi la valeur courante du champ password
	 * @return string
	 */
	public function getPassword()
	{
		return $this->_get('password');
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
	 * Renvoi la valeur courante du champ date
	 * @param date $value
	 * @return $this
	 */
	public function setDate($value)
	{
		$this->_set('date', $value);
		return $this;
	}

	/**
	 * Renvoi la valeur courante du champ ip
	 * @param string $value
	 * @return $this
	 */
	public function setIp($value)
	{
		$this->_set('ip', $value);
		return $this;
	}

	/**
	 * Renvoi la valeur courante du champ host
	 * @param string $value
	 * @return $this
	 */
	public function setHost($value)
	{
		$this->_set('host', $value);
		return $this;
	}

	/**
	 * Renvoi la valeur courante du champ port
	 * @param int $value
	 * @return $this
	 */
	public function setPort($value)
	{
		$this->_set('port', $value);
		return $this;
	}

	/**
	 * Renvoi la valeur courante du champ password
	 * @param string $value
	 * @return $this
	 */
	public function setPassword($value)
	{
		$this->_set('password', $value);
		return $this;
	}

}