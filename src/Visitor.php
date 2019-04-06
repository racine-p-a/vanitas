<?php
/**
 * PROJET SITE PERSO
 * @author  Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @copyright Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @license http://www.cecill.info/licences/Licence_CeCILL-C_V1-fr.html LICENCE DE LOGICIEL LIBRE CeCILL-C
 * @date 05/11/18 18:02
 *
 * Contexte : TODO
 *
 * @link https://github.com/racine-p-a/p-a-racine
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


class Visitor
{
    /**
     * @var string Current visitor IP.
     */
    private $_ipVisitor = '';

    /**
     * @var bool Is the IP in mode IPV4 ?
     */
    private $_isIPV4 = false;

    /**
     * @var bool Is the IP in mode IPV6 ?
     */
    private $_isIPV6 = false;

    /**
     * @var string Date of the request (YYYY-MM-DD)
     */
    private $_date = '';

    /**
     * @var string Hour of the request (HH-MM-SS)
     */
    private $_hour = '';

    /**
     * @var string Url of the current requested page.
     */
    private $_requestedPage = '';

    /**
     * @var string From which url come the visitor.
     */
    private $_comingFromUrl = '';

    /**
     * @var array Array containing informations about current visitor's country.
     */
    private $_countryVisitor = array(
        'countryName'=>'',
        'countryTag2'=>'',
        'countryTag3'=>'',
    );

