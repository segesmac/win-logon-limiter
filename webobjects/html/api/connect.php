<?php

include(__DIR__ . "/../../password.php");

$db_servername = "wlldb";
$db_username = "timeuser";
$db_name = "winlogonlimiter";

if (empty($password)){
    die ("You must include a password variable in the ../../password.php file.");
}

// Create connection
$conn = mysqli_connect($db_servername, $db_username, $password, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

