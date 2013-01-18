<?php
/**
 * Class étendu de \He\DB\Row
 * Vous pouvez ajouter ici les méthodes personnalisés
 * Représentation objet d'une ligne de la table HE/test (test) 
 * 
 * @author: He@He\DB\DAO
 * @version: 1
 */
namespace He\DB\Row;

final class Test extends \He\DB\Row\Ref\Test
{
	/**
	 * Ajoutez vos getters personnalisés ici.
	 * Liste des variables : 
	 * @var id
	 * @var label
	 * @var instant
	 * @var id_cat
	 * 
	 * Liste des jointures : 
	 * @join chat => id_cat@cat
	 */
}