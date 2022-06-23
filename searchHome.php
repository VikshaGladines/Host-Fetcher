<?php 
session_start();
$value = $_POST['university'];

$_SESSION['university'] = $value;

if (isset($value)) {
    header('Location: search.php?');
}
?>