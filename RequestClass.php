<?php

// overpass query
class Request {


    private $postCode;
    private $postCode2;
    private $travelName;

    public function __construct($postCode, $postCode2) {
        $this->postCode = $postCode;
        $this->postCode2 = $postCode2;
        $this->travelName = $postCode.", ".$postCode2;
    }

    //Getter
    public function getPostCode() {
        return $this->postCode;
    }

    public function getPostCode2() {
        return $this->postCode2;
    }

    public function getTravelName() {
        return $this->travelName;
    }

    //Setter
    public function setPostCode($postCode) {
        $this->postCode = $postCode;
    }

    public function setPostCode2($postCode2) {
        $this->postCode2 = $postCode2;
    }



    public function BestJourney() {

        //.$this->postCode.
        ini_set('max_execution_time', 0);
        $data =  json_decode(file_get_contents('https://api.tfl.gov.uk/journey/journeyresults/'.$this->postCode.'/to/'.$this->postCode2.'?app_key=a59c7dbb0d51419d8d3f9dfbf09bd5cc'), true);

        $duration = $data['journeys'];


        //$datas = file_get_contents('https://api.tfl.gov.uk/journey/journeyresults/UB68BP/to/W138ER');


        //echo $datas."<br>";

        $table = [];

        foreach($duration as $durations) {
            $durationns = $durations['duration'];
            array_push($table, $durationns);
            
        }

        $min = min($table);

        echo "Shorter travel time : ".$min." of ".$this->travelName." <br>";

        echo "<br>";

        return $min;
        }
    

}


// $postTest = [
//     new Request("W43XQ", "CR02JA"),
//     new Request("W43XQ","W68DS"), 
//     new Request("W43XQ","W104JW"),
//     new Request("W43XQ","SW166NR")
// ];

// $table = [];



// foreach($postTest as $request) {

//     array_push($table, $request->BestJourney());

// }

// $travelIndex = array_search($bestTravel = min($table), $table);

// echo $postTest[$travelIndex]->getTravelName()." is the best and takes : "."<h1> <div style='color :red'>".$bestTravel." min."."</div> </h1>";

//request on database => zipcode -> easy

//Select top 10 by time -> medium 

?>


