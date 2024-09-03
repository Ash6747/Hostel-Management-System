<?php
$dbuser="root";
$dbpass="";
$host="localhost";
$db="hostel";
// $mysqli =new mysqli($host,$dbuser, $dbpass, $db);

try {
    $mysqli = new mysqli($host,$dbuser, $dbpass, $db);

    if ($mysqli->connect_error) {
        throw new Exception('Could not connect to the database.');
    }
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>