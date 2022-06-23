<?php

session_start();

$error = null;

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styleSearch.css">
    <title>Update Page</title>
</head>

<body>

    <h1>Update Page</h1>

    <p>
        <a href="scripts/updateBase.php">
            <button class="ButtonUpdate">Update All</button>
        </a>
    </p>

    <p>
    <form action="scripts/updateBase.php">
        <input type="text" name="enteredPostCode" id="enteredPostCode">
        <input type="submit" value="Update">
        <p>
            <label for="hostRadio">Host
                <input type="radio" name="placeType" id="hostRadio" value="hostRadio" required>
            </label>
        </p>
        <p>
            <label for="uniRadio">University
                <input type="radio" name="placeType" id="uniRadio" value="uniRadio">
            </label>
        </p>
    </form>
    </p>

    <p>
        <a href="search.php">
            <button class="Button">Back To Search</button>
        </a>
    </p>

    <p>
        
    </p>

</body>

</html>