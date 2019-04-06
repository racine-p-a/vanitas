<?php
/**
 * PROJET SITE PERSO
 * @author  Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @copyright Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @license http://www.cecill.info/licences/Licence_CeCILL-C_V1-fr.html LICENCE DE LOGICIEL LIBRE CeCILL-C
 * @date 02/12/18 18:22
 *
 * Contexte : TODO
 *
 * @link https://github.com/racine-p-a/p-a-racine
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


class Countries
{
    private $currentCountry;

    private $corrections=array(
        'United States'=>'United States of America',
        'Russia'=>'Russian Federation',
        'Republic of Korea'=>'Korea (Republic of)'
    );

    // TODO https://github.com/lukes/ISO-3166-Countries-with-Regional-Codes
    // TODO CASSER TABLEAUX POUR METTRE EN MODE OBJET


    /**
     * Countries constructor.
     */
    public function __construct($countryName='')
    {
        if($countryName!='') {
            $countriesString = file_get_contents(__DIR__ . '/data/countries.json');

            $countries = json_decode($countriesString, TRUE);

            foreach ($countries as $key => $val)
            {
                $nameToSeekFor = $countryName;
                if( array_key_exists($countryName, $this->corrections) )
                {
                    $nameToSeekFor = $this->corrections[$countryName];
                }
                if($val['name']==$nameToSeekFor)
                {
                    $this->currentCountry = $val;
                    break;
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getCurrentCountry()
    {
        return $this->currentCountry;
    }
}