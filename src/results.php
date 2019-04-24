<?php
/**
 * PROJET VANITAS.
 * @author  Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @copyright Copyright (c) 2019, Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @license http://www.gnu.org/licenses/lgpl.html
 * @date 04/11/18 20:38
 *
 * Class Results
 * Get the data recorded and prepare them for a fancy display.
 *
 * @link https://github.com/racine-p-a/vanitas
 */


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


class Results
{
    /**
     * @var array The date read in the .csv file
     */
    private $_data = array();


    private $_authorizedSetsOfData = array(
        'bot',
        'browser',
        'country',
        'hour',
        'mobile',
        'navigation',
        'system',
    );


    private $_currentChartOptions=array();

    /**
     * Results constructor.
     * @param array $dataNames
     * @throws Exception
     */
    public function __construct(array $dataNames=array())
    {
        foreach ($dataNames as $dataName) {
            if(!in_array($dataName, $this->_authorizedSetsOfData)) {
                $errorMessage = 'Unknown type of data asked : ' . $dataName . ' . Please choose one of them : ';
                $count=0;
                while ($count<count($this->_authorizedSetsOfData)) {
                    $errorMessage .= $this->_authorizedSetsOfData[$count];
                    if ($count< count($this->_authorizedSetsOfData)-1) {
                        $errorMessage .= ',';
                    }
                    $count+=1;
                }
                $errorMessage .= '.';
                throw new Exception($errorMessage);
            }
            $this->_data[$dataName] = array();
        }

        // Known type of data asked. Let's fetch it.
        require_once __DIR__ . '/BigFileIterator.php';
        $largefile = new BigFileIterator(
            __DIR__ . '/data/VanitasVisitors.csv'
        );
        $iterator = $largefile->iterate("Text");
        foreach ($iterator as $line) {
            if(!trim($line)=='') {

                $csvLine = str_getcsv($line);

                foreach ($this->_data as $idArray=>$valueArray) {
                    switch ($idArray) {
                        case 'bot':
                            array_push($this->_data['bot'], $csvLine[11]);
                            break;
                        case 'browser':
                            array_push($this->_data['browser'], $csvLine[12]);
                            break;
                        case 'country':
                            array_push($this->_data['country'], $csvLine[7]);
                            break;
                        case 'hour':
                            array_push($this->_data['hour'], $csvLine[1]);
                            break;
                        case 'mobile':
                            array_push($this->_data['mobile'], $csvLine[16]);
                            break;
                        case 'navigation':
                            $prefixes = array('http://', 'https://', 'http://www.', 'https://www.', 'www.');
                            $comingFrom = str_replace($prefixes, '', trim($csvLine[6]));
                            while(mb_substr($comingFrom, -1)=='/') {
                                $comingFrom = rtrim($comingFrom, '/');
                            }
                            $currentURL = str_replace($prefixes, '', trim($csvLine[5]));
                            while(mb_substr($currentURL, -1)=='/') {
                                $currentURL = rtrim($currentURL, '/');
                            }
                            array_push($this->_data['navigation'], array($comingFrom, $currentURL));
                            break;
                        case 'system':
                            array_push($this->_data['system'], $csvLine[17]);
                            break;
                        default:
                            break;
                    }
                }
            }
        }
    }

    public function getChart($typeChart= '', $dataName = '', $data=array(), $options=array())
    {
        if (!in_array($dataName, $this->_authorizedSetsOfData)) {
            throw new Exception('Unknown type of data asked : ' . $dataName . ' .');
        }

        // On vérifie précieusement et stocke les options.
        $this->manageOptions($options);


        $codeHTML = '<div id="' . $this->_currentChartOptions['divContainingCanvasId'] . '" class="' . $this->_currentChartOptions['divContainingCanvasClass'] . '" style="width:' . $this->_currentChartOptions['width'] . '; height:' . $this->_currentChartOptions['height'] . ';">
        <canvas id="' . $this->_currentChartOptions['canvasId'] . '" style="position: relative;"></canvas>
    </div>';
        switch ($dataName) {
            case 'bot':
                return $codeHTML . $this->manageBots($typeChart, $data);
                break;
            case 'browser':
                return $codeHTML . $this->manageBrowsers($typeChart, $data);
                break;
            case 'country':
                return $codeHTML . $this->manageCountries($typeChart, $data);
                break;
            case 'hour':
                return $codeHTML . $this->manageHours($typeChart, $data);
                break;
            case 'mobile':
                return $codeHTML . $this->manageMobiles($typeChart, $data);
                break;
            case 'navigation':
                return $codeHTML . $this->manageNavigation($data);
                break;
            case 'system':
                return $codeHTML . $this->manageSystems($typeChart, $data);
                break;
            default:
                throw new Exception('Not yet implemented.');
        }



    }

    /*******************************************************************************************************************
     *                                              GEOGRAPHY - COUNTRIES
     ******************************************************************************************************************/


    private function manageCountries($typeChart='', &$data=array())
    {
        if($typeChart=='piechart') {
            return $this->getCountryPieChart($data, 'pie');
        } else {
            return $this->getCountryPieChart($data, $typeChart);
        }
    }


