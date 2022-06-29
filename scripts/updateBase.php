<?php
include('../class/DatabaseClass.php');
require 'vendor/autoload.php';

use GuzzleHttp\Promise;
use GuzzleHttp\Client;

session_start();

// We need to delete the process time limitation of a basic php page because the full update can take multiple hours
ini_set('max_execution_time', 0);

// Database information
$host = 'localhost';
$username = 'root';
$dbName = 'databasetflapi';
$hostTable = 'host_database';
$uniTable = 'university_database';
$savedTable = 'saved_data';

// Creating a client object (from the guzzle api) using the tfl api base url
$client = new Client(['base_uri' => 'https://api.tfl.gov.uk/']);

// Connection to the database using our Database class and the previously set database information
$connect = new Database($host, $username, '', $dbName);

// Checking if the user entered a post code and checked a radio button
$isPostCodeGiven = isset($_GET['enteredPostCode']);
$isPlaceTypeRadioChecked = isset($_GET['placeType']);

// Checking if the user clicked on the "update" or the "delete" button
$isUpdateButtonClicked = isset($_GET['updateButton']);
$isDeleteButtonClicked = isset($_GET['deleteButton']);

// Getting all the host and the university post code
$postCodeHost = $connect->load($hostTable);
$postCodeUni = $connect->load($uniTable);

/**
 * If a postcode is given by the user, we change the process so it will calculate the travel
 * between the given post code (a university one for exemple) and all the others (all the host ones for exemple)
 * 
 * If on postcode is given (that mean that the user want to update all the journeys), we delete all the saved data
 * in the $savedTable
 */
if ($isPostCodeGiven) {

    $enteredPostCode = $_GET['enteredPostCode'];

    if ($isUpdateButtonClicked) {

        if ($isPlaceTypeRadioChecked) {

            $radioChoice = $_GET['placeType'];

            // We select which table to work with
            if ($radioChoice == 'uniRadio') {
                $actionTable = $uniTable;
                $delPostCodeName = 'UniPostCode';
            } else if ($radioChoice == 'hostRadio') {
                $actionTable = $hostTable;
                $delPostCodeName = 'HostPostCode';
            }

            // We delete the already existing travel informations related to the entered post code
            $connect->delete($savedTable, $delPostCodeName, $enteredPostCode);
        } else {

            $_SESSION['error'] = 'Please check one of the place type option.';
            header("Location: ../updatePage.php");
            exit;
        }

        // We check if the entered post code exist in the selected table
        $results = $connect->selectWhere($actionTable, '*', 'Postcode', $enteredPostCode, 'ASC', 'Postcode');

        // If it exist in the selected database, we change the $postCode variables to this postcode
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
    } elseif ($isDeleteButtonClicked) {

        if ($isPlaceTypeRadioChecked) {

            // We select which table to work with
            $radioChoice = $_GET['placeType'];
            if ($radioChoice == 'uniRadio') {
                $delPostCodeName = 'UniPostCode';
            } else if ($radioChoice == 'hostRadio') {
                $delPostCodeName = 'HostPostCode';
            }
        } else {

            $_SESSION['error'] = 'Please check one of the place type option.';
            header("Location: ../updatePage.php");
            exit;
        }

        // We check if the entered post code exist in the selected table
        $results = $connect->selectWhere($savedTable, '*', $delPostCodeName, $enteredPostCode, 'ASC', $delPostCodeName);

        // If it exist in the selected database, we delete the entered post code related travel in the savedTable
        if (empty($results) == false) {

            $connect->delete($savedTable, $delPostCodeName, $enteredPostCode);
        } else {

            $_SESSION['error'] = 'Please enter a correct post code that is in the database or choose the correct place type.';
            header("Location: ../updatePage.php");
            exit;
        }

        $_SESSION['done'] = 'Delete done !';
        header("Location: ../updatePage.php");
        exit;
    }
} else {

    $connect->truncate($savedTable);
}

// This array will contain every results indexed with there corresponding two journey postcodes
$tableAllRequest = [];

$promisesTbl = [];

$requestSent = 0;
$errors = 0;
$insertCount = 0;

// for each existing university, we calculate asynchronously the travel time between it and all the existing host
foreach ($postCodeUni as $uniPostCode) {

    $uni = ltrim(utf8_encode($uniPostCode['Postcode']));

    foreach ($postCodeHost as $hostPostCode) {

        $host = ltrim(utf8_encode($hostPostCode['Postcode']));

        // We create a promise between the current host and the current university using the tfl api and we add it to the $promisesTbl array
        $promisesTbl[$uni . "," . $host] = $client->getAsync('/journey/journeyresults/' . $uni . '/to/' . $host . '?app_key=a59c7dbb0d51419d8d3f9dfbf09bd5cc');
        $requestSent++;

        /**
         * The tfl api is limited to 500 request per minutes, so every 500 promises added to the $promisesTbl array,
         * We asynchronously execute each request (contained in the promises) and then stock them in the $tableAllRequest array.
         * Then we empty the $promisesTbl to start working on another 500 requests.
         * We finally wait 1 minute (tfl api restriction)
         */
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

// We do the process again for the last request that ar not contained in a 500 series
$promisesResults = processTravel($promisesTbl);
$promisesTbl = $promisesResults[0];
$tableAllRequest = array_merge($tableAllRequest, $promisesResults[1]);
$errors = $promisesResults[2];

// We then add each result or update them in the $savedTable
foreach ($tableAllRequest as $travelName => $travelTime) {

    $postCodes = explode(',', $travelName);
    $uni = $postCodes[0];
    $host = $postCodes[1];

    $connect->update($savedTable, $uni, $host, $travelTime);

    $insertCount++;
}

$_SESSION['done'] = 'Update done !';
header('Location: ../updatePage.php');

/**
 * This function will execute asynchronously every promises contained in the $promises array and then return in an array :
 * The $promise array emptied,
 * The $tableRequest array that contain every result from every successfull request indexed with the $promiseKey that contain the two post code of his journey
 * The $tableError array that contain every failed request
 *
 * @param [array] $promises
 * @return Array
 */
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
