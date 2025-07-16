<?php
$host = 'bwuk6hfdeaip73fnxjhm-mysql.services.clever-cloud.com';
$port = 3306;
$username = 'unhrmm83fk9v6apd';
$password = 'q1XwTflllYth6mQFrdiG';
$database = 'bwuk6hfdeaip73fnxjhm';

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