    private function getCountryPieChart(&$data=array(), $typeChart='pie')
    {
        $countries = $this->getCountryRepartition($data);
        $codeHTML = '<script>
        new Chart(document.getElementById(\'' . $this->_currentChartOptions['canvasId'] . '\').getContext(\'2d\'), {
            type: "' . $typeChart . '",
            data: {
                labels: [';
        foreach ($countries as $country=>$quantity) {
            $codeHTML .= '"' . $country . '", ';
        }
        $codeHTML .='],
                datasets: [{
                    data: [';

        foreach ($countries as $country=>$quantity)
        {
            $codeHTML .= $quantity . ',';
        }

        $codeHTML .= '],
                    label: "' . $this->_currentChartOptions['label'] . '",
                    backgroundColor: [';

        if( count($countries) > count($this->_currentChartOptions['colors']) ) {
            $this->_currentChartOptions['colors'] = array_merge($this->_currentChartOptions['colors'], $this->pickRandomColors(count($countries) - count($this->_currentChartOptions['colors'])));
        }

         $codeHTML .= $this->arrayColorToString($this->_currentChartOptions['colors']) . ', ';

        $codeHTML .= ']
                    },
    ]
  },
  options: {
    title: {
      display: true,
      text: "' . $this->_currentChartOptions['title'] . '"
    }
  }
});
    </script>';

        return $codeHTML;
    }




    /*******************************************************************************************************************
     *                                          TEMPORALITY - HOURS
     ******************************************************************************************************************/

    private function manageHours($typeChart='', &$data=array())
    {
        if($typeChart=='linechart') {
            return $this->getHourLineChart($data);
        } else if($typeChart=='barchart') {
            return $this->getHourBarChart($data);
        }

    }

    private function getHourBarChart(&$data=array())
    {
        $codeHTML = '<script>
        new Chart(document.getElementById(\'' . $this->_currentChartOptions['canvasId'] . '\').getContext(\'2d\'), {
            type: "bar",
            data: {
                labels: ["00H00","01H00","02H00","03H00","04H00","05H00","06H00","07H00","08H00","09H00","10H00","11H00","12H00","13H00","14H00","15H00","16H00","17H00","18H00","19H00","20H00","21H00","22H00","23H00",],
                datasets: [{
                    data: [';

        foreach ($this->getHourRepartition($data) as $hour=>$qty)
        {
            $codeHTML .= $qty . ',';
        }


        $codeHTML .= '],
                    steppedLine:' . $this->_currentChartOptions['steppedLine'] . ',
                    label: "' . $this->_currentChartOptions['label'] . '",
                    backgroundColor: "' . $this->_currentChartOptions['color'] . '",
                    fill: ' . $this->_currentChartOptions['fill'] . '
                    },
    ]
  },
  options: {
    title: {
      display: true,
      text: "' . $this->_currentChartOptions['title'] . '"
    }
  }
});
    </script>';

        return $codeHTML;
    }

    private function getHourLineChart(&$data=array())
    {
        $codeHTML = '<script>
        new Chart(document.getElementById(\'' . $this->_currentChartOptions['canvasId'] . '\').getContext(\'2d\'), {
            type: "line",
            data: {
                labels: ["00H00","01H00","02H00","03H00","04H00","05H00","06H00","07H00","08H00","09H00","10H00","11H00","12H00","13H00","14H00","15H00","16H00","17H00","18H00","19H00","20H00","21H00","22H00","23H00",],
                datasets: [{
                    data: [';

        foreach ($this->getHourRepartition($data) as $hour=>$qty)
        {
            $codeHTML .= $qty . ',';
        }


        $codeHTML .= '],
                    steppedLine:' . $this->_currentChartOptions['steppedLine'] . ',
                    label: "' . $this->_currentChartOptions['label'] . '",
                    borderColor: "' . $this->_currentChartOptions['color'] . '",
                    fill: ' . $this->_currentChartOptions['fill'] . '
                    },
    ]
  },
  options: {
    title: {
      display: true,
      text: "' . $this->_currentChartOptions['title'] . '"
    }
  }
});
    </script>';

        return $codeHTML;
    }




    /*******************************************************************************************************************
     *                                              USERS - BOT
     ******************************************************************************************************************/

    private function manageBots($typeChart='', &$data=array())
    {
        if($typeChart=='piechart') {
            return $this->getBotPieChart($data, 'pie');
        } else {
            return $this->getBotPieChart($data, $typeChart);
        }
    }


    private function getBotPieChart(&$data=array(), $typeChart='pie')
    {
        $bots = $this->getBotRepartition($data);
        $codeHTML = '<script>
        new Chart(document.getElementById(\'' . $this->_currentChartOptions['canvasId'] . '\').getContext(\'2d\'), {
            type: "' . $typeChart . '",
            data: {
                labels: [';
        foreach ($bots as $bot=>$quantity) {
            $codeHTML .= '"' . $bot . '", ';
        }
        $codeHTML .='],
                datasets: [{
                    data: [';

        foreach ($bots as $bot=>$quantity)
        {
            $codeHTML .= $quantity . ',';
        }

        $codeHTML .= '],
                    label: "' . $this->_currentChartOptions['label'] . '",
                    backgroundColor: [';

        if( count($bots) > count($this->_currentChartOptions['colors']) ) {
            $this->_currentChartOptions['colors'] = array_merge($this->_currentChartOptions['colors'], $this->pickRandomColors(count($bots) - count($this->_currentChartOptions['colors'])));
        }

        $codeHTML .= $this->arrayColorToString($this->_currentChartOptions['colors']) . ', ';

        $codeHTML .= ']
                    },
    ]
  },
  options: {
    title: {
      display: true,
      text: "' . $this->_currentChartOptions['title'] . '"
    }
  }
});
    </script>';

        return $codeHTML;
    }


    /*******************************************************************************************************************
     *                                              USERS - BROWSER
     ******************************************************************************************************************/

    private function manageBrowsers($typeChart='', &$data=array())
    {
        if($typeChart=='piechart') {
            return $this->getBrowserPieChart($data, 'pie');
        } else {
            return $this->getBrowserPieChart($data, $typeChart);
        }
    }


    private function getBrowserPieChart(&$data=array(), $typeChart='pie')
    {
        $bots = $this->getBrowserRepartition($data);
        $codeHTML = '<script>
        new Chart(document.getElementById(\'' . $this->_currentChartOptions['canvasId'] . '\').getContext(\'2d\'), {
            type: "' . $typeChart . '",
            data: {
                labels: [';
        foreach ($bots as $bot=>$quantity) {
            $codeHTML .= '"' . $bot . '", ';
        }
        $codeHTML .='],
                datasets: [{
                    data: [';

        foreach ($bots as $bot=>$quantity)
        {
            $codeHTML .= $quantity . ',';
        }

        $codeHTML .= '],
                    label: "' . $this->_currentChartOptions['label'] . '",
                    backgroundColor: [';

        if( count($bots) > count($this->_currentChartOptions['colors']) ) {
            $this->_currentChartOptions['colors'] = array_merge($this->_currentChartOptions['colors'], $this->pickRandomColors(count($bots) - count($this->_currentChartOptions['colors'])));
        }

        $codeHTML .= $this->arrayColorToString($this->_currentChartOptions['colors']) . ', ';

        $codeHTML .= ']
                    },
    ]
  },
  options: {
    title: {
      display: true,
      text: "' . $this->_currentChartOptions['title'] . '"
    }
  }
});
    </script>';

        return $codeHTML;
    }



    /*******************************************************************************************************************
     *                                              USERS - MOBILE
     ******************************************************************************************************************/

    private function manageMobiles($typeChart='', &$data=array())
    {
        if($typeChart=='piechart') {
            return $this->getMobilePieChart($data, 'pie');
        } else {
            return $this->getMobilePieChart($data, $typeChart);
        }
    }


    private function getMobilePieChart(&$data=array(), $typeChart='pie')
    {
        $bots = $this->getMobileRepartition($data);
        $codeHTML = '<script>
        new Chart(document.getElementById(\'' . $this->_currentChartOptions['canvasId'] . '\').getContext(\'2d\'), {
            type: "' . $typeChart . '",
            data: {
                labels: [';
        foreach ($bots as $bot=>$quantity) {
            $codeHTML .= '"' . $bot . '", ';
        }
        $codeHTML .='],
                datasets: [{
                    data: [';

        foreach ($bots as $bot=>$quantity)
        {
            $codeHTML .= $quantity . ',';
        }

        $codeHTML .= '],
                    label: "' . $this->_currentChartOptions['label'] . '",
                    backgroundColor: [';

        if( count($bots) > count($this->_currentChartOptions['colors']) ) {
            $this->_currentChartOptions['colors'] = array_merge($this->_currentChartOptions['colors'], $this->pickRandomColors(count($bots) - count($this->_currentChartOptions['colors'])));
        }

        $codeHTML .= $this->arrayColorToString($this->_currentChartOptions['colors']) . ', ';

        $codeHTML .= ']
                    },
    ]
  },
  options: {
    title: {
      display: true,
      text: "' . $this->_currentChartOptions['title'] . '"
    }
  }
});
    </script>';

        return $codeHTML;
    }


    /*******************************************************************************************************************
     *                                              USERS - NAVIGATION
     ******************************************************************************************************************/

    private function manageNavigation(&$data=array())
    {
        return $this->getGrapheParcours($data);
    }




    private function getGrapheParcours($data=array())
    {
        // TODO add ignored urls
        // First, let's order data in a proper way.
        $nodes = array();
        $edges = array();

        foreach ($data['navigation'] as $datum)
        {
            if( trim($datum['0'])!='' )
            {
                if( !array_key_exists(trim($datum['0']), $nodes ) )
                {

                    $nodes[trim($datum['0'])]=1;
                }
                else
                {
                    $nodes[trim($datum['0'])]++;
                }
            }
            if( trim($datum['1'])!='' )
            {
                if( !array_key_exists(trim($datum['1']), $nodes ) )
                {

                    $nodes[trim($datum['1'])]=1;
                }
                else
                {
                    $nodes[trim($datum['1'])]++;
                }
            }
        }

        foreach ($data['navigation'] as $datum)
        {
            if( trim($datum['1'])!='' && trim($datum['0'])!='' )
            {
                // The edges array follows this pattern : ( origin=>([destination=> nbOccurency],...), ...)
                if (isset($edges[trim($datum['0'])] ))
                {
                    if( isset( $edges[trim($datum['0'])] [trim($datum['1'])] ) )
                    {
                        $edges[trim($datum['0'])] [trim($datum['1'])]++;
                    }
                    else
                    {
                        $edges[trim($datum['0'])] [trim($datum['1'])]=1;
                    }
                }
                else
                {
                    $edges[trim($datum['0'])]=array();
                    if( isset( $edges[trim($datum['0'])] [trim($datum['1'])] ) )
                    {
                        $edges[trim($datum['0'])] [trim($datum['1'])]++;
                    }
                    else
                    {
                        $edges[trim($datum['0'])] [trim($datum['1'])]=1;
                    }
                }
            }
        }
        $codeHTML = '
        <div id="' . $this->_currentChartOptions['divContainingCanvasId'] . '"></div>
        
        <script type="text/javascript">
            // create an array with nodes
          var nodes = new vis.DataSet([';

        $count = 1;
        $labelToId = array();

        foreach ($nodes as $node=>$value)
        {
            $codeHTML .= '
            {id: ' . $count . ', label: \'' . $node . '\'},';
            $labelToId[$node]=$count;
            $count++;
        }

        $codeHTML .= '
          ]);
        
          // create an array with edges
          var edges = new vis.DataSet([
          ';

        foreach ($edges as $originEdge=>$destinations)
        {
            foreach ($destinations as $destination=>$quantity)
            {
                $codeHTML .= '
            {from: ' . $labelToId[$originEdge] . ', to: ' . $labelToId[$destination] . ', value: ' . $quantity . ', arrows:\'to\', dashes:true},';
            }
        }

        $codeHTML .= '
          ]);
        
          // create a network
          var container = document.getElementById("' . $this->_currentChartOptions['divContainingCanvasId'] . '");
          var data = {
            nodes: nodes,
            edges: edges
          };
          var options = {};
          var network = new vis.Network(container, data, options);
        </script>';
        return $codeHTML;
    }

    /*******************************************************************************************************************
     *                                              USERS - SYSTEM
     ******************************************************************************************************************/

    private function manageSystems($typeChart='', &$data=array())
    {
        if($typeChart=='piechart') {
            return $this->getSystemPieChart($data, 'pie');
        } else {
            return $this->getSystemPieChart($data, $typeChart);
        }
    }


    private function getSystemPieChart(&$data=array(), $typeChart='pie')
    {
        $systems = $this->getSystemRepartition($data);
        $codeHTML = '<script>
        new Chart(document.getElementById(\'' . $this->_currentChartOptions['canvasId'] . '\').getContext(\'2d\'), {
            type: "' . $typeChart . '",
            data: {
                labels: [';
        foreach ($systems as $system=>$quantity) {
            $codeHTML .= '"' . $system . '", ';
        }
        $codeHTML .='],
                datasets: [{
                    data: [';

        foreach ($systems as $system=>$quantity)
        {
            $codeHTML .= $quantity . ',';
        }

        $codeHTML .= '],
                    label: "' . $this->_currentChartOptions['label'] . '",
                    backgroundColor: [';

        if( count($systems) > count($this->_currentChartOptions['colors']) ) {
            $this->_currentChartOptions['colors'] = array_merge($this->_currentChartOptions['colors'], $this->pickRandomColors(count($systems) - count($this->_currentChartOptions['colors'])));
        }

        $codeHTML .= $this->arrayColorToString($this->_currentChartOptions['colors']) . ', ';

        $codeHTML .= ']
                    },
    ]
  },
  options: {
    title: {
      display: true,
      text: "' . $this->_currentChartOptions['title'] . '"
    }
  }
});
    </script>';

        return $codeHTML;
    }






/*



    public function getDaysLineChart($height='', $width='', $class='', $idCanevas='', $idDiv='', $title='' )
    {
        if($idCanevas=='')
        {
            $idCanevas = $this->generateRandomString();
        }
        if ($height=='')
        {
            $height = 400;
        }
        if ($width=='')
        {
            $width = 600;
        }
        $browserData = array();
        foreach ($this->data as $datum)
        {
            if(!key_exists($datum['deviceName'], $browserData))
            {
                $browserData[$datum['deviceName']]=1;
            }
            else
            {
                $browserData[$datum['deviceName']]++;
            }
        }
        $hoursData = array(
            '00'=>0,
            '01'=>0,
            '02'=>0,
            '03'=>0,
            '04'=>0,
            '05'=>0,
            '06'=>0,
            '07'=>0,
            '08'=>0,
            '09'=>0,
            '10'=>0,
            '11'=>0,
            '12'=>0,
            '13'=>0,
            '14'=>0,
            '15'=>0,
            '16'=>0,
            '17'=>0,
            '18'=>0,
            '19'=>0,
            '20'=>0,
            '21'=>0,
            '22'=>0,
            '23'=>0,
        );
        foreach ($this->data as $datum)
        {
            $chunks = explode('-', $datum['date']);
            if(count($chunks)>2)
            {
                if(key_exists($chunks[1], $hoursData))
                {
                    $hoursData[$chunks[1]]++;
                }
            }

        }

        $codeHTML = '
    <div id="' . $idDiv . '" class="' . $class . '" style="width: ' . $width . 'px; height: ' . $height . 'px;">
        <canvas id="' . $idCanevas . '" style="position: relative;"></canvas>
    </div>
    <script>
        new Chart(document.getElementById("' . $idCanevas . '"), {
            type: "line",
            data: {
                labels: ["","Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"],
                datasets: [{
                    data: [';

        foreach ($hoursData as $hour=>$qty)
        {
            $codeHTML .= $qty . ',';
        }


        $codeHTML .= '],
                    label: "Affluence",
                    borderColor: "#3e95cd",
                    fill: true
                    },
    ]
  },
  options: {
    title: {
      display: true,
      text: "' . $title . '"
    }
  }
});


    </script>
    ';
        return $codeHTML;
    }


    public function getNavigationModeChart($height='', $width='', $class='', $idCanevas='', $idDiv='', $typeChart='pie', $title='' )
{
    if($idCanevas=='')
    {
        $idCanevas = $this->generateRandomString();
    }
    if ($height=='')
    {
        $height = 400;
    }
    if ($width=='')
    {
        $width = 600;
    }
    if ($typeChart=='')
    {
        $typeChart='pie';
    }
    $browserData = array();
    foreach ($this->data as $datum)
    {
        if(!key_exists($datum['deviceName'], $browserData))
        {
            $browserData[$datum['deviceName']]=1;
        }
        else
        {
            $browserData[$datum['deviceName']]++;
        }
    }
    unset($browserData['']);
    $codeHTML = '
    <div id="' . $idDiv . '" class="' . $class . '" style="width: ' . $width . 'px; height: ' . $height . 'px;">
        <canvas id="' . $idCanevas . '" style="position: relative;"></canvas>
    </div>
    <script>
        data = {
            labels: [';

    foreach ($browserData as $browser=>$quantity)
    {
        $codeHTML .= '\'' . $browser . '\', ';
    }
    $codeHTML .= '],
            datasets: [
            {
                label: "Utilisateurs",
                backgroundColor : [
                                    ' . $this->arrayColorToString($this->getRandomColors(count($browserData))) . '
                                    ],
                data: [';
    foreach ($browserData as $browser=>$quantity)
    {
        $codeHTML .= $quantity . ', ';
    }

    $codeHTML .= ']
                }
            ],
        
        };
        options = {
                    title:{
                            display: true,
                            text: "' . $title . '"
                          }
        };
        var ctx = document.getElementById("' . $idCanevas . '");
        var myPieChart = new Chart(ctx,{
            type: "' . $typeChart . '",
            data: data,
            options: options
            });
    </script>
    ';
    return $codeHTML;
}


    public function getOSChart($height='', $width='', $class='', $idCanevas='', $idDiv='', $typeChart='pie', $title='' )
    {
        if($idCanevas=='')
        {
            $idCanevas = $this->generateRandomString();
        }
        if ($height=='')
        {
            $height = 400;
        }
        if ($width=='')
        {
            $width = 600;
        }
        if ($typeChart=='')
        {
            $typeChart='pie';
        }
        $OSData = array();
        foreach ($this->data as $datum)
        {
            if(!key_exists($datum['OSName'], $OSData))
            {
                if($datum['OSName']!='' && $datum['OSName']!='unknown')
                {
                    $OSData[$datum['OSName']]=1;
                }
            }
            else
            {
                $OSData[$datum['OSName']]++;
            }
        }
        $codeHTML = '
    <div id="' . $idDiv . '" class="' . $class . '" style="width: ' . $width . 'px; height: ' . $height . 'px;">
        <canvas id="' . $idCanevas . '" style="position: relative;"></canvas>
    </div>
    <script>
        data = {
            labels: [';

        foreach ($OSData as $OS=>$quantity)
        {
            $codeHTML .= '\'' . $OS . '\', ';
        }
        $codeHTML .= '],
            datasets: [
            {
                label: "Utilisateurs",
                backgroundColor : [
                                    ' . $this->arrayColorToString($this->getRandomColors(count($OSData))) . '
                                    ],
                data: [';
        foreach ($OSData as $OS=>$quantity)
        {
            $codeHTML .= $quantity . ', ';
        }

        $codeHTML .= ']
                }
            ],
        
        };
        options = {
                    title:{
                            display: true,
                            text: "' . $title . '"
                          }
        };
        var ctx = document.getElementById("' . $idCanevas . '");
        var myPieChart = new Chart(ctx,{
            type: "' . $typeChart . '",
            data: data,
            options: options
            });
    </script>
    ';
        return $codeHTML;
    }

    public function getPagesChart($height='', $width='', $class='', $idCanevas='', $idDiv='', $typeChart='pie', $title='' )
    {
        if($idCanevas=='')
        {
            $idCanevas = $this->generateRandomString();
        }
        if ($height=='')
        {
            $height = 400;
        }
        if ($width=='')
        {
            $width = 600;
        }
        if ($typeChart=='')
        {
            $typeChart='pie';
        }
        $OSData = array();
        foreach ($this->data as $datum)
        {
            if(!key_exists($datum['urlVisited'], $OSData))
            {
                if($datum['urlVisited']!='' && $datum['urlVisited']!='unknown')
                {
                    $OSData[$datum['urlVisited']]=1;
                }
            }
            else
            {
                $OSData[$datum['urlVisited']]++;
            }
        }
        $codeHTML = '
    <div id="' . $idDiv . '" class="' . $class . '" style="width: ' . $width . 'px; height: ' . $height . 'px;">
        <canvas id="' . $idCanevas . '" style="position: relative;"></canvas>
    </div>
    <script>
        data = {
            labels: [';

        foreach ($OSData as $OS=>$quantity)
        {
            $codeHTML .= '\'' . $OS . '\', ';
        }
        $codeHTML .= '],
            datasets: [
            {
                label: "Utilisateurs",
                backgroundColor : [
                                    ' . $this->arrayColorToString($this->getRandomColors(count($OSData))) . '
                                    ],
                data: [';
        foreach ($OSData as $OS=>$quantity)
        {
            $codeHTML .= $quantity . ', ';
        }

        $codeHTML .= ']
                }
            ],
        
        };
        options = {
                    title:{
                            display: true,
                            text: "' . $title . '"
                          }
        };
        var ctx = document.getElementById("' . $idCanevas . '");
        var myPieChart = new Chart(ctx,{
            type: "' . $typeChart . '",
            data: data,
            options: options
            });
    </script>
    ';
        return $codeHTML;
    }



    public function getCountryChart($height='', $width='', $class='', $idCanevas='', $idDiv='', $typeChart='pie', $title='' )
    {
        if($idCanevas=='')
        {
            $idCanevas = $this->generateRandomString();
        }
        if ($height=='')
        {
            $height = 400;
        }
        if ($width=='')
        {
            $width = 600;
        }
        if ($typeChart=='')
        {
            $typeChart='pie';
        }
        $OSData = array();
        foreach ($this->data as $datum)
        {
            if(!key_exists($datum['country'], $OSData))
            {
                $OSData[$datum['country']]=1;
            }
            else
            {
                $OSData[$datum['country']]++;
            }
        }
        unset($OSData['']);
        $codeHTML = '
    <div id="' . $idDiv . '" class="' . $class . '" style="width: ' . $width . 'px; height: ' . $height . 'px;">
        <canvas id="' . $idCanevas . '" style="position: relative;"></canvas>
    </div>
    <script>
        data = {
            labels: [';

        foreach ($OSData as $OS=>$quantity)
        {
            $codeHTML .= '\'' . $OS . '\', ';
        }
        $codeHTML .= '],
            datasets: [
            {
                label: "Pays",
                backgroundColor : [
                                    ' . $this->arrayColorToString($this->getRandomColors(count($OSData))) . '
                                    ],
                data: [';
        foreach ($OSData as $OS=>$quantity)
        {
            $codeHTML .= $quantity . ', ';
        }

        $codeHTML .= ']
                }
            ],
        
        };
        options = {
                    title:{
                            display: true,
                            text: "' . $title . '"
                          }
        };
        var ctx = document.getElementById("' . $idCanevas . '");
        var myPieChart = new Chart(ctx,{
            type: "' . $typeChart . '",
            data: data,
            options: options
            });
    </script>
    ';
        return $codeHTML;
    }

    public function getCountryMap($height='', $width='', $class='', $idCanevas='', $idDiv='', $typeChart='pie', $title='' )
    {
        if($idCanevas=='')
        {
            $idCanevas = $this->generateRandomString();
        }
        if ($height=='')
        {
            $height = 400;
        }
        if ($width=='')
        {
            $width = 600;
        }
        if ($typeChart=='')
        {
            $typeChart='pie';
        }
        $OSData = array();
        foreach ($this->data as $datum)
        {
            if(!key_exists($datum['country'], $OSData))
            {
                $OSData[$datum['country']]=1;
            }
            else
            {
                $OSData[$datum['country']]++;
            }
        }
        unset($OSData['']);
        $codeHTML = '
        <div id="container" style="position: relative; width: 500px; height: 300px;"></div>
        
        <script>
            var basic_choropleth = new Datamap({
                element: document.getElementById("container"),
                projection: \'mercator\',
                fills: {
                    defaultFill: "#ABDDA4",
                    authorHasTraveledTo: "#fa0fa0"
                    },
                data: {';
        require_once __DIR__ . '/src/Countries.php';
        foreach ($OSData as $country=>$quantity)
        {
            $countryData = new Countries($country);
            if($countryData->getCurrentCountry() == 'null')
            {
                require_once __DIR__ . '/src/errors.php';
                new errors('pays manquant ' . $country, 'lacking country ' . $country);
            }
            else
            {

                $codeHTML .= '
                ' . $countryData->getCurrentCountry()['alpha-3'] . ': { fillKey: "authorHasTraveledTo" },';
            }
        }
        $codeHTML .='
                    }
                }
            );
        </script>
';
        return $codeHTML;
    }

    public function getGPSMap($height='', $width='', $class='', $idCanevas='', $idDiv='', $typeChart='pie', $title='' )
    {
        if($idCanevas=='')
        {
            $idCanevas = $this->generateRandomString();
        }
        if ($height=='')
        {
            $height = 400;
        }
        if ($width=='')
        {
            $width = 600;
        }
        if ($typeChart=='')
        {
            $typeChart='pie';
        }
        $OSData = array();
        foreach ($this->data as $datum)
        {
            if(!key_exists($datum['country'], $OSData))
            {
                $OSData[$datum['country']]=1;
            }
            else
            {
                $OSData[$datum['country']]++;
            }
        }
        unset($OSData['']);
        $codeHTML = '
                
        <div id=\'cartoLab\' style=\'width: 400px; height: 300px;\'></div>
        
         <script>
            let labs = [
            {"titre":"TUBA","offre":"Innovation ouverte| Dispositifs de m&eacute;diation| Animation \/ R&eacute;seau","gouvernance":"Association","publicCible":"Universit&eacute;| Industrie| Porteur de projet \/ Start-up| M&eacute;tropole| Grand Public| Etudiants","expertise":"Usages \/ Soci&eacute;t&eacute;","web":"http:\/\/www.tuba-lyon.com\/","description":"Le TUB&Agrave;, lieu d&rsquo;innovation et d&rsquo;exp&eacute;rimentation pour la ville de demain, est port&eacute; par l&rsquo;association Lyon Urban Data. Il favorise l&rsquo;innovation, l&rsquo;incubation et le d&eacute;veloppement de services urbains s&rsquo;appuyant sur les donn&eacute;es num&eacute;riques priv&eacute;es et publiques.","adressePostale":"145 Cours Lafayette, 69006 Lyon","latitude":45.764914,"longitude":4.853938}, 
            {"titre":"Urban Lab ERASME","offre":"Prototypage| Programmation &eacute;ducative","gouvernance":"M&eacute;tropole","publicCible":"Etudiants","expertise":"Num&eacute;rique \/ Electronique| Usages \/ Soci&eacute;t&eacute;| Education \/ Formation","web":"http:\/\/www.erasme.org\/","description":"Erasme, living lab de la M&eacute;tropole de Lyon. Nous cherchons &agrave; mettre toutes les possibilit&eacute;s du num&eacute;rique au service de la ville intelligente, de la transmission du savoir, de la culture et de l&rsquo;action sociale. Pour cela nous faisons appel &agrave; des m&eacute;thodes de co-design et &agrave; toutes les ressources et d&eacute;tournements de la culture num&eacute;rique.","adressePostale":"Studio 2, P&ocirc;le Pixel, 24 Rue Emile Decorps, 69100 Villeurbanne","latitude":45.757754,"longitude":4.89861}, 
            {"titre":"YOUFACTORY","offre":"Prototypage","gouvernance":"Association","publicCible":"Porteur de projet \/ Start-up| Grand Public","expertise":"Num&eacute;rique \/ Electronique| Ing&eacute;nierie \/ M&eacute;canique| Arts \/ Culture","web":"http:\/\/youfactory.co\/","description":"YOUFACTORY, c&rsquo;est la nouvelle usine collaborative pour les industries cr&eacute;atives, les makers, l&rsquo;artisanat et les PME. Un lieu unique en Rh&ocirc;ne-Alpes pour booster l&rsquo;innovation !","adressePostale":"P&ocirc;le PIXEL, 50 rue Antoine Primat, 69100 Villeurbanne","latitude":45.759136,"longitude":4.898806}, 
            {"titre":"Fabrique d\'Objets Libres","offre":"Prototypage","gouvernance":"Association","publicCible":"Grand Public","expertise":"Num&eacute;rique \/ Electronique| Ing&eacute;nierie \/ M&eacute;canique","web":"http:\/\/www.fablab-lyon.fr\/","description":"Le FABLAB  &raquo; LA FABRIQUE D&rsquo;OBJETS LIBRES  &raquo;\nvous propose l&rsquo;acc&egrave;s &agrave; :\n&ndash; une d&eacute;coupe Laser trotec 100 (dimensions du plateau 600x300 mm),\n&ndash; des imprimantes 3D, Zortrax et Ultimaker (dimensions 20x20x20 cm),\n&ndash; des plotters de d&eacute;coupe vinyle,\n&ndash; un atelier &eacute;lectronique, arduino, raspberry pi&hellip;,\n&ndash; une floqueuse, sans oublier un atelier d&rsquo;outils&hellip;","adressePostale":"36 Bd Edouard Herriot, 69800 Saint-Priest","latitude":45.694549,"longitude":4.937352}, 
            {"titre":"Fabrique de l\'Innovation","offre":"Prototypage| Innovation ouverte","gouvernance":"Universit&eacute;","publicCible":"Grand Public","expertise":"Num&eacute;rique \/ Electronique| Ing&eacute;nierie \/ M&eacute;canique| Education \/ Formation","web":"https:\/\/www.universite-lyon.fr\/innovation-entrepreneurship\/the-innovation-factory-innovating-and-approaching-entrepreneurship-differently-\/la-fabrique-de-l-innovation-innover-et-entreprendre-autrement-avec-l-universite-de-lyon-972.kjsp","description":"La Fabrique de l&rsquo;Innovation mobilise les talents et la cr&eacute;ativit&eacute; des &eacute;tudiants, l&rsquo;excellence scientifique des chercheurs mais aussi les plateformes technologiques et scientifiques de tous les &eacute;tablissements de l&rsquo;Universit&eacute; de Lyon pour d&eacute;velopper des projets innovants. En synergie avec le P&ocirc;le &Eacute;tudiant Pour l&rsquo;Innovation, le Transfert et l&rsquo;Entrepreneuriat (PEPITE) Beelys et la Soci&eacute;t&eacute; d&rsquo;Acc&eacute;l&eacute;ration du Transfert des Technologies (SATT) Pulsalys, elle renforce les liens entre les milieux acad&eacute;miques et socio-&eacute;conomiques.","adressePostale":"28-30 Avenue Gaston Berger, 69100 Villeurbanne","latitude":45.782021,"longitude":4.870861}, 
            {"titre":"Technicentre SNCF d\'Oulins","offre":"Prototypage","gouvernance":"Industrie","publicCible":"Industrie| Interne","expertise":"Ing&eacute;nierie \/ M&eacute;canique","web":"","description":"Technicentre SNCF Oulins","adressePostale":"25 Quai Pierre Semard, 69350 La Mulati&egrave;re","latitude":45.724757,"longitude":4.816734}, 
            {"titre":"574 (SNCF)","offre":"Prototypage","gouvernance":"Industrie","publicCible":"Interne","expertise":"Num&eacute;rique \/ Electronique","web":"https:\/\/www.digital.sncf.com\/transformation-numerique\/les-574","description":"Les 574 sont les &laquo; maisons du digital &raquo; du groupe ferroviaire. Implant&eacute;s &agrave; Saint-Denis, Toulouse, Nantes et maintenant Lyon, ces espaces m&ecirc;lant co-working, showroom et zones d&rsquo;exp&eacute;rimentation h&eacute;bergent les Fabs (centres d&rsquo;expertises d&eacute;di&eacute;s au big data, design, internet industriel et open innovation), des &eacute;quipes projets digitaux et accueillent des collaborateurs pour des moments d&rsquo;inspiration ou de co-cr&eacute;ation. Ce sont des lieux o&ugrave; l&rsquo;on invente, on vit et on partage le num&eacute;rique.","adressePostale":"116 Cours Lafayette, 69003 Lyon","latitude":45.763549,"longitude":4.850979}, 
            {"titre":"SEB Lab","offre":"Prototypage","gouvernance":"Industrie","publicCible":"Interne","expertise":"Ing&eacute;nierie \/ M&eacute;canique","web":"http:\/\/www.groupeseb.com\/fr\/content\/politique-et-enjeux","description":"L&rsquo;inititive SEB Lab est n&eacute;e du constat que les &eacute;quipes innovation du Groupe ne disposaient pas toujours d&rsquo;un espace et de moyens agiles pour travailler sur des projets amont dans le processus de cr&eacute;ation de nouveaux produits. Nous avons donc d&eacute;cid&eacute; de cr&eacute;er un lieu exp&eacute;rimental, inspir&eacute; des Fab Labs, d&eacute;di&eacute; &agrave; la Cr&eacute;ativit&eacute; et &agrave; la Mat&eacute;rialisation. Nous organisons des sessions de travail regroupant des &eacute;quipes mixtes (marketing, recherche, design, experts internes et externes) autour d&rsquo;une probl&eacute;matique d&rsquo;innovation. Les concepts identifi&eacute;s sont ensuite imm&eacute;diatement concr&eacute;tis&eacute;s dans l&rsquo;atelier, voire test&eacute;s sur place. C&rsquo;est un formidable outil d&rsquo;aide &agrave; la cr&eacute;ation qui sera renforc&eacute; dans les ann&eacute;es &agrave; venir car il nous permet de rapidement d&eacute;tecter le potentiel d&rsquo;un produit.","adressePostale":"112 Chemin du Moulin Carron, ,69130 &Eacute;cully","latitude":45.79798,"longitude":4.77208}, 
            {"titre":"Silex3DPrint","offre":"Prototypage","gouvernance":"Industrie","publicCible":"Industrie","expertise":"Ing&eacute;nierie \/ M&eacute;canique","web":"https:\/\/silex3dprint.fr\/","description":"Service d\'impression 3D ( LYON \/ SAINT-ETIENNE \/ CLERMONT-FERRAND \/ ROANNE ). Silex3D votre service d&rsquo;impression 3D vous offre son savoir-faire dans le domaine de la fabrication additive. Nous r&eacute;alisons tous types de prototypes industriels, maquettes, pi&egrave;ces d&rsquo;exceptions, concept car &amp; petites s&eacute;ries, jusqu&rsquo;&agrave; plusieurs milliers de pi&egrave;ces. Notre atelier de production ainsi que notre parc de machines 3D dernier cri en constante &eacute;volution nous permet de vous offrir des produits de qualit&eacute; reconnus dans de nombreux secteurs industriels tels que l&rsquo;a&eacute;ronautique, l&rsquo;automobile, la d&eacute;fense, l&rsquo;usinage de pr&eacute;cision, l&rsquo;injection plastique et le secteur du luxe. Notre bureau d\'&eacute;tudes et de CAO est &agrave; votre service pour vous accompagner dans la conception, la modification et l\'am&eacute;lioration de vos fichiers d\'impression. Une production de qualit&eacute; et optimale assur&eacute;e !","adressePostale":"Avenue de la p&eacute;pini&egrave;re, ZAE des Portes du Beaujolais, 69240 Thizy-les-Bourgs","latitude":46.033938,"longitude":4.298124}, 
            {"titre":"PRODIUM","offre":"Prototypage","gouvernance":"Industrie","publicCible":"Industrie","expertise":"Num&eacute;rique \/ Electronique| Ing&eacute;nierie \/ M&eacute;canique","web":"http:\/\/www.prodium.fr\/","description":"PRODIUM est sp&eacute;cialis&eacute; dans la sous-traitance industrielle dans les domaines de la plasturgie, de la m&eacute;tallurgie et de l\'&eacute;lectronique.\n PRODIUM intervient de la conception &agrave; la r&eacute;alisation de produits multi-technologies, du prototype &agrave; la s&eacute;rie et pour des projets globaux.\n PRODIUM prend en charge vos projets globaux, dans les domaines de la plasturgie, de la m&eacute;canique, de la fonderie et de l\'&eacute;lectronique, de l\'&eacute;tude &agrave; la production, avec une sp&eacute;cialit&eacute; de prototypage rapide.","adressePostale":"Le Semanet 1, 2 All&eacute;e de la Combe, 69380 Lissieu","latitude":45.841516,"longitude":4.733676}, 
            {"titre":"Modellus","offre":"Prototypage","gouvernance":"Industrie","publicCible":"Industrie","expertise":"Ing&eacute;nierie \/ M&eacute;canique","web":"","description":"Modellus est un service de conception, prototypage rapide et production par fabrication additive ( Impression 3D).\n Notre service de prototypage rapide vous permet d\'obtenir vos &eacute;l&eacute;ments fonctionnels sous 24\/72H dans une grande gamme de mat&eacute;riaux techniques : PETG, POM, ABS, Composites carbone, PA12, PA6, r&eacute;sines m&eacute;tacrylates....\n Obtenez la production de vos petites et moyennes s&eacute;ries dans les meilleurs d&eacute;lais gr&acirc;ce &agrave; la r&eacute;activit&eacute; de nos proc&eacute;d&eacute;s de production additive : FDM, SLA et SLS.\n Notre service de conception met &agrave; disposition son savoir faire dans la cr&eacute;ation de mod&egrave;les 3D destin&eacute;s &agrave; l\'impression 3D.\n Description produits et services \n Prototypage rapide, impression 3d, fabrication digitale, fabrication additive, prototypes visuel et fonctionnels, production en s&eacute;rie, gestion de projet, copie d\'art","adressePostale":"115 Avenue Thiers, 69006 Lyon","latitude":45.770218,"longitude":4.86183}, 
            {"titre":"ATTOM","offre":"Prototypage","gouvernance":"Industrie","publicCible":"Industrie","expertise":"Ing&eacute;nierie \/ M&eacute;canique","web":"http:\/\/attom.eu\/","description":"M&eacute;catronique g&eacute;n&eacute;rale et dispositifs m&eacute;dicaux. \nETUDES PR&Eacute;LIMINAIRES : Nous vous proposons des &eacute;tudes techniques, propri&eacute;t&eacute; industrielle et financement de l&rsquo;innovation vous permettant de formaliser un besoin, de produire les analyses (fonctionnelles, risques, &hellip;) et une roadmap strat&eacute;gique pour votre projet.\nPROTOTYPAGE : Nous vous proposons de r&eacute;aliser des prototypes fonctionnels con&ccedil;us pour s&rsquo;adapter &agrave; leurs destinataires (client final, investisseur,&hellip;) vous permettant de r&eacute;aliser des &laquo; tests d&rsquo;utilisation &raquo; rapidement et &agrave; bas co&ucirc;t, tout en cr&eacute;ant ou simulant les fonctions essentielles de votre futur produit.\nD&Eacute;VELOPPEMENT EXP&Eacute;RIMENTAL (R&amp;D) : Une &eacute;quipe d&eacute;di&eacute;e pour vos projets externalis&eacute;s de d&eacute;veloppement exp&eacute;rimental. Notre valeur ajout&eacute;e : une gestion rigoureuse, une expertise technique de haut niveau, une approche propri&eacute;t&eacute; industrielle d&egrave;s le d&eacute;marrage du projet (design to patent) et un r&eacute;seau d&rsquo;experts, de distributeurs et de sous-traitants qualifi&eacute;s.","adressePostale":"24 Ave. Joannes Masset, 69009 Lyon","latitude":45.769713,"longitude":4.800828}, 
            ];
            </script>
        
<script>
var mymap = L.map(\'cartoLab\').setView([45.75, 4.85], 9);

// On ajoute une tuile sur la carte avec les outils utilisÃ©s.
L.tileLayer(\'https://{s}.piano.tiles.data.gouv.fr/fr{r}/{z}/{x}/{y}.png\', {
    maxZoom: 18,
    attribution: \'<a href="https://www.openstreetmap.org/">OpenStreetMap</a> | \' +
        \'<a href="https://www.mapbox.com/">Mapbox</a>\',
    id: \'mapbox.streets\'
}).addTo(mymap);

// Gestion des marqueurs et groupes de marqueurs sur la carte.
var markers = L.markerClusterGroup();

for (i = 0; i < labs.length; i++)
{
    if( labs[i][\'latitude\'] != 0 && labs[i][\'longitude\'] != 0 ) 
    {
        //var codePopUp = forgerTextePopUp(labs[i]);
        var codePopUp = "";

        markers.addLayer(L.marker([labs[i][\'latitude\'], labs[i][\'longitude\']]).bindPopup(codePopUp));
    }
}
mymap.addLayer(markers);


</script>

        
';
        return $codeHTML;
    }

    public function getOrganizationChart($height='', $width='', $class='', $idCanevas='', $idDiv='', $typeChart='pie', $title='' )
    {
        if($idCanevas=='')
        {
            $idCanevas = $this->generateRandomString();
        }
        if ($height=='')
        {
            $height = 400;
        }
        if ($width=='')
        {
            $width = 600;
        }
        if ($typeChart=='')
        {
            $typeChart='pie';
        }
        $OSData = array();
        foreach ($this->data as $datum)
        {
            if(!key_exists($datum['organization'], $OSData))
            {
                $OSData[$datum['organization']]=1;
            }
            else
            {
                $OSData[$datum['organization']]++;
            }
        }
        unset($OSData['']);
        $codeHTML = '
    <div id="' . $idDiv . '" class="' . $class . '" style="width: ' . $width . 'px; height: ' . $height . 'px;">
        <canvas id="' . $idCanevas . '" style="position: relative;"></canvas>
    </div>
    <script>
        data = {
            labels: [';

        foreach ($OSData as $OS=>$quantity)
        {
            $codeHTML .= '
            \'' . substr($this->cleanString($OS),0, 9 ) . '...\', ';
        }
        $codeHTML .= '
        ],
            datasets: [
            {
                label: "Organizations",
                backgroundColor : [
                                    ' . $this->arrayColorToString($this->getRandomColors(count($OSData))) . '
                                    ],
                data: [';
        foreach ($OSData as $OS=>$quantity)
        {
            $codeHTML .= $quantity . ', ';
        }

        $codeHTML .= ']
                }
            ],
        
        };
        options = {
                    title:{
                            display: true,
                            text: "' . $title . '"
                          }
        };
        var ctx = document.getElementById("' . $idCanevas . '");
        var myPieChart = new Chart(ctx,{
            type: "' . $typeChart . '",
            data: data,
            options: options
            });
    </script>
    ';
        return $codeHTML;
    }


    public function getOSBarChart($height='', $width='', $class='', $idCanevas='', $idDiv='', $title='' )
    {
        if($idCanevas=='')
        {
            $idCanevas = $this->generateRandomString();
        }
        if ($height=='')
        {
            $height = 400;
        }
        if ($width=='')
        {
            $width = 600;
        }
        $browserData = array();
        foreach ($this->data as $datum)
        {
            if(!key_exists($datum['OSName'], $browserData))
            {
                if($datum['OSName']!='' && $datum['OSName']!='unknown')
                {
                    $browserData[$datum['OSName']]=1;
                }
            }
            else
            {
                $browserData[$datum['OSName']]++;
            }
        }

        $codeHTML = '
                <div id="' . $idDiv . '" class="' . $class . '" style="width: ' . $width . 'px;height: ' . $height . 'px;">
                    <canvas id="' . $idCanevas .'" ></canvas>
                </div>
                
                <script>
                    new Chart(document.getElementById("' . $idCanevas . '"), {
    type: "bar",
    data: {
      labels: [';

        foreach ($browserData as $browser=>$quantity)
        {
            $codeHTML .= '\'' . $browser . '\', ';
        }

        $codeHTML .= '],
      datasets: [
        {
          label: "Utilisateurs",
          backgroundColor: [' . $this->arrayColorToString($this->getRandomColors(count($browserData))) . '],
          data: [';

        foreach ($browserData as $browser=>$quantity)
        {
            $codeHTML .= $quantity . ', ';
        }

        $codeHTML.= ']
        }
      ]
    },
    options: {
      legend: { display: false },
      title: {
        display: true,
        text: "' . $title . '"
      }
    }
});
                </script>
                ';

        return $codeHTML;
    }



    public function getDaysBarChart($height='', $width='', $class='', $idCanevas='', $idDiv='', $title='' )
    {
        // TODO Changer les labels : les noms des mois à la place de 01 à 12
        if($idCanevas=='')
        {
            $idCanevas = $this->generateRandomString();
        }
        if ($height=='')
        {
            $height = 400;
        }
        if ($width=='')
        {
            $width = 600;
        }
        $monthsData = array(
            '01'=>0,
            '02'=>0,
            '03'=>0,
            '04'=>0,
            '05'=>0,
            '06'=>0,
            '07'=>0,
            '08'=>0,
            '09'=>0,
            '10'=>0,
            '11'=>0,
            '12'=>0,
        );
        foreach ($this->data as $datum)
        {
            $chunks = explode('-', $datum['date']);
            if( count($chunks)>2)
            {
                if(key_exists($chunks[1], $monthsData))
                {
                    $monthsData[$chunks[1]]++;
                }
            }
        }

        $codeHTML = '
                <div id="' . $idDiv . '" class="' . $class . '" style="width: ' . $width . 'px;height: ' . $height . 'px;">
                    <canvas id="' . $idCanevas .'" ></canvas>
                </div>
                
                <script>
                    new Chart(document.getElementById("' . $idCanevas . '"), {
    type: "bar",
    data: {
      labels: [';

        foreach ($monthsData as $browser=>$quantity)
        {
            $codeHTML .= '\'' . $browser . '\', ';
        }

        $codeHTML .= '],
      datasets: [
        {
          label: "Utilisateurs",
          backgroundColor: [' . $this->arrayColorToString($this->getRandomColors(count($monthsData))) . '],
          data: [';

        foreach ($monthsData as $browser=>$quantity)
        {
            $codeHTML .= $quantity . ', ';
        }

        $codeHTML.= ']
        }
      ]
    },
    options: {
      legend: { display: false },
      title: {
        display: true,
        text: "' . $title . '"
      }
    }
});
                </script>
                ';

        return $codeHTML;
    }


    /*
     *          BROWSER
     *//*

    /**
     * Returns a complete HTML/js code containing a div which contains a canvas containing the barchart
     * @param string $height
     * @param string $width
     * @param string $class
     * @param string $idCanevas
     * @param string $idDiv
     * @param string $title
     * @return string
     *//*
    public function getBrowserBarChart($height='', $width='', $class='', $idCanevas='', $idDiv='', $title='' )
    {
        if($idCanevas=='')
        {
            $idCanevas = $this->generateRandomString();
        }
        if ($height=='')
        {
            $height = 400;
        }
        if ($width=='')
        {
            $width = 600;
        }
        $browserData = array();
        foreach ($this->data as $datum)
        {
            if(!key_exists($datum['agentName'], $browserData))
            {
                if(trim($datum['agentName'])!='' && ($datum['agentName'])!='unknown')
                {
                    $browserData[$datum['agentName']]=1;
                }
            }
            else
            {
                $browserData[$datum['agentName']]++;
            }
        }

        $codeHTML = '
                <div id="' . $idDiv . '" class="' . $class . '" style="width: ' . $width . 'px;height: ' . $height . 'px;">
                    <canvas id="' . $idCanevas .'" ></canvas>
                </div>
                
                <script>
                    new Chart(document.getElementById("' . $idCanevas . '"), {
    type: "bar",
    data: {
      labels: [';

        foreach ($browserData as $browser=>$quantity)
        {
            $codeHTML .= '\'' . $browser . '\', ';
        }

        $codeHTML .= '],
      datasets: [
        {
          label: "Utilisateurs",
          backgroundColor: [' . $this->arrayColorToString($this->getRandomColors(count($browserData))) . '],
          data: [';

        foreach ($browserData as $browser=>$quantity)
        {
            $codeHTML .= $quantity . ', ';
        }

        $codeHTML.= ']
        }
      ]
    },
    options: {
      legend: { display: false },
      title: {
        display: true,
        text: "' . $title . '"
      }
    }
});
                </script>
                ';

        return $codeHTML;
    }

    /**
     * Returns a complete HTML/js code containing a div which contains a canvas containing the chart (pie chart or
     * donut chart).
     * @param string $height The div's height.
     * @param string $width The div's width.
     * @param string $class THe CSS classes that have to be added to the container div.
     * @param string $idCanevas
     * @param string $idDiv
     * @param string $typeChart TYpe of desired chart pie|doughnut.
     * @param string $title Title of the chart
     * @return string The complete HTML js code of the cart.
     *//*
    public function getBrowserPieChart($height='', $width='', $class='', $idCanevas='', $idDiv='', $typeChart='pie', $title='')
    {
        if($idCanevas=='')
        {
            $idCanevas = $this->generateRandomString();
        }
        if ($height=='')
        {
            $height = 400;
        }
        if ($width=='')
        {
            $width = 600;
        }
        if ($typeChart=='')
        {
            $typeChart='pie';
        }
        $browserData = array();
        foreach ($this->data as $datum)
        {
            if(!key_exists($datum['agentName'], $browserData))
            {
                if(trim($datum['agentName'])!='' && ($datum['agentName'])!='unknown')
                {
                    $browserData[$datum['agentName']]=1;
                }
            }
            else
            {
                $browserData[$datum['agentName']]++;
            }
        }
        $codeHTML = '
    <div id="' . $idDiv . '" class="' . $class . '" style="width: ' . $width . 'px; height: ' . $height . 'px;">
        <canvas id="' . $idCanevas . '" style="position: relative;"></canvas>
    </div>
    <script>
        data = {
            labels: [';

        foreach ($browserData as $browser=>$quantity)
        {
            $codeHTML .= '\'' . $browser . '\', ';
        }
        $codeHTML .= '],
            datasets: [
            {
                label: "Utilisateurs",
                backgroundColor : [
                                    ' . $this->arrayColorToString($this->getRandomColors(count($browserData))) . '
                                    ],
                data: [';
        foreach ($browserData as $browser=>$quantity)
        {
            $codeHTML .= $quantity . ', ';
        }

        $codeHTML .= ']
                }
            ],
        
        };
        options = {
                    title:{
                            display: true,
                            text: "' . $title . '"
                          }
        };
        var ctx = document.getElementById("' . $idCanevas . '");
        var myPieChart = new Chart(ctx,{
            type: "' . $typeChart . '",
            data: data,
            options: options
            });
    </script>
    ';
        return $codeHTML;
    }


    public function getEnginePieChart($height='', $width='', $class='', $idCanevas='', $idDiv='', $typeChart='pie', $title='' )
    {
        if($idCanevas=='')
        {
            $idCanevas = $this->generateRandomString();
        }
        if ($height=='')
        {
            $height = 400;
        }
        if ($width=='')
        {
            $width = 600;
        }
        if ($typeChart=='')
        {
            $typeChart='pie';
        }
        $OSEngine = array();
        foreach ($this->data as $datum)
        {
            if(!key_exists($datum['displayEngine'], $OSEngine))
            {
                $OSEngine[$datum['displayEngine']]=1;
            }
            else
            {
                $OSEngine[$datum['displayEngine']]++;
            }
        }
        unset($OSEngine['']);
        $codeHTML = '
    <div id="' . $idDiv . '" class="' . $class . '" style="width: ' . $width . 'px; height: ' . $height . 'px;">
        <canvas id="' . $idCanevas . '" style="position: relative;"></canvas>
    </div>
    <script>
        data = {
            labels: [';

        foreach ($OSEngine as $OS=>$quantity)
        {
            $codeHTML .= '\'' . $OS . '\', ';
        }
        $codeHTML .= '],
            datasets: [
            {
                label: "Utilisateurs",
                backgroundColor : [
                                    ' . $this->arrayColorToString($this->getRandomColors(count($OSEngine))) . '
                                    ],
                data: [';
        foreach ($OSEngine as $OS=>$quantity)
        {
            $codeHTML .= $quantity . ', ';
        }

        $codeHTML .= ']
                }
            ],
        
        };
        options = {
                    title:{
                            display: true,
                            text: "' . $title . '"
                          }
        };
        var ctx = document.getElementById("' . $idCanevas . '");
        var myPieChart = new Chart(ctx,{
            type: "' . $typeChart . '",
            data: data,
            options: options
            });
    </script>
    ';
        return $codeHTML;
    }


    /**
     * Returns just a dummy example of a working chart with hardcoded data.
     * @return string
     *//*
    public function getExample()
    {
        return '
        <div style="height:200px; width:400px;">
            <canvas id="myChart" style="position: relative;"></canvas>
            <script>
            var ctx = document.getElementById("myChart");
            var myChart = new Chart(ctx, {
                type: \'bar\',
                data: {
                    labels: ["Red", "Blue", "Yellow", "Green", "Purple", "Orange"],
                    datasets: [{
                        label: \'# of Votes\',
                        data: [12, 19, 3, 5, 2, 3],
                        backgroundColor: [
                            \'rgba(255, 99, 132, 0.2)\',
                            \'rgba(54, 162, 235, 0.2)\',
                            \'rgba(255, 206, 86, 0.2)\',
                            \'rgba(75, 192, 192, 0.2)\',
                            \'rgba(153, 102, 255, 0.2)\',
                            \'rgba(255, 159, 64, 0.2)\'
                        ],
                        borderColor: [
                            \'rgba(255,99,132,1)\',
                            \'rgba(54, 162, 235, 1)\',
                            \'rgba(255, 206, 86, 1)\',
                            \'rgba(75, 192, 192, 1)\',
                            \'rgba(153, 102, 255, 1)\',
                            \'rgba(255, 159, 64, 1)\'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero:true
                            }
                        }]
                    }
                }
            });
            </script>
        </div>
';
    }


    private function cleanString($string='')
    {
        $forbidden = array("'", "\"", ".", ",", "(", ")", '-');
        return str_replace($forbidden, "", $string);
    }


    /**
     * Generator reading the file (this one could be huge, generators became mandatory)
     * @param string $pathToFile The path to the file to open and iterate to.
     * @return Generator Generator iterating upon the lines.
     *//*
    private function getLinesFromFile($pathToFile='')
    {
        $fileHandle = fopen($pathToFile, 'r');
        while (false !== $line = fgets($fileHandle))
        {
            yield $line;
        }
        fclose($fileHandle);
    }

    /**
     * Data are just in a csv file. Each line of the file has to be extracted and each entry of the line has to
     * be extracted too.
     *//*
    private function orderData()
    {
        $lines = $this->getLinesFromFile(__DIR__ . '/src/data/VanitasVisitors.csv');
        foreach ($lines as $line)
        {
            $newEntry = array();
            $line = explode("\t", $line);
            // Dirty but eficient way to make sure to not go further than the length of the array in case of missing data.
            if(count($line) > 2 )
            {
                for ($i = 0; $i < count($line); $i++)
                {
                    switch ($i)
                    {
                        case 0:
                            $newEntry['ip'] = $line[$i];
                            break;
                        case 1:
                            $newEntry['country'] = $line[$i];
                            break;
                        case 2:
                            $newEntry['countryTag'] = $line[$i];
                            break;
                        case 3:
                            $newEntry['city'] = $line[$i];
                            break;
                        case 4:
                            $newEntry['continentTag'] = $line[$i];
                            break;
                        case 5:
                            $newEntry['latitude'] = $line[$i];
                            break;
                        case 6:
                            $newEntry['longitude'] = $line[$i];
                            break;
                        case 7:
                            $newEntry['organization'] = $line[$i];
                            break;
                        case 8:
                            $newEntry['urlVisited'] = str_replace("http:", "https:", $line[$i]);
                            break;
                        case 9:
                            $newEntry['comingfFromUrl'] = str_replace("http:", "https:", $line[$i]);
                            break;
                        case 10:
                            $newEntry['date'] = $line[$i];
                            break;
                        case 11:
                            $newEntry['time'] = $line[$i];
                            break;
                        case 12:
                            $newEntry['displayEngine'] = $line[$i];
                            break;
                        case 13:
                            $newEntry['agentType'] = $line[$i];
                            break;
                        case 14:
                            $newEntry['agentName'] = $line[$i];
                            break;
                        case 15:
                            $newEntry['agentVersion'] = $line[$i];
                            break;
                        case 16:
                            $newEntry['OSName'] = $line[$i];
                            break;
                        case 17:
                            $newEntry['OSVersionNumber'] = $line[$i];
                            break;
                        case 18:
                            $newEntry['OSPlateForme'] = $line[$i];
                            break;
                        case 19:
                            $newEntry['deviceName'] = $line[$i];
                            break;
                        case 20:
                            $newEntry['brand'] = $line[$i];
                            break;
                        case 21:
                            $newEntry['modele'] = $line[$i];
                            break;
                    }
                }
                array_push($this->data, $newEntry);
            }
        }
    }*/

    /*******************************************************************************************************************
     *                                                      MISC
     ******************************************************************************************************************/

    private function manageOptions(&$options=array())
    {
        $this->_currentChartOptions = array(
            'canvasId'=>$this->generateRandomString(20),
            'color'=>$this->pickRandomColors(1)[0],
            'colors'=>array(),
            'divContainingCanvasId'=>$this->generateRandomString(20),
            'divContainingCanvasClass'=>'',
            'fill'=>'true',
            'height'=>'400px',
            'label'=>'',
            'steppedLine'=>'false',
            'title'=>'',
            'width'=>'400px',
        );

        foreach ($options as $optionName=>$optionValue) {
            switch ($optionName) {
                case 'canvasId':
                    $this->_currentChartOptions['canvasId'] = $optionValue;
                    break;
                case 'color':
                    $this->_currentChartOptions['color'] = $optionValue;
                    break;
                case 'colors':
                    $this->_currentChartOptions['colors'] = $optionValue;
                    break;
                case 'divContainingCanvasId':
                    $this->_currentChartOptions['divContainingCanvasId'] = $optionValue;
                    break;
                case 'divContainingCanvasClass':
                    $this->_currentChartOptions['divContainingCanvasClass'] = $optionValue;
                    break;
                case 'fill':
                    if($optionValue=='true' || $optionValue=='false') {
                        $this->_currentChartOptions['fill'] = $optionValue;
                    }
                    break;
                case 'height':
                    $this->_currentChartOptions['height'] = $optionValue;
                    break;
                case 'label':
                    $this->_currentChartOptions['label'] = $optionValue;
                    break;
                case 'steppedLine':
                    if(in_array($optionValue, array('true', 'false', 'before', 'after', 'middle')) ) {
                        $this->_currentChartOptions['steppedLine'] = $optionValue;
                    }
                    break;
                case 'title':
                    $this->_currentChartOptions['title'] = $optionValue;
                    break;
                case 'width':
                    $this->_currentChartOptions['width'] = $optionValue;
                    break;
                default:
                    break;
            }
        }
    }



    /**
     * Generate a random string (useful in order to get unique HTML ids for charts)
     * @see https://stackoverflow.com/questions/4356289/php-random-string-generator
     * @param int $length Optionnal, the length of the desired random string. If inexplicit, it will be set to 10.
     * @return string The random string generated.
     */
    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }




    /**
     * Returns an array of flat colors in hexadecimal values.
     * @param int $nbColor The number of desired colors.
     * @return array The array of flat colors in hexadecimal values.
     */
    private function pickRandomColors($nbColor =1)
    {
        $proposedColors = [
            ["#3e95cd","#8e5ea2","#3cba9f","#e8c3b9","#c45850"],
            ["#1F518B","#1488C8","#F7E041","#E2413E","#E91222"],
            ["#F7A61B","#7CBF42","#EB4A24","#34A8C8","#30A443"],
            ["#FE6EDA","#7CB0C3","#5BADAF","#96CEB4","#FFCC5C"],
            ["#FF6B57","#FF884D","#32526E","#81B9C3","#41C3AC"],
            ["#3FC380","#89C4F4","#E08283","#B388DD","#FEC956"],
            ["#3498DB","#E67E22","#D35400","#E74C3C","#1ABC9C"],
            ["#302B2D","#1F8D85","#22409A","#FBCD36","#F26C68"],
            ["#F77D74","#B00843","#CFBA7A","#56C8BE","#4A5269"],
            ["#FFC312","#C4E538","#12CBC4","#FDA7DF","#ED4C67"],
            ["#F79F1F","#A3CB38","#1289A7","#D980FA","#B53471"],
            ["#EE5A24","#009432","#0652DD","#9980FA","#833471"],
            ["#EA2027","#006266","#1B1464","#5758BB","#6F1E51"],
            ];
        $colorsSelected = [];
        while (count($colorsSelected) < $nbColor)
        {
            if( count($colorsSelected)+5 <= $nbColor )
            {
                $selectedColorLine = rand(0,count($proposedColors)-1);
                $colorsSelected = array_merge($colorsSelected, $proposedColors[$selectedColorLine]);
                unset($proposedColors[$selectedColorLine]);
                $proposedColors=array_values($proposedColors);
            }
            else
            {
                $numberOfLackingColors = $nbColor-count($colorsSelected);
                $selectedColorLine = rand(0,count($proposedColors)-1);
                $colorsSelected = array_merge($colorsSelected, array_slice($proposedColors[$selectedColorLine], 0, $numberOfLackingColors) );
            }
        }
        return $colorsSelected;
    }


    private function arrayColorToString($arrayColor=array())
    {
        return '"' . implode('","', $arrayColor) . '"';
    }



    private function getHourRepartition(&$data)
    {
        $hoursData = array(
            '00'=>0,
            '01'=>0,
            '02'=>0,
            '03'=>0,
            '04'=>0,
            '05'=>0,
            '06'=>0,
            '07'=>0,
            '08'=>0,
            '09'=>0,
            '10'=>0,
            '11'=>0,
            '12'=>0,
            '13'=>0,
            '14'=>0,
            '15'=>0,
            '16'=>0,
            '17'=>0,
            '18'=>0,
            '19'=>0,
            '20'=>0,
            '21'=>0,
            '22'=>0,
            '23'=>0,
        );
        foreach ($data['hour'] as $datum)
        {
            $hour = explode(':', $datum)[0];
            if(key_exists($hour, $hoursData))
            {
                $hoursData[$hour]++;
            }
        }
        return $hoursData;
    }


    private function getCountryRepartition(&$data=array())
    {
        $countries =array(
            'Unknown'=>0,
        );
        foreach ($data['country'] as $country) {
            if($country=='') {
                $countries['Unknown']+=1;
            } else if(!array_key_exists($country, $countries)) {
                $countries[$country]=1;
            } else {
                $countries[$country]+=1;
            }
        }
        return $countries;
    }



    private function getBotRepartition(&$data=array())
    {
        $bots = array(
            'human'=>0,
            'bot'=>0,
        );
        foreach ($data['bot'] as $isBot) {
            if($isBot=='') {
                $bots['bot']+=1;
            } else if($isBot=='0') {
                $bots['bot']+=1;
            } else {
                $bots['human']+=1;
            }
        }
        return $bots;
    }

    private function getBrowserRepartition(&$data=array())
    {
        $browsers =array(
            'Unknown'=>0,
        );
        foreach ($data['browser'] as $browser) {
            if($browser=='') {
                $browsers['Unknown']+=1;
            } else if(!array_key_exists($browser, $browsers)) {
                $browsers[$browser]=1;
            } else {
                $browsers[$browser]+=1;
            }
        }
        return $browsers;
    }


    private function getMobileRepartition(&$data=array())
    {
        $mobiles = array(
            'isMobile'=>0,
            'isNotMobile'=>0,
        );
        foreach ($data['mobile'] as $isMobile) {
            if($isMobile=='') {
                $mobiles['isNotMobile']+=1;
            } else if($isMobile=='1') {
                $mobiles['isNotMobile']+=1;
            } else {
                $mobiles['isMobile']+=1;
            }
        }
        return $mobiles;
    }


    private function getSystemRepartition(&$data=array())
    {
        $systems =array(
            'Unknown'=>0,
        );
        foreach ($data['system'] as $system) {
            if($system=='') {
                $systems['Unknown']+=1;
            } else if(!array_key_exists($system, $systems)) {
                $systems[$system]=1;
            } else {
                $systems[$system]+=1;
            }
        }
        return $systems;
    }



    /*******************************************************************************************************************
     *                                                      GETTERS
     ******************************************************************************************************************/


    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->_data;
    }
}
