<?php
/**
 * PROJET VANITAS.
 * @author  Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @copyright Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @license http://www.cecill.info/licences/Licence_CeCILL-C_V1-fr.html LICENCE DE LOGICIEL LIBRE CeCILL-C
 * @date 04/11/18 20:38
 *
 * Contexte : Ce fichier est celui à appeler pour utiliser l'application Vanitas. Ce script doit récupérer les
 * informations du vsiteur courant et les stocker dans un fichier .tsv.
 *
 * @link https://github.com/racine-p-a/p-a-racine
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/Visitor.php';
new Visitor();