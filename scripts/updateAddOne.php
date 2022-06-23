<?php
include('../class/DatabaseClass.php');

ini_set('max_execution_time', 0);

$enteredPostCode = $_GET['enteredPostCode'];

$username = 'root';
$dbName = 'databasetflapi';
$hostTable = 'host_database';
$uniTable = 'university_database';
$savedTable = 'saved_data';

$connect = new Database($username, '', $dbName);

var_dump($enteredPostCode);

$results = $connect->selectWhere($uniTable, '*', 'Postcode', $enteredPostCode, 'ASC', 'Postcode');

if (empty($results)) {
    echo 'isEmpty';
} else {
    echo 'not Empty';
}

var_dump($results);
