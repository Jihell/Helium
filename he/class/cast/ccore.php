<?php
/**
 * Class maitresse des différents surtypages.
 * Défini les méthodes par défaut des class de surtypage
 * 
 * @author  Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 3
 */
namespace He\Cast;
 
class CCore
{
	/**
	 * Masque à utliser pour les test de type
	 * @var string
	 */
	protected static $_masque = '#^(.*)?$#';
	
	/**
	 * Variable stockant la valeur à caster
	 * @var	mixed	$val
	 */
	protected $_val;
	
	/**
	 * Constructeur de class, donne à $_val sa valeur par défaut
	 * @param mixed $val 
	 */
	public function __construct($val)
	{
		$this->_val = $val;
		$this->_cast();
	}
	
	/**
	 * Quelque soit la variable que nous devons modifier, on envois les données
	 * à $_val
	 * @param string $name	Nom de la variable a définir
	 * @param mixed $value	Valeur à attribuer à la variable
	 */
	public function set($value)
	{
		$this->_val = $value;
		$this->_cast();
	}
	
	/**
	 * Cast la valeur dans $_val vers le type spécifique à la class hérité
	 */
	protected function _cast() {}
	
	/**
	 * Retourne la valeur de l'objet si envoyé dans un echo 
	 * (peu de chance, mais au cas où ...)
	 * @return mixed
	 */
	public function __toString()
	{
		return (string) $this->_val;
	}
	
	/**
	 * Renvoi la valeur stocké dans $_val
	 * @return mixed 
	 */
	public function export()
	{
		return $this->_val;
	}
	
	/**
	 * Renvoit true si $_val n'est pas vide, false si vide
	 * @return	bool
	 */
	public function bool()
	{
		return !empty($this->_val);
	}
	
	/**
	 * Test la valeur envoyé et répond vrai ou faux
	 * @return bool
	 */
	public static function test($val)
	{
		if(preg_match(static::$_masque, $val))
			return true;
		else
			return false;
	}
}