/*


    private $villeVisiteur = '';

    private $codeContinent = '';

    private $latitudeVisiteur = '';

    private $longitudeVisiteur = '';

    private $organisationVisiteur = '';

    private $moteurDeRenduVisiteur = '';

    private $agentType = '';

    private $agentName = '';

    private $agentVersion = '';

    private $OSName = '';

    private $OSVersionNumber = '';

    private $OSplateForme = '';

    private $nomMachine = '';

    private $marque = '';

    private $modele = '';
    */

    /**
     * Visitor constructor.
     * Grab all current visitor's informations using following methods.
     * @throws Exception
     */
    public function __construct()
    {
        /*
         * What are our current raw data ?
         * - $_SERVER['HTTP_REFERER']                           --> coming from url
         * - "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"  --> current page
         * - date('Y-m-d')
         * - date('H:i:s')
         * - IP
         * - $_SERVER['HTTP_USER_AGENT']
         */
        $this->_ipVisitor = $this->getUserIP();
        $this->_date = date('Y-m-d');
        $this->_hour = date('H:i:s');
        $this->_requestedPage = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        if( isset($_SERVER['HTTP_REFERER']) )
        {
            $this->_comingFromUrl = $_SERVER['HTTP_REFERER'];
        }

        // What is current IP mode ?
        if(filter_var($this->_ipVisitor, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->_isIPV4 = true;
            $this->_isIPV6 = false;
        } elseif (filter_var($this->_ipVisitor, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->_isIPV4 = false;
            $this->_isIPV6 = true;
        }
        /*
         * Easy stuff is done. Now let's make the difficult ones :
         * - extract informations from the IP.
         * - extract informations from the HTTP_USER_AGENT.
         */

        // --> extract informations from IP
        require_once 'IPData.php';
        try {
            new IPData($this);
        } catch (Exception $e) {
            echo '<p>Error : ',  $e->getMessage(), '</p>';
        }
        var_dump($this);
        /*

        $this->getIPAPIData();




        //$this->moteurDeRenduVisiteur = $this->recupererMoteurDeRendu(); // Plus nécesaire avec bibliothèque useragent
        $this->getUserAgentData();

        $this->addEntryToTsv();
*/
    }

    public function getIpVisitor()
    {
        return $this->_ipVisitor;
    }

    public function isIPV4()
    {
        return $this->_isIPV4;
    }
    public function isIPV6()
    {
        return $this->_isIPV6;
    }






























    /**
     * Add a line of information about the current visitor to the csv file.
     */
    private function addEntryToTsv()
    {
        try
        {
            $ligneAInserer =
                $this->ipVisiteur . "\t" .
                $this->paysVisiteur . "\t" .
                $this->codePaysVisiteur . "\t" .
                $this->villeVisiteur . "\t" .
                $this->codeContinent . "\t" .
                $this->latitudeVisiteur . "\t" .
                $this->longitudeVisiteur . "\t" .
                $this->organisationVisiteur . "\t" .
                $this->pageVue . "\t" .
                $this->origineVisite . "\t" .
                $this->dateVisite . "\t" .
                $this->heureVisite . "\t" .
                $this->moteurDeRenduVisiteur . "\t" .
                $this->agentType . "\t" .
                $this->agentName . "\t" .
                $this->agentVersion . "\t" .
                $this->OSName . "\t" .
                $this->OSVersionNumber . "\t" .
                $this->OSplateForme . "\t" .
                $this->nomMachine . "\t" .
                $this->marque . "\t" .
                $this->modele . "\n"
            ;

            file_put_contents(__DIR__ . '/data/VanitasVisitors.csv', $ligneAInserer, FILE_APPEND | LOCK_EX);
        }
        catch (Exception $e)
        {
            require_once __DIR__ . '/errors.php';
            new Errors(
                'Erreur ! Impossible de lire le fichier /data/VanitasVisitors.csv.',
                'Error : Unable to open the file « /data/VanitasVisitors.csv »');
        }
    }


    /**
     * Makes a curl request to IP API in order to get some informations about current visitor.
     */
    private function getIPAPIData()
    {
        $curl = curl_init('https://ipapi.co/' . $this->ipVisiteur . '/json/');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 1000);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 2000);
        $resultat = curl_exec($curl);
        if( $resultat=== false)
        {
            new Errors(
                'La requête à IP API a échoué : ' . $this->ipVisiteur,
                'IP API request failed : ' . $this->ipVisiteur);
        }
        else
        {
            $resultatCurl = json_decode($resultat);
            foreach ($resultatCurl as $clef=>$valeur)
            {
                switch ($clef)
                {
                    case 'city':
                        $this->villeVisiteur = $valeur;
                        break;
                    case 'country_name':
                        $this->paysVisiteur = $valeur;
                        break;
                    case 'country':
                        $this->codePaysVisiteur = $valeur;
                        break;
                    case 'continent_code':
                        $this->codeContinent = $valeur;
                        break;
                    case 'latitude':
                        $this->latitudeVisiteur = $valeur;
                        break;
                    case 'longitude':
                        $this->longitudeVisiteur = $valeur;
                        break;
                    case 'org':
                        $this->organisationVisiteur = $valeur;
                        break;

                }
            }
        }
        curl_close($curl);
    }

    /**
     * Makes a curl request to user agent data in order to get some informations about visitor's browser.
     * @see https://github.com/matomo-org/device-detector
     */
    private function getUserAgentData()
    {
        DeviceParserAbstract::setVersionTruncation(DeviceParserAbstract::VERSION_TRUNCATION_NONE);
        $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
        $dd = new DeviceDetector($userAgent);
        $dd->parse();

        if ($dd->isBot())
        {
            // handle bots,spiders,crawlers,...
            $botInfo = $dd->getBot();
            new Error($botInfo);
        }
        else
        {
            $clientInfo = $dd->getClient(); // holds information about browser, feed reader, media player, ...
            $this->agentType = $clientInfo['type'];
            $this->agentName = $clientInfo['name'];
            $this->agentVersion = $clientInfo['version'];
            $this->moteurDeRenduVisiteur = $clientInfo['engine'];

            $osInfo = $dd->getOs();
            $this->OSName = $osInfo['name'];
            $this->OSVersionNumber = $osInfo['version'];
            $this->OSplateForme = $osInfo['platform'];

            $this->nomMachine = $dd->getDeviceName();

            $this->marque = $dd->getBrandName();
            $this->modele = $dd->getModel();
        }

        /*
        $curl = curl_init('http://www.useragentstring.com/?uas=' . urlencode($_SERVER['HTTP_USER_AGENT']) . '&getJSON=all');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 2);
        $resultat = curl_exec($curl);
        if( $resultat=== false)
        {
            new Errors(
                'La requête à user agent data a échoué : ' . $this->ipVisiteur . ' et user agent : ' . $_SERVER['HTTP_USER_AGENT'],
                'user agent data request failed : ' . $this->ipVisiteur . ' and user agent : ' . $_SERVER['HTTP_USER_AGENT']
            );
        }
        else
        {
            $resultatCurlMatos = json_decode(curl_exec($curl));
            if($resultatCurlMatos!=null)
            {
                foreach ($resultatCurlMatos as $clef=>$valeur)
                {
                    switch ($clef)
                    {
                        case 'agent_type':
                            $this->agentType = $valeur;
                            break;
                        case 'agent_name':
                            $this->agentName = $valeur;
                            break;
                        case 'agent_version':
                            $this->agentVersion = $valeur;
                            break;
                        case 'os_type':
                            $this->OSType = $valeur;
                            break;
                        case 'os_name':
                            $this->OSName = $valeur;
                            break;
                        case 'os_versionName':
                            $this->OSVersionName = $valeur;
                            break;
                        case 'os_versionNumber':
                            $this->OSVersionNumber = $valeur;
                            break;
                        case 'os_producer':
                            $this->OSProducer = $valeur;
                            break;
                        case 'os_producerURL':
                            $this->OSProducerUrl = $valeur;
                            break;
                        case 'linux_distibution':
                            $this->linuxDistribution = $valeur;
                            break;
                        case 'agent_language':
                            $this->agentLanguage = $valeur;
                            break;
                        case 'agent_languageTag':
                            $this->agentLanguageTag = $valeur;
                            break;
                    }
                }
            }
        }
        curl_close($curl);
        */
    }


    /**
     * Get browser engine which is written in the $_SERVER['HTTP_USER_AGENT']
     * @see https://developer.mozilla.org/fr/docs/Web/HTTP/Detection_du_navigateur_en_utilisant_le_user_agent
     * @return string
     */
    private function recupererMoteurDeRendu()
    {
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') !== false)
            return 'Gecko';
        elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'AppleWebKit') !== false)
            return 'AppleWebKit';
        elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false)
            return 'Blink';
        elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false)
            return 'Trident';
        elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false)
            return 'Blink';
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * Get the true ip adress of the visitor even beyond a proxy.
     * @see https://stackoverflow.com/questions/13646690/how-to-get-real-ip-from-visitor
     * @return string
     */
    function getUserIP()
    {
        $ip = '';
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]))
        {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }
        return $ip;
    }

    public function setCountry($countryName='')
    {
        $this->_countryVisitor['countryName'] = $countryName;
    }
    public function setCountryTag2($countryTag2='')
    {
        $this->_countryVisitor['countryTag2'] = $countryTag2;
    }
    public function setCountryTag3($countryTag3='')
    {
        $this->_countryVisitor['countryTag3'] = $countryTag3;
    }
}