<?php
/* Connection à la bdd */
define('HE_BASE_NAME', 'helium');
define('HE_BASE_HOST', 'localhost');
define('HE_BASE_USER', 'root');
define('HE_BASE_PWD', '');

define('DEBUG', true); // Active le mode debug
define('TRACE', true); // Définie si on affiche ou non les traces de log
define('MERGE_TEMPLATE', true); // Crée des ficheirs de cache pour les template, à désactiver en dev
define('LOCALISE', false); // Autorise la création de fichiers de localisation