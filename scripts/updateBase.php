<?php
include('../class/DatabaseClass.php');
require 'vendor/autoload.php';

use GuzzleHttp\Promise;
use GuzzleHttp\Client;

session_start();
ini_set('max_execution_time', 0);

$username = 'root';
$dbName = 'databasetflapi';
$hostTable = 'host_database';
$uniTable = 'university_database';
$savedTable = 'saved_data';

$client = new Client(['base_uri' => 'https://api.tfl.gov.uk/']);
$connect = new Database($username, '', $dbName);

$isPostCodeGiven = isset($_GET['enteredPostCode']);
$isPlaceTypeRadioChecked = isset($_GET['placeType']);

$postCodeHost = $connect->load($hostTable);
$postCodeUni = $connect->load($uniTable);

if ($isPostCodeGiven) {
    
    $enteredPostCode = $_GET['enteredPostCode'];
    
    if ($isPlaceTypeRadioChecked) {
        $radioChoice = $_GET['placeType'];
        if ($radioChoice == 'uniRadio') {
            $actionTable = $uniTable;
            $delPostCodeName = 'UniPostCode';
        } else if ($radioChoice == 'hostRadio') {
            $actionTable = $hostTable;
            $delPostCodeName = 'HostPostCode';
        }
        $connect->delete($savedTable, $delPostCodeName, $enteredPostCode);
    } else {
        $_SESSION['error'] = 'Please check one of the place type option.';
        header("Location: ../updatePage.php");
        exit;
    }
    
    $results = $connect->selectWhere($actionTable, '*', 'Postcode', $enteredPostCode, 'ASC', 'Postcode');
    var_dump($results);
    
    if (empty($results) == false) {
        if ($radioChoice == 'uniRadio') {
            $postCodeUni = $results;
        } else if ($radioChoice == 'hostRadio') {
            $postCodeHost = $results;
        }
    } else {
        $_SESSION['error'] = 'Please enter a correct post code or choose the correct place type.';
        header("Location: ../updatePage.php");
        exit;
    }
} else {
    $connect->truncate($savedTable);
}

$tableAllRequest = [];
$promisesTbl = [];

$requestSent = 0;
$errors = 0;
$insertCount = 0;

foreach ($postCodeUni as $uniPostCode) {
    
    $uni = ltrim(utf8_encode($uniPostCode['Postcode']));

    foreach ($postCodeHost as $hostPostCode) {

        $host = ltrim(utf8_encode($hostPostCode['Postcode']));

        if ($host != 'SW20 OPJ') {
            $promisesTbl[$uni . "," . $host] = $client->getAsync('/journey/journeyresults/' . $uni . '/to/' . $host . '?app_key=a59c7dbb0d51419d8d3f9dfbf09bd5cc');
            $requestSent++;
        }

        if ($requestSent == 499) {

            $promisesResults = processTravel($promisesTbl);
            $promisesTbl = $promisesResults[0];
            $tableAllRequest = array_merge($tableAllRequest, $promisesResults[1]);
            $errors = $promisesResults[2];

            sleep(60);
            $requestSent = 0;
        }
    }
}

$promisesResults = processTravel($promisesTbl);
$promisesTbl = $promisesResults[0];
$tableAllRequest = array_merge($tableAllRequest, $promisesResults[1]);
$errors = $promisesResults[2];

foreach ($tableAllRequest as $travelName => $travelTime) {
    $postCodes = explode(',', $travelName);
    $uni = $postCodes[0];
    $host = $postCodes[1];

    $connect->update($savedTable, $uni, $host, $travelTime);

    $insertCount++;
}

function processTravel($promises)
{
    $results = Promise\settle($promises)->wait();
    $tableRequest = [];
    $tableError = 0;

    foreach ($results as $promiseKey => $result) {

        if ($result['state'] == 'fulfilled') {
            if ($result['value']->getStatusCode() == 200) {
                $json = json_decode($result['value']->getBody()->getContents(), true);
                $journeys = $json['journeys'];

                $durations = [];

                foreach ($journeys as $journey) {
                    $duration = $journey['duration'];
                    array_push($durations, $duration);
                }

                $min = min($durations);

                $tableRequest[$promiseKey] = $min;
            } else if ($result['value']->getStatusCode() == 300) {
                $tableError++;
            }
        }
    }
    $promises = [];
    return [$promises, $tableRequest, $tableError];
}

header('Location: ../updatePage.php');
