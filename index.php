<?php
require("./config/init.php");
He\Dispatch::run();

/* TODO
 * Ajouter un interface d'admin au comportement suivant
 * - Un fois loggé en tant que SU, afficher un bouton en haut à gauche
 * regroupant les outils d'administration :
 * => Mini php my admin (browse / del / Insert / exe SQL)
 * => Gestion des users
 * 
 * Ajouter une gestion des users
 * Ajouter une gestion des roles
 * Ajouter la fonction pour se logger en tant que ...
 * 
 * Créer un intalleur pour générer les tables de base
 */