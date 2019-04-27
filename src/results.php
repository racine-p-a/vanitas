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
        $botChosen = array();
        foreach ($dataNames as $dataName) {
            if(!in_array($dataName[0], $this->_authorizedSetsOfData)) {
                $errorMessage = 'Unknown type of data asked : ' . $dataName[0] . ' . Please choose one of them : ';
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
            $this->_data[$dataName[0]] = array();
            $botChosen[$dataName[0]]=$dataName[1];
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
                            if($botChosen['bot'] || $csvLine[11]=='0') {
                                array_push($this->_data['bot'], $csvLine[11]);
                            }
                            break;
                        case 'browser':
                            if($botChosen['browser'] || $csvLine[11]=='0') {
                                array_push($this->_data['browser'], $csvLine[12]);
                            }
                            break;
                        case 'country':
                            if($botChosen['country'] || $csvLine[11]=='0') {
                                array_push($this->_data['country'], $csvLine[7]);
                            }
                            break;
                        case 'hour':
                            if($botChosen['hour'] || $csvLine[11]=='0') {
                                array_push($this->_data['hour'], $csvLine[1]);
                            }
                            break;
                        case 'mobile':
                            if($botChosen['mobile'] || $csvLine[11]=='0') {
                                array_push($this->_data['mobile'], $csvLine[16]);
                            }
                            break;
                        case 'navigation':
                            if($botChosen['navigation'] || $csvLine[11]=='0') {
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
                            }
                            break;
                        case 'system':
                            if($botChosen['system'] || $csvLine[11]=='0') {
                                array_push($this->_data['system'], $csvLine[17]);
                            }
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
