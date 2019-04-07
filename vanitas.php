<?php
/**
 * PROJET VANITAS.
 * @author  Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @copyright Copyright (c) 2019, Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @license http://www.gnu.org/licenses/lgpl.html
 * @date 04/11/18 20:38
 *
 * This the file to call if you want to gather informations about your current visitor. Data will be
 * recorded in a .csv file.
 *
 * @link https://github.com/racine-p-a/vanitas
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/Visitor.php';
new Visitor();