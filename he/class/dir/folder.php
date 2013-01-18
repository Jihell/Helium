<?php
/**
 * Class de listage sous forme d'objets d'un répertoire. Suivant le design 
 * pattern composite
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 1
 */
namespace He\Dir;

class Folder
{
	/**
	 * Objet parent de ce sous dossier. Vide si racine
	 * @var He\Dir\Folder 
	 */
	protected $_parent;
	
	/**
	 * Nom du dossier
	 * @var string
	 */
	protected $_name = '';
	
	/**
	 * Chemin absolue du dossier
	 * @var string
	 */
	protected $_path = '';
	
	/**
	 * Liste des fichiers présents dans le dossier
	 * @var array \He\Dir\File
	 */
	protected $_files = array();
	
	/**
	 * Liste des dossiers présents dans le dossier
	 * @var array \He\Dir\File
	 */
	protected $_folders = array();
	
	/**
	 * Constructeur de class, donne son nom au dossier
	 * @param type $name 
	 */
	public function __construct($name, $parent = null)
	{
		/* Anti bug pour les système windows */
		$name = str_replace('\\', '/', $name);
		
		$this->_name = $name;
		$this->_parent = $parent;
		
		/* Récupération du chemin absolue */
		if($this->getParent())
		{
			$this->_path = $this->getParent()->getPath().'/'.$this->_name;
		}
		else
		{
			$this->_path = $this->_name;
			if(substr($this->_path, -1) == '/')
			{
				$this->_path = substr($this->_path, 0, -1);
			}
		}
	}
	
	/**
	 * Tranforme un chemin en tableau de sous dossiers
	 * @param string $name
	 * @return array 
	 */
	protected function _nameToArray($name)
	{
		/* Anti bug pour les système windows */
		$name = str_replace('\\', '/', $name);
		
		return explode('/', $name);
	}
	
	/**
	 * Transforme un tableau de sous dossier en chemin
	 * @param array $name
	 * @return string 
	 */
	protected function _arrayToName($path)
	{
		return implode('/', $path);
	}
	
	/**
	 * Ajoute un sous-dossier à ce dossier. Si le sous dossier intermédiaire
	 * n'existe pas il est automatiquement créer
	 * @param string $name chemin
	 * @return \He\Dir\Folder Le dernier créer
	 */
	public function addFolder($name)
	{
		if(empty($name))
		{
			\He\Trace::addTrace('ERREUR : le nom de fichier est null', get_called_class(), -2);
			throw new \He\Exception('ERREUR : le nom de fichier est null');
			return false;
		}
		
		$path = $this->_nameToArray($name);
		
		/* Si on est en fin de chemin */
		if(count($path) == 1)
		{
			if(empty($this->_folders[$name]))
			{
				$this->_folders[$name] = new static($name, $this);
				return $this;
			}
			else
			{
				\He\Trace::addTrace('Impossible d\'attribuer le nom "'.$name
						.'" à un nouveau sous répertoire de "'.$this->_name
						.'". Le dossier existe déjà !', get_called_class(), -2);
			}
		}
		/* Si on est dans un chemin intermédiaire */
		else
		{
			$intermediaire = array_shift($path);
			if(empty($this->_folders[$intermediaire]))
			{
				$this->_folders[$intermediaire] = new static($intermediaire, $this);
			}
			
			return $this->_folders[$intermediaire]->addFolder($this->_arrayToName($path));
		}
	}
	
	/**
	 * Ajoute un fichier au dossier. Si le sous dossier intermédiaire
	 * n'existe pas il est automatiquement créer
	 * @param string $name chemin du nouveau fichier
	 * @return \He\Dir\File Le dernier créer
	 */
	public function addFile($name)
	{
		if(empty($name))
		{
			\He\Trace::addTrace('ERREUR : le nom de fichier est null', get_called_class(), -2);
			throw new \He\Exception('ERREUR : le nom de fichier est null');
			return false;
		}
		
		$path = $this->_nameToArray($name);
		
		/* Si on est en fin de chemin */
		if(count($path) == 1)
		{
			if(empty($this->_files[$name]))
			{
				$this->_files[$name] = new \He\Dir\File($name, $this);
				return $this;
			}
			else
			{
				\He\Trace::addTrace('Impossible d\'attribuer le nom "'.$name
						.'" à un nouveau fichier de "'.$this->_name
						.'". Le fichier existe déjà !', get_called_class(), -2);
			}
		}
		/* Si on est dans un chemin intermédiaire */
		else
		{
			$intermediaire = array_shift($path);
			if(empty($this->_files[$intermediaire]))
			{
				$this->_files[$intermediaire] = new static($intermediaire, $this);
			}
			
			return $this->_files[$intermediaire]->addFile($this->_arrayToName($path));
		}
	}
	
	/**
	 * Associe le fichier passé en paramètre à ce dossier.
	 * @param \He\Dir\File $file
	 * @return Folder 
	 */
	public function bindFile(\He\Dir\File $file)
	{
		if(empty($this->_files[$file->getName()]))
		{
			$this->_files[$file->getName()] = $file;
			return $this;
		}
		else
		{
			\He\Trace::addTrace('Impossible d\'associer le fichier au dossier "'
					.$this->_name.'", il existe déjà !', get_called_class(), -2);
			throw new \He\Exception('Impossible d\'associer le fichier au dossier "'
					.$this->_name.'", il existe déjà !');
			return false;
		}
	}
	
