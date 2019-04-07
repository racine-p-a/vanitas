<?php
/**
 * PROJET VANITAS.
 * @author  Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @copyright Copyright (c) 2019, Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @license http://www.gnu.org/licenses/lgpl.html
 * @date 04/11/18 20:38
 *
 * Class IPData
 * The class gets all informations about a certain IP adress.
 *
 * @link https://github.com/racine-p-a/vanitas
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Visitor.php';

class IPData
{
    private $_currentVisitor;
    /**
     * IPData constructor.
     * @param Visitor $visitor
     * @throws Exception
     */
    public function __construct(Visitor &$visitor)
    {
        $this->_currentVisitor = $visitor;
        $this->extractData();
    }


    /**
     * @throws Exception
     */
    private function extractData()
    {
        if($this->_currentVisitor->isIPV4()) {
            // IPV4 file.
            $convertedIP = $this->convertIpToInt($this->_currentVisitor->getIpVisitor());

            if(!file_exists(__DIR__ . '/data/geo-ip/IpToCountry.csv')) {
                throw new Exception('Can not find file IpToCountry.csv .');
            }
            if(!is_readable(__DIR__ . '/data/geo-ip/IpToCountry.csv')) {
                throw new Exception('File IpToCountry.csv exists but is unreadable.');
            }

            require_once __DIR__ . '/BigFileIterator.php';
            $largefile = new BigFileIterator(__DIR__ . '/data/geo-ip/IpToCountry.csv');
            $iterator = $largefile->iterate("Text");

            $count = 0;
            foreach ($iterator as $line) {
                if($count>340) {
                    $informations = str_getcsv($line, ',', '"');
                    if( intval($informations[0]) < $convertedIP && $convertedIP < intval($informations[1])) {
                        $this->_currentVisitor->setCountry($informations[6]);
                        $this->_currentVisitor->setCountryTag2($informations[4]);
                        $this->_currentVisitor->setCountryTag3($informations[5]);
                        break;
                    }
                }
            $count+=1;
            }
        } elseif ($this->_currentVisitor->isIPV6()) {
            // IPV6 file.
            if(!file_exists(__DIR__ . '/data/geo-ip/IpToCountry.6R.csv')) {
                throw new Exception('Can not find file IpToCountry.6R.csv .');
            }
            if(!is_readable(__DIR__ . '/data/geo-ip/IpToCountry.6R.csv')) {
                throw new Exception('File IpToCountry.6R.csv exists but is unreadable.');
            }
            require_once __DIR__ . '/BigFileIterator.php';
            $largefile = new BigFileIterator(__DIR__ . '/data/geo-ip/IpToCountry.6R.csv');
            $iterator = $largefile->iterate("Text");

            $count = 0;
            foreach ($iterator as $line) {
                if($count>327) {
                    $informations = str_getcsv($line, ',', '"');
                    $ips = explode('-', $informations[0]);
                    $startingIP = inet_pton($ips[0]);
                    $endingIp = inet_pton($ips[1]);
                    $currentIP = inet_pton($this->_currentVisitor->getIpVisitor());
                    if ( (strlen($currentIP) == strlen($startingIP)) &&  ($currentIP >= $startingIP && $currentIP <= $endingIp)) {
                        require_once __DIR__ . '/CountryCodes.php';
                        $country = new CountryCodes();
                        $country = $country->getCountryByTag2($informations[1]);
                        $this->_currentVisitor->setCountry($country['countryName']);
                        $this->_currentVisitor->setCountryTag2($country['countryTag2']);
                        $this->_currentVisitor->setCountryTag3($country['countryTag3']);
                        break;
                    }
                }
                $count+=1;
            }
        }

    }


    /**
     * Give back numerical representation of IP address.
     * Example: (from Right to Left)
     * 1.2.3.4 = 4 + (3 * 256) + (2 * 256 * 256) + (1 * 256 * 256 * 256)
     * is 4 + 768 + 13,1072 + 16,777,216 = 16,909,060
     * @param $ip string The IP to convert in integer
     * @return int The integer equivalent of the IP given.
     * @throws Exception
     */
    private function convertIpToInt($ip)
    {
        if($this->_currentVisitor->isIPV4()) {
            $explodedIP = explode('.', $ip);
            if(count($explodedIP)==4) {
                return $explodedIP[0]*256*256*256 + $explodedIP[1]*256*256 + $explodedIP[2]*256 + $explodedIP[3];
            }
            throw new Exception('Incoherent IPv4 received : ' . $ip);
        }

        return 0;
    }
}



