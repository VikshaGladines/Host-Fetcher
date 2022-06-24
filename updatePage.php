<?php

session_start();

$error = null;
$doneText = null;

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
}

if (isset($_SESSION['done'])) {
    $doneText = $_SESSION['done'];
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
    <form action="scripts/updateBase.php">
        <p>
            <input type="text" name="enteredPostCode" id="enteredPostCode">
        </p>
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
        <p>
            <input type="submit" name='updateButton' value="Update">
            <input type="submit" name='deleteButton' value="Delete">
        </p>
    </form>
    </p>

    <p>
        <a href="scripts/updateBase.php">
            <button class="ButtonUpdate">Update All</button>
        </a>
    </p>

    <p>
        <a href="search.php">
            <button class="Button">Back To Search</button>
        </a>
    </p>

    <?php

    if ($error != null) {
        echo "<script type='text/javascript'>alert('$error');</script>";;
        session_destroy();
    }

    if ($doneText != null) {
        echo "<script type='text/javascript'>alert('$doneText');</script>";;
        session_destroy();
    }

    ?>


</body>

</html>