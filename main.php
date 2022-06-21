<?php
include('RequestClass.php');
include('DatabaseClass.php');
require 'vendor/autoload.php';
use GuzzleHttp\Promise;
use GuzzleHttp\Client;

ini_set('max_execution_time', 0);

$connect = new Database('root', '');

$postCodeHost = $connect->connect('databasetflapi', 'host_database');
$postCodeUni = $connect->connect('databasetflapi', 'university_database');

$tableAllRequest = [];

$client = new Client(['base_uri' => 'https://api.tfl.gov.uk/']);

$promisesTbl = [];

$appKey = ['a59c7dbb0d51419d8d3f9dfbf09bd5cc','a2ab67e908f44a0b8836e7f69a3557c9'];

$keyIndex = 0;

$requestSent = 0;

$errors=0;


foreach ($postCodeUni as $uniPostCode) {

    $uni = ltrim(utf8_encode($uniPostCode['Postcode']));
    //var_dump($uni);

    foreach ($postCodeHost as $hostPostCode) {

        $host = ltrim(utf8_encode($hostPostCode['Postcode']));
        //var_dump($host);
        
        if ($host != 'SW20 OPJ') {

            $promisesTbl[$uni . " and ". $host] = $client->getAsync('/journey/journeyresults/' . $uni . '/to/' . $host . '?app_key=a59c7dbb0d51419d8d3f9dfbf09bd5cc');
            $requestSent++;
        }
        
        // var_dump($requestSent);
        if ($requestSent == 499) {
            // $keyIndex++;
            
            $promisesResults = processTravel($promisesTbl);
            $promisesTbl = $promisesResults[0];
            $tableAllRequest = array_merge($tableAllRequest, $promisesResults[1]);
            $errors=$promisesResults[2];
            
            sleep(60); 
            $requestSent = 0;
        }
        //usleep(100000);
        
       
    }
}

$promisesResults = processTravel($promisesTbl);
$promisesTbl = $promisesResults[0];
$tableAllRequest = array_merge($tableAllRequest, $promisesResults[1]);
$errors=$promisesResults[2];
var_dump($errors);
var_dump($tableAllRequest);

function processTravel($promises) {
    //var_dump($promises);
    $results = Promise\settle($promises)->wait();
    $tableRequest = [];
    $tableError=0;
            
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