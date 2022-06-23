<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Search Home</title>
</head>
<body>
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

    <script>
        let universities = <?php echo json_encode($uniJson); ?>;
        document.write(arr[1]);

    </script>
    
    <h1> Search your best home </h1>

    <label for="search"> Enter the name of your school </label> 

    <form action="" name="search" method="POST">
        <input type="text" name="university" required placeholder="ex : Abbey Manor College">
        <button type="submit"> update </button>   
    </form>

    <?php 

    if (isset($_POST['university'])) {
        $value = $_POST['university'];
        $universityPostCode = $connect->selectWhereOr($uniTable, 'Postcode', $value);
        if (isset($universityPostCode)) {
                echo $universityPostCode['Postcode'];
                $travel = $connect->selectWhere($savedTable, '*', 'UniPostCode', $universityPostCode['Postcode'], "TravelTime", "ASC");
                // foreach ($travel as $host) {
                //     $test = $connect->selectWhere($uniTable, "*", '')
                // }
        }
    } 
    ?>

    <form action="updatePage.php" method="POST">
        <button type="submit"> update </button>  
    </form>

    
    
</body>
</html>