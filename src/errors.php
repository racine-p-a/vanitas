<?php
/**
 * PROJET SITE PERSO
 * @author  Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @copyright Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @license http://www.cecill.info/licences/Licence_CeCILL-C_V1-fr.html LICENCE DE LOGICIEL LIBRE CeCILL-C
 * @date 05/11/18 21:35
 *
 * Contexte : TODO
 *
 * @link https://github.com/racine-p-a/p-a-racine
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


class errors
{

    /**
     * errors constructor.
     */
    public function __construct($messageFR='', $messageEN='')
    {
        /*
         * On insérera dans cet ordre : jour, heure, messageFR, messageEN
         */
        $ligneAInserer = '"' . date('Y-m-d') . '";"' . date('H:i:s') . '";"' . $messageFR . '";"' . $messageEN . '";' . "\n";
        try
        {
            file_put_contents(__DIR__ . '/data/errors.csv', $ligneAInserer, FILE_APPEND | LOCK_EX);
        }
        catch (Exception $e)
        {
            echo '<p>' .  $e . '</p>';
        }
    }
}