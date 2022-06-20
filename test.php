<?php
$data =  json_decode(file_get_contents('https://api.tfl.gov.uk/journey/journeyresults/E1 7RA/to/SW20 OPJ?app_key=a59c7dbb0d51419d8d3f9dfbf09bd5cc'), true);

$journeys = $data['journeys'];
        
            $durations = [];
        
            //var_dump($promiseKey);
            //var_dump($journeys);
    
            foreach ($journeys as $journey) {
                $duration = $journey['duration'];
                array_push($durations, $duration);
            }
        
            $min = min($durations);

            var_dump($min);
?>