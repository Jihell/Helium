<?php
/**
 * Manipule les dossiers et les fichiers qui le compose
 *
 * @author Joseph Lemoine - lemoine.joseph@gmail.com
 * @version 2
 */
namespace He;

class Dir
{
	/**
	 * vide le dossier spécifié par le chemin $dir
	 * @param string $dir chemin vers le fichiers à effacer
	 * @param bool $resursive Effacement de tout les fichiers dans les sous dossiers
	 * @param bool $destroyStructure Effacement de tout les sous dossiers
	 * @return bool
	 */
	public static function clear($dir, $resursive = false, $destroyStructure = false)
	{
		static::set777($dir);
		\He\Trace::addTrace('Ouverture du dossier "'.$dir.'" à effacer', get_called_class());
		
		$oDir = opendir($dir);
		while($file = readdir($oDir))
		{
			if($file != '..' && $file != '.')
			{
				if(!is_dir($dir.'/'.$file))
				{
					if(!unlink($dir.'/'.$file))
						return false;
				}
				elseif($resursive)
				{
					static::clear($dir.'/'.$file, $resursive, $destroyStructure);
				}
			}
		}
		closedir($oDir);
		
		if($destroyStructure)
		{
			if(!rmdir($dir))
				\He\Trace::addTrace('Impossible de supprimer le dossier !', get_called_class(), -1);
		}
		else
			static::set755($dir);
		
		\He\Trace::addTrace('Dossier "'.$dir.'" effacé avec succès !', get_called_class());
		
		return true;
	}
	
	/**
	 * Modifie les droits d'accès au fichier en 777
	 * @param string $dir chemin vers le dossier à modifier
	 */
	public static function set777($dir)
	{
		chmod($dir, '0777');
	}
	
	/**
	 * Modifie les droits d'accès au fichier en 755
	 * @param string $dir chemin vers le dossier à modifier
	 */
	public static function set755($dir)
	{
		chmod($dir, '0755');
	}
	
	/**
	 * Modifie les droits d'accès au fichier en 750
	 * @param string $dir chemin vers le dossier à modifier
	 */
	public static function set750($dir)
	{
		chmod($dir, '0770');
	}
	
	/**
	 * Liste les fichiers d'un répertoire et retourne un tableau contenant les
	 * noms des fichiers.
	 * @param string $dir
	 * @return array / false
	 */
	public static function listFile($dir, $bindName = false)
	{
		if(is_dir($dir))
		{
			$send = array();
			$oDir = opendir($dir);
			while($file = readdir($oDir))
			{
				if($file != '..' && $file != '.' && !is_dir($dir.'/'.$file))
				{
					if(!$bindName)
						$send[] = $file;
					else
						$send[][$bindName] = $file;
				}
			}
			closedir($oDir);
			
			return $send;
		}
		else
		{
			\He\Trace::addTrace('Ce n\'est pas un répertoire : '.$dir, get_called_class(), -1);
			return false;
		}
	}
	
	/**
	 * Liste les dossiers d'un répertoire et retourne un tableau contenant les
	 * noms.
	 * @param string $dir
	 * @return array / false
	 */
	public static function listDir($dir, $bindName = false)
	{
		if(is_dir($dir))
		{
			$send = array();
			$oDir = opendir($dir);
			while($file = readdir($oDir))
			{
				if($file != '..' && $file != '.' && is_dir($dir.'/'.$file))
					if(!$bindName)
						$send[] = $file;
					else
						$send[][$bindName] = $file;
			}
			closedir($oDir);
			
			return $send;
		}
		else
		{
			\He\Trace::addTrace('Ce n\'est pas un répertoire : '.$dir, get_called_class(), -1);
			return false;
		}
	}
	
	/**
	 * Liste les fichiers d'un répertoire et retourne un tableau contenant les
	 * noms des fichiers.
	 * @param string $dir
	 * @return array / false
	 */
	public static function listAll($dir, $bindName = false)
	{
		if(is_dir($dir))
		{
			$send = array();
			$oDir = opendir($dir);
			while($file = readdir($oDir))
			{
				if($file != '..' && $file != '.')
					if(!$bindName)
						$send[] = $file;
					else
						$send[][$bindName] = $file;
			}
			closedir($oDir);
			
			return $send;
		}
		else
		{
			\He\Trace::addTrace('Ce n\'est pas un répertoire : '.$dir, get_called_class(), -1);
			return false;
		}
	}
	
	/**
	 * Créer le dossier spécifier si il n'existe pas, en lui donnant le maximum 
	 * de droits. On renvoi le chemin spécifié pour facilier les inclusions.
	 * @param string $path chemin vers le dossier
	 * @return string
	 */
	public static function makePath($path)
	{
		if(!file_exists($path))
		{
			\He\Trace::addTrace('Création du répertoire '.$path, get_called_class());
			mkdir($path, '0777', true);
		}
		return $path;
	}
	
	public static function getTree($dir,\He\Dir\Folder $root = null)
	{
		if(is_dir($dir))
		{
			$root = new \He\Dir\Folder($dir);
			return $root->load(true);
		}
		else
		{
			\He\Trace::addTrace('Ce n\'est pas un répertoire : '.$dir, get_called_class(), -1);
			return false;
		}
	}
}