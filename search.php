<?php
include('class/DatabaseClass.php');

// Database information
$host = 'localhost';
$username = 'root';
$dbName = 'databasetflapi';
$hostTable = 'host_database';
$uniTable = 'university_database';
$savedTable = 'saved_data';

// Connection to the database using our Database class and the previously set database information
$connect = new Database($host,$username, '', $dbName);

// Getting all the universities informations from the database
$universities = $connect->selectAll($uniTable, '*');

// Encode all the universities information into Json
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
                <!--form to search an university -->
                <form action="" name="search" method="POST">
                    <!--input for the search engine -->
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
    // Verify if there is a value in the input named university
    if (isset($_POST['university'])) {
        // Take the value from the input named university
        $value = $_POST['university'];
        // Select all the universities Postcode where the value from the input university is equal to the universities from the database
        $universityPostCode = $connect->selectWhereOr($uniTable, 'Postcode', $value);

        // If there is an error show an error message
        if ($universityPostCode == false) {
            echo '<p class="Error">Invalid university Name, PostCode or Street </p>';
            die();
        }
        // If there's a value in universityPostCode we can continue
        if (isset($universityPostCode)) {
            // Select all the travels where the value of Postcode from universityPostCode is equal to a travel saved in savedTable
            $travel = $connect->selectWhere($savedTable, '*', 'UniPostCode', $universityPostCode['Postcode'], "ASC", "TravelTime");

            // If the value is empty show an error message
            if (empty($travel) == true) {
                echo '<p class="Error">This journey has not yet been saved in the database</p>';
                die();
            }

            // For each travels that we find in savedTable
            foreach ($travel as $hostTravel) {
                // Select all the information of an host where the Postcode from savedTable match the host Postcode and select the best 10
                $hosts = $connect->selectWhere($hostTable, "*", 'Postcode', $hostTravel['HostPostCode'], "ASC", "Postcode");
                // For each 10 host, show their information
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
        // Saved in the variable universities the Json from the universities of the database
        let universities = <?php echo json_encode($uniJson); ?>;
        // Parse in obj the Json so it will be readable
        const obj = JSON.parse(universities);
        // Function search engine with Jquery
        $(function() {
            // Create a table to saved the value for the search engine
            var autocompleteValue = [];

            // For each object in the Json saved the value in the table autocompleteValue
            for (const key in obj) {
                autocompleteValue.push(obj[key].EstablishmentName);
                autocompleteValue.push(obj[key].Street);
                if (obj[key].Postcode != null) {
                    autocompleteValue.push(obj[key].Postcode);
                }
            }
            // Make the search engine use the table autocompleteTable for the search and use it in the input with the id university
            $("#university").autocomplete({
                source: autocompleteValue
            });
        });
    </script>
</body>
</html>