<?php
include('class/DatabaseClass.php');

$username = 'root';
$dbName = 'databasetflapi';
$hostTable = 'host_database';
$uniTable = 'university_database';
$savedTable = 'saved_data';

$connect = new Database($username, '', $dbName);

$universities = $connect->selectAll($uniTable, '*');

$uniJson = json_encode($universities);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styleSearch.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
    <title>Search Home</title>
    <style>
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            /* prevent horizontal scrollbar */
            overflow-x: hidden;
            /* add padding to account for vertical scrollbar */
            padding-right: 20px;
        }
    </style>
</head>

<body>

    <div class="Header">
        <div class="inputSearch">
            <label class="labelInput" for="search"> Enter the name of your school </label>

            <div class="ui-widget">
                <form action="" name="search" method="POST">
                    <input style="overflow: scroll" class="Input" type="text" id="university" name="university" required placeholder="ex : Abbey Manor College">
                    <button class="Button" type="submit"> Search </button>
                </form>
            </div>
            <form action="updatePage.php" method="POST">
                <button class="ButtonUpdate" type="submit"> update </button>
            </form>
        </div>
    </div>

    <?php

    if (isset($_POST['university'])) {
        $value = $_POST['university'];
        $universityPostCode = $connect->selectWhereOr($uniTable, 'Postcode', $value);
        if ($universityPostCode == false) {
            echo '<p class="Error">Invalid university Name, PostCode or Street </p>';
            die();
        }
        if (isset($universityPostCode)) {
            $travel = $connect->selectWhere($savedTable, '*', 'UniPostCode', $universityPostCode['Postcode'], "ASC", "TravelTime");
            if (empty($travel) == true) {
                echo '<p class="Error">This journey has not yet been saved in the database</p>';
                die();
            }
            foreach ($travel as $hostTravel) {
                $hosts = $connect->selectWhere($hostTable, "*", 'Postcode', $hostTravel['HostPostCode'], "ASC", "Postcode");
                foreach ($hosts as $host) {
                    echo $host['Postcode'] . '  |  ';
                    echo $host['Number_of_bedrooms_available_to_students'] . '  |  ';
                    echo $host['Meal_Plan'] . '  |  ';
                    echo $host['Select_the_beds_in_room_1'] . '  |  ';
                    echo $hostTravel['TravelTime'] . '  |  ';
                    echo '<br> <br> <br>';
                }
            }
        }
    }
    ?>
    <script>
        let universities = <?php echo json_encode($uniJson); ?>;
        const obj = JSON.parse(universities);
        console.log(obj);
        $(function() {
            var autocompleteValue = [];

            for (const key in obj) {
                autocompleteValue.push(obj[key].EstablishmentName);
                autocompleteValue.push(obj[key].Street);
                if (obj[key].Postcode != null) {
                    autocompleteValue.push(obj[key].Postcode);
                }
            }

            var test = ['Viksha', 'Richard', 'Thomas'];
            console.log(test);
            console.log(autocompleteValue);
            $("#university").autocomplete({
                source: autocompleteValue
            });
        });
    </script>
</body>
</html>