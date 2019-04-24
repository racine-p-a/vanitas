<?php
/**
 * Project : vanitas
 * File : example.php
 * PROJET VANITAS.
 * @author  Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @copyright Copyright (c) 2019, Pierre-Alexandre RACINE <patcha.dev at{@} gmail dot[.] com>
 * @license http://www.gnu.org/licenses/lgpl.html
 * @date 11/04/19
 * @link https://github.com/racine-p-a/vanitas
 *
 * This file presents you various examples
 *
 */


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/src/results.php';
// It is highly recommended to gather all your data in one shot. It's way quicker.
$myResults = new Results(
    array(
        'hour',
        'country',
        'bot',
        'browser',
        'mobile',
        'system',
        'navigation',
        // todo add colored map of countries
        // todo add count of all users (count() ).
        // todo piechart 3D ?
    )
);

$myData = $myResults->getData();
/*
 * Now, you have all your data stored in an array made of arrays.
 * array(
 *     date => array(
 *         date1,
 *         date2,
 *         ...
 *         )
 *     hour => array(
 *          hour1,
 *          hour2,
 *          ...
 *         )
 * )
 *
 * and you can use it as you wish. For example :
 */

echo '<!doctype html>
<html>
    <head>
        <title>Charts examples</title>
        <!-- Import the following script to acces to charts. -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.min.js"></script>
        
        <!-- Import the following script and css to get the node chart. -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.js"></script>
         <link href="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.css" rel="stylesheet">
    </head>
    
    <body>
        ' . $myResults->getChart('linechart', 'hour', $myData, array(
        'canvasId'=>'myHoursLineChart',
        'divContainingCanvasId'=>'myHoursLineChartBlock',
        'divContainingCanvasClass'=>'class1 class2 class3', // You can apply the classes you want directly on the canvas container.
        'label'=>'Hourly affluence',
        'color'=>'#3e95cd',
        'fill'=>'true',
        'steppedLine'=>'false', // Gives your line chart an aspect of barchart. Choose among : false, true, before, after, middle.
        'title'=>'Hours Linechart',
        'height'=>'500px',
        'width'=>'600px',
    ) ) .
    $myResults->getChart('barchart', 'hour', $myData, array(
        'canvasId'=>'myHoursBarChart',
        'divContainingCanvasId'=>'myHoursBarChartBlock',
        'divContainingCanvasClass'=>'class1 class2 class3',
        'label'=>'Hourly affluence',
        'color'=>'#3e95cd',
        'fill'=>'true',
        'steppedLine'=>'false', // Gives your line chart an aspect of barchart. Choose among : false, true, before, after, middle.
        'title'=>'Hours Linechart',
        'height'=>'500px',
        'width'=>'600px',
    ) ).
    $myResults->getChart('piechart', 'country', $myData, array(
        'canvasId'=>'myCountriesPieChart',
        'divContainingCanvasId'=>'myCountriesPieChartBlock',
        'divContainingCanvasClass'=>'class1 class2 class3',
        'label'=>'Origin of my visitors',
        'colors'=>array('red', 'blue', 'yellow'),   // Please, note that only three colors are given here.
                                                    // If more colors are needed, others will be automatically chosen
        'fill'=>'true',
        'title'=>'Origin of my visitors',
        'height'=>'500px',
        'width'=>'600px',
    ) ) .
    $myResults->getChart('doughnut', 'country', $myData, array(
        'canvasId'=>'myCountriesDonutChart',
        'divContainingCanvasId'=>'myCountriesDonutChartBlock',
        'divContainingCanvasClass'=>'class1 class2 class3',
        'label'=>'Origin of my visitors',
        'colors'=>array(),
        'fill'=>'true',
        'title'=>'Origin of my visitors',
        'height'=>'500px',
        'width'=>'600px',
    ) ) .
    $myResults->getChart('piechart', 'bot', $myData, array(
        'canvasId'=>'myBotPieChart',
        'divContainingCanvasId'=>'myBotPieChartBlock',
        'divContainingCanvasClass'=>'class1 class2 class3',
        'label'=>'Proportion of bots',
        'colors'=>array('blue', 'grey'),   // Please, note that the first color always represents the human proportion.
        'fill'=>'true',
        'title'=>'Origin of my visitors',
        'height'=>'500px',
        'width'=>'600px',
    ) ) .
    $myResults->getChart('doughnut', 'bot', $myData, array(
        'canvasId'=>'myBotDonutChart',
        'divContainingCanvasId'=>'myBotDonutBlock',
        'divContainingCanvasClass'=>'class1 class2 class3',
        'label'=>'Proportion of bots',
        'colors'=>array('blue', 'grey'),   // Please, note that the first color always represents the human proportion.
        'fill'=>'true',
        'title'=>'Origin of my visitors',
        'height'=>'500px',
        'width'=>'600px',
    ) ) .
    $myResults->getChart('piechart', 'mobile', $myData, array(
        'canvasId'=>'myMobilePieChart',
        'divContainingCanvasId'=>'myMobilePieChartBlock',
        'divContainingCanvasClass'=>'class1 class2 class3',
        'label'=>'Proportion of bots',
        'colors'=>array('blue', 'grey'),   // Please, note that the first color always represents the human proportion.
        'fill'=>'true',
        'title'=>'Origin of my visitors',
        'height'=>'500px',
        'width'=>'600px',
    ) ) .
    $myResults->getChart('doughnut', 'mobile', $myData, array(
        'canvasId'=>'myMobileDonutChart',
        'divContainingCanvasId'=>'myMobileDonutBlock',
        'divContainingCanvasClass'=>'class1 class2 class3',
        'label'=>'Proportion of bots',
        'colors'=>array('blue', 'grey'),   // Please, note that the first color always represents the human proportion.
        'fill'=>'true',
        'title'=>'Origin of my visitors',
        'height'=>'500px',
        'width'=>'600px',
    ) ) .
    $myResults->getChart('piechart', 'browser', $myData, array(
        'canvasId'=>'myBrowserPieChart',
        'divContainingCanvasId'=>'myBrowserPieChartBlock',
        'divContainingCanvasClass'=>'class1 class2 class3',
        'label'=>'Browsers',
        'colors'=>array('blue', 'grey'),   // Please, note that the first color always represents the human proportion.
        'fill'=>'true',
        'title'=>'Which browsers are used ?',
        'height'=>'500px',
        'width'=>'600px',
    ) ) .
    $myResults->getChart('doughnut', 'browser', $myData, array(
        'canvasId'=>'myBrowserDonutChart',
        'divContainingCanvasId'=>'myBrowserDonutBlock',
        'divContainingCanvasClass'=>'class1 class2 class3',
        'label'=>'Browsers',
        'colors'=>array('blue', 'grey'),   // Please, note that the first color always represents the human proportion.
        'fill'=>'true',
        'title'=>'Which browsers are used ?',
        'height'=>'500px',
        'width'=>'600px',
    ) ) .
    $myResults->getChart('piechart', 'system', $myData, array(
        'canvasId'=>'mySystemPieChart',
        'divContainingCanvasId'=>'mySystemPieChartBlock',
        'divContainingCanvasClass'=>'class1 class2 class3',
        'label'=>'Operanding systems',
        'colors'=>array('blue', 'grey'),   // Please, note that the first color always represents the mobile proportion.
        'fill'=>'true',
        'title'=>'Which systems are used ?',
        'height'=>'500px',
        'width'=>'600px',
    ) ) .
    $myResults->getChart('doughnut', 'system', $myData, array(
        'canvasId'=>'mySystemDonutChart',
        'divContainingCanvasId'=>'mySystemDonutBlock',
        'divContainingCanvasClass'=>'class1 class2 class3',
        'label'=>'Operanding systems',
        'colors'=>array('blue', 'grey'),   // Please, note that the first color always represents the mobile proportion.
        'fill'=>'true',
        'title'=>'Which systems are used ?',
        'height'=>'500px',
        'width'=>'600px',
    ) ) .
    $myResults->getChart('nodes', 'navigation', $myData, array(
        'divContainingCanvasId'=>'myNavigationGraphBlock',
        'divContainingCanvasClass'=>'class1 class2 class3',
        'label'=>'Operanding systems',
        'colors'=>array('blue', 'grey'),   // Please, note that the first color always represents the mobile proportion.
        'fill'=>'true',
        'title'=>'Which systems are used ?',
        'height'=>'500px',
        'width'=>'600px',
    ) ) .'        
    </body>
</html>';