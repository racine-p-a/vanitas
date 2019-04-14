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
$myResults = new Results(
    array(
        'date',
        'hour',
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
        <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.min.js"></script>
    </head>
    
    <body>
        ' . $myResults->getChart('linechart', 'hour', $myData, array(
            'canvasId'=>'myHoursLineChart',
            'divContainingCanvasId'=>'myHoursLIneChartBlock',
            'label'=>'Hourly affluence',
            'color'=>'#3e95cd',
            'fill'=>'true',
            'title'=>'Hours Linechart',
            'height'=>'500px',
            'width'=>'600px',
    ) ) . '
    </body>
</html>';