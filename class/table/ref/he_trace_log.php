<?php
/**
 * Class étendu de \He\DB\Row
 * Ne touchez pas à cette class, elle sera régénéré lors de la purge du cache !
 * Représentation objet d'une ligne de la table HE/he_trace_log (he_trace_log) 
 * 
 * @author: He@He\DB\DAO
 * @version: 1
 */
namespace He\DB\Row\Ref;

abstract class He_trace_log extends \He\DB\Row
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
	 * Renvoi la valeur courante du champ instant
	 * @return date
	 */
	public function getInstant()
	{
		return $this->_get('instant');
	}

	/**
	 * Renvoi la valeur courante du champ content
	 * @return string
	 */
	public function getContent()
	{
		return $this->_get('content');
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
	 * Renvoi la valeur courante du champ instant
	 * @param date $value
	 * @return $this
	 */
	public function setInstant($value)
	{
		$this->_set('instant', $value);
		return $this;
	}

	/**
	 * Renvoi la valeur courante du champ content
	 * @param string $value
	 * @return $this
	 */
	public function setContent($value)
	{
		$this->_set('content', $value);
		return $this;
	}

}