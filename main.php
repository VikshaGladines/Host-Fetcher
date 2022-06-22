<?php
include('RequestClass.php');
include('DatabaseClass.php');
require 'vendor/autoload.php';
use GuzzleHttp\Promise;
use GuzzleHttp\Client;

ini_set('max_execution_time', 0);

$username = 'root';
$dbName = 'databasetflapi';
$hostTable = 'host_database';
$uniTable = 'university_database';
$savedTable = 'saved_data';

$testUniPostCode = "'EN3 5PA'"; 

$client = new Client(['base_uri' => 'https://api.tfl.gov.uk/']);

$connect = new Database($username, '', $dbName);

$postCodeHost = $connect->load($hostTable);
$postCodeUni = $connect->load($uniTable);

$tableAllRequest = [];
$promisesTbl = [];

$requestSent = 0;
$errors = 0;
$insertCount = 0;

foreach ($postCodeUni as $uniPostCode) {

    $uni = ltrim(utf8_encode($uniPostCode['Postcode']));
    //var_dump($uni);

    foreach ($postCodeHost as $hostPostCode) {

        $host = ltrim(utf8_encode($hostPostCode['Postcode']));
        //var_dump($host);
        
        if ($host != 'SW20 OPJ') {
            $promisesTbl[$uni . ",". $host] = $client->getAsync('/journey/journeyresults/' . $uni . '/to/' . $host . '?app_key=a59c7dbb0d51419d8d3f9dfbf09bd5cc');
            $requestSent++;
        }
        
        // var_dump($requestSent);
        if ($requestSent == 499) {
          
            $promisesResults = processTravel($promisesTbl);
            $promisesTbl = $promisesResults[0];
            $tableAllRequest = array_merge($tableAllRequest, $promisesResults[1]);
            $errors=$promisesResults[2];
            
            sleep(60); 
            $requestSent = 0;
        }
    }
}

$promisesResults = processTravel($promisesTbl);
$promisesTbl = $promisesResults[0];
$tableAllRequest = array_merge($tableAllRequest, $promisesResults[1]);
$errors=$promisesResults[2];

//var_dump($errors);
//var_dump($tableAllRequest);

$connect->truncate($savedTable);

foreach($tableAllRequest as $travelName => $travelTime) {
    $postCodes = explode(',', $travelName);
    $uni = $postCodes[0];
    $host = $postCodes[1];
    
    $connect->update($savedTable, $uni, $host, $travelTime);

    $insertCount++;
}

echo 'number of register : ' . $insertCount;

$travels = $connect->selectWhere($savedTable, 'HostPostCode, TravelTime, UniPostCode', 'UniPostCode', $testUniPostCode, 'ASC', 'TravelTime');

foreach($travels as $travel) {
    echo $travel['HostPostCode'] . ' : ' . $travel['TravelTime'] . 'min <br>';
}   



function processTravel($promises) {
    //var_dump($promises);
    $results = Promise\settle($promises)->wait();
    $tableRequest = [];
    $tableError = 0;
            
    foreach($results as $promiseKey => $result) {
        
        if ($result['state'] == 'fulfilled') {
            if ($result['value']->getStatusCode() == 200) {
                $json = json_decode($result['value']->getBody()->getContents(), true);
                $journeys = $json['journeys'];
                
                $durations = [];
                
                //var_dump($promiseKey);
                //var_dump($journeys);
                
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
    return [$promises, $tableRequest,$tableError];
}

















//.$this->postCode.
        // $host = $hostPostCode['Postcode'];

        // $data =  json_decode(file_get_contents('https://api.tfl.gov.uk/journey/journeyresults/' . $uni . '/to/' . $host . '?app_key=a59c7dbb0d51419d8d3f9dfbf09bd5cc'), true);

        // $duration = $data['journeys'];

        
        // //$datas = file_get_contents('https://api.tfl.gov.uk/journey/journeyresults/UB68BP/to/W138ER');


        // //echo $datas."<br>";

        // $tableTemp = [];

        // foreach ($duration as $durations) {
        //     $durationns = $durations['duration'];
        //     array_push($tableTemp, $durationns);
        // }

        // $min = min($tableTemp);

        // echo "Shorter travel time : " . $min . " of travel between " . $uni . " and ". $host . " . <br>";

        // echo "<br>";

        // //$request = new Request($uniPostCode['Postcode'], $hostPostCode['Postcode']);
        // array_push($tableAllRequest, array($uni . " and ". $host => $min));