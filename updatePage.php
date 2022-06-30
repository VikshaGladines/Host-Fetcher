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
    <link rel="stylesheet" href="css/searchStyle.css">
    <title>Update Page</title>
</head>

<body>

    <div class="Header">
        <label class="labelInput" for="update">Update Page</label>
    </div>


    <div class="inputSearch">
        <p>
        <form action="scripts/updateBase.php" name="update">
            <p>
                <label for="enteredPostCode" class="labelInputPostCode">Post Code</label>
                <br>
                <input type="text" class="Input" name="enteredPostCode" id="enteredPostCode">
            </p>
            <p>
                <label for="hostRadio" class="labelInputPostCode container">Host
                <input type="radio" name="placeType" id="hostRadio" value="hostRadio" required>
                <span class="checkmark"></span>
                </label>
    
                <label for="uniRadio" class="labelInputPostCode container">University
                <input type="radio" name="placeType" id="uniRadio" value="uniRadio">
                <span class="checkmark"></span>
                </label>
            </p>
            <p>
                <input type="submit" name='updateButton' value="Update" class="ButtonUpdatePage">
                <input type="submit" name='deleteButton' value="Delete" class="ButtonUpdatePage">
            </p>
        </form>
        </p>
    </div>

    <p class="navigationButton">
        <a href="scripts/updateBase.php">
            <button class="ButtonUpdatePage">Update All</button>
        </a>
        <a href="search.php">
            <button class="ButtonUpdatePage">Back To Search</button>
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