<?php
/**
 * Class de listage sous forme d'objets d'un fichier. Suivant le design 
 * pattern composite
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\Dir;

class File
{
	/**
	 * Dossier parent de ce fichier
	 * @var \He\Dir\Folder
	 */
	protected $_parent;
	
	/**
	 * Nom du fichier
	 * @var string
	 */
	protected $_name;
	
	/**
	 * Extention du fichier
	 * @var string
	 */
	protected $_type;
	
	/**
	 * Contenue du fichier
	 * @var type 
	 */
	protected $_content;
	
	/**
	 * Constructeur de class, donne son nom et extention au fichier
	 * @param string $name Nom du fichier
	 * @param \He\Dir\Folder $parent dossier parent du fichier
	 */
	public function __construct($name, \He\Dir\Folder $parent)
	{
		$this->_name = $name;
		$this->_parent = $parent;
		$this->_type = array_pop(explode('.', $name));
	}
	
	/**
	 * Renvoi le nom du fichier
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}
	
	/**
	 * renvoi le type d'extention du fichier
	 * @return string
	 */
	public function getType()
	{
		return $this->_type;
	}
	
	/**
	 * Renvoi le dossier parent s'il existe ou faux si il n'est pas défini
	 * @return \He\Dir\Folder || false
	 */
	public function getParent()
	{
		if(!empty($this->_parent))
		{
			return $this->_parent;
		}
		return false;
	}
	
	/**
	 * Récupère le chemin absolue vers ce fichier. Si il n'a pas de dossier
	 * attribué, on renvoi une chaine vide
	 * @return string
	 */
	public function getPath()
	{
		if(!empty($this->_parent))
		{
			return $this->getParent()->getPath().'/'.$this->getName();
		}
	}
}