<?php


class CountryCodes
{
    /**
     * @param $tag2
     * @return array
     * @throws Exception
     */
    public function getCountryByTag2($tag2)
    {
        if(!file_exists(__DIR__ . '/data/geo-ip/country-codes.txt')) {
            throw new Exception('Can not find file country-codes.txt .');
        }
        if(!is_readable(__DIR__ . '/data/geo-ip/country-codes.txt')) {
            throw new Exception('File country-codes.txt exists but is unreadable.');
        }

        require_once __DIR__ . '/BigFileIterator.php';
        $largefile = new BigFileIterator(__DIR__ . '/data/geo-ip/country-codes.txt');
        $iterator = $largefile->iterate("Text");

        foreach ($iterator as $line) {
            $informations = str_getcsv($line, ',', '');
            if( $informations[1]==$tag2 ) {
                return array(
                    'countryName'=>$informations[0],
                    'countryTag2'=>$informations[1],
                    'countryTag3'=>$informations[2],
                );
            }
        }
        return array();
    }
}