	/**
	 * Associe le sous-dossier passé en paramètre à ce dossier.
	 * @param \He\Dir\Folder $file
	 * @return Folder 
	 */
	public function bindFolder(\He\Dir\Folder $folder)
	{
		if(empty($this->_folders[$folder->getName()]))
		{
			$this->_folders[$folder->getName()] = $folder;
			return $this;
		}
		else
		{
			\He\Trace::addTrace('Impossible d\'associer le sous-dossier au dossier "'
					.$this->_name.'", il existe déjà !', get_called_class(), -2);
			throw new \He\Exception('Impossible d\'associer le sous-dossier au dossier "'
					.$this->_name.'", il existe déjà !');
			return false;
		}
	}
	
	/**
	 * Retourne le nom de ce dossier
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}
	
	/**
	 * Renvoi l'objet représentant le fichier demandé
	 * @param string $name
	 * @return \He\Dir\File 
	 */
	public function getFile($name)
	{
		$path = $this->_nameToArray($name);
		
		/* Si on est en fin de chemin */
		if(count($path) == 1)
		{
			if(!empty($this->_files[$name]))
			{
				return $this->_files[$name];
			}
			else
			{
				\He\Trace::addTrace('Le fichier "'.$name
						.'" n\'existe pas dans le dossier "'.$this->_name.'"', 
						get_called_class(), -1);
			}
		}
		else
		{
			$intermediaire = array_shift($path);
			if(!empty($this->_folders[$intermediaire]))
			{
				return $this->_folders[$intermediaire]->getFile($this->_arrayToName($path));
			}
			else
			{
				\He\Trace::addTrace('ERREUR : Le sous dossier demandé "'
						.$intermediaire.'" pour récupérer le fichier "'
						.$name.'" n\'existe pas !', get_called_class());
				throw new \He\Exception('ERREUR : Le sous dossier demandé "'
						.$intermediaire.'" pour récupérer le fichier "'
						.$name.'" n\'existe pas !');
				return false;
			}
		}
	}
	
	/**
	 * Retourne le tableau d'objets représentant les fichiers de ce dossier
	 * @return array
	 */
	public function getFiles()
	{
		return $this->_files;
	}
	
	/**
	 * Renvoi l'objet représentant le fichier demandé
	 * @param string $name
	 * @return \He\Dir\File 
	 */
	public function getFolder($name)
	{
		$path = $this->_nameToArray($name);
		
		/* Si on est en fin de chemin */
		if(count($path) == 1)
		{
			if(!empty($this->_folders[$name]))
			{
				return $this->_folders[$name];
			}
			else
			{
				\He\Trace::addTrace('Le sous-dossier "'.$name
						.'" n\'existe pas dans le dossier "'.$this->_name.'"', 
						get_called_class(), -1);
			}
		}
		else
		{
			$intermediaire = array_shift($path);
			if(!empty($this->_folders[$intermediaire]))
			{
				return $this->_folders[$intermediaire]->getFolder($this->_arrayToName($path));
			}
			else
			{
				\He\Trace::addTrace('ERREUR : Le sous dossier demandé "'
						.$intermediaire.'" pour récupérer le dossier "'
						.$name.'" n\'existe pas !', get_called_class());
				throw new \He\Exception('ERREUR : Le sous dossier demandé "'
						.$intermediaire.'" pour récupérer le dossier "'
						.$name.'" n\'existe pas !');
				return false;
			}
		}
	}
	
	/**
	 * Retourne le tableau d'objets représentant les sous dossiers de dossier
	 * @return array
	 */
	public function getFolders()
	{
		return $this->_folders;
	}
	
	/**
	 * Retourne le chemin absolue de ce dossier
	 * @return string 
	 */
	public function getPath()
	{
		return $this->_path;
	}
	
	/**
	 * Retourne le dossier parent de celui-ci, ou renvoi faux si c'est la racine
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
	 * Test si le dossier comporte ou non des fichier ou des sous-dossiers
	 * @return bool
	 */
	public function isEmpty()
	{
		if(count($this->_folders) + count($this->_files) == 0)
			return true;
		else
			return false;
	}
	
	public function haveFolders()
	{
		if(count($this->_folders) > 0)
			return true;
		else
			return false;
	}
	
	public function haveFiles()
	{
		if(count($this->_files) > 0)
			return true;
		else
			return false;
	}
	
	/**
	 * Recherche tout les fichiers et sous-dossiers de ce dossier.
	 * @return $this
	 */
	public function load($recursive = false)
	{
		$oDir = opendir($this->_path);
		while($file = readdir($oDir))
		{
			if($file != '..' && $file != '.')
			{
				/* Si c'est un dossier, récursion */
				if(is_dir($this->_path.'/'.$file))
				{
					$this->addFolder($file, $this);
					
					/* Déclenche la récursion aux sous dossiers */
					if($recursive)
					{
						$this->getFolder($file)->load($recursive);
					}
				}
				else
				{
					$this->addFile($file, $this);
				}
			}
		}
		closedir($oDir);
		
		return $this;
	}
}