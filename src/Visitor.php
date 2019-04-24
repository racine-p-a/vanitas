<?php
/**
 * PROJET VANITAS.
 * @author  Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @copyright Copyright (c) 2019, Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @license http://www.gnu.org/licenses/lgpl.html
 * @date 04/11/18 20:38
 *
 * Class Visitor
 * Gathers all data from the current visitor then stores them in a .csv file.
 *
 * @link https://github.com/racine-p-a/vanitas
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

    /**
     * @var string The raw user agent sent by the visitor.
     */
    private $_userAgent = '';

    /**
     * @var bool Is the visitor a bot ?
     */
    private $_isBot = false;


    /*
     *              BROWSER
     */
    /**
     * @var string Visitor's browser name.
     */
    private $_browserName = '';

    /**
     * @var string Visitor's browser version.
     */
    private $_browserVersion = '';

    /**
     * @var string Visitor's browser engine.
     */
    private $_browserEngine = '';

    /*
     *              HARDWARE
     */
    /**
     * @var bool Is the processor design in 64 bits ?
     */
    private $_processor64 = false;

    /**
     * @var bool Is the visitor using a mobile device ?
     */
    private $_isMobileDevice = false;

    /*
     *              OS
     */
    /**
     * @var string Visitor's operanding system family.
     */
    private $_osFamily = '';

    /**
     * @var string Visitor's operanding system version.
     */
    private $_osVersion = '';

    /**
     * @var string Visitor's operanding system name.
     */
    private $_osName = '';


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
        $this->_userAgent = $_SERVER['HTTP_USER_AGENT'];
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

        // The browser engine ?
        $this->_browserEngine = $this->getBrowserEngine();

        /*
         * Easy stuff is done. Now let's make the difficult ones :
         * - extract informations from the IP.
         * - extract informations from the HTTP_USER_AGENT.
         */

        // --> extract informations from IP
        require_once __DIR__ . '/IPData.php';
        try {
            new IPData($this);
        } catch (Exception $e) {
            echo '<p>Error : ',  $e->getMessage(), '</p>';
        }

        // -> extract informations from user agent
        require_once __DIR__ . '/UserAgentData.php';
        new UserAgentData($this);


        // It's done, let's record it.
        $this->addEntry();
    }

    /**
     * Add an entry in the csv containing all visitors. Data come from current object Visitor.
     * @throws Exception
     */
    private function addEntry()
    {
        if(!file_exists(__DIR__ . '/data/VanitasVisitors.csv')) {
            throw new Exception('Can not find file VanitasVisitors.csv .');
        }
        if(!is_readable(__DIR__ . '/data/VanitasVisitors.csv')) {
            throw new Exception('File VanitasVisitors.csv exists but is unreadable.');
        }
        $fp = fopen(__DIR__ . '/data/VanitasVisitors.csv', 'a');
        $dataTable = array(
            $this->_date,
            $this->_hour,
            $this->_ipVisitor,
            intval($this->isIPV4()),
            intval($this->isIPV6()),
            trim($this->_requestedPage),
            trim($this->_comingFromUrl),
            $this->_countryVisitor['countryName'],
            $this->_countryVisitor['countryTag2'],
            $this->_countryVisitor['countryTag3'],
            $this->_userAgent,
            intval($this->_isBot),
            $this->_browserName,
            $this->_browserVersion,
            $this->_browserEngine,
            intval($this->_processor64),
            intval($this->_isMobileDevice),
            $this->_osFamily,
            $this->_osVersion,
            $this->_osName,
        );
        fputcsv($fp, $dataTable, ',', '"');
        fclose($fp);
    }

    /**
     * Get browser engine which is written in the $_SERVER['HTTP_USER_AGENT']
     * @see https://developer.mozilla.org/fr/docs/Web/HTTP/Detection_du_navigateur_en_utilisant_le_user_agent
     * @return string
     */
    private function getBrowserEngine()
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
        return 'Other';
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




    /*
     *          SETTERS
     */
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
    public function setBrowserName($browserName='')
    {
        $this->_browserName = $browserName;
    }
    public function setBrowserVersion($browserVersion='')
    {
        $this->_browserVersion = $browserVersion;
    }
    public function setProcessorDesign($processorDesign=false)
    {
        $this->_processor64 = $processorDesign;
    }
    public function setMobileDevice($mobileDevice=false)
    {
        $this->_isMobileDevice = $mobileDevice;
    }
    public function setRobot($robot=false)
    {
        $this->_isBot = $robot;
    }
    public function setOS($osFamily='')
    {
        $this->_osFamily = $osFamily;
    }
    public function setOSVersion($osVersion='')
    {
        $this->_osVersion = $osVersion;
    }
    public function setOSName($osName='')
    {
        $this->_osName = $osName;
    }

    /*
     *          GETTERS
     */
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
}