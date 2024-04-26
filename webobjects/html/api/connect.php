<?php

include(__DIR__ . "/../../password.php");

$servername = "wlldb";
$username = "timeuser";
$dbname = "winlogonlimiter";

if (empty($password)){
	die ("You must include a password variable in the ../../password.php file.");
}

// Create connection
$retry_max = 10;
$retry_count = 0;
while ($retry_count < $retry_max){
    try {
        $conn = mysqli_connect($servername, $username, $password, $dbname);
    } catch (Exception $e) {
        echo "Caught exception on try $retry_count: ",  $e->getMessage(), "\n";
        if ($conn){
            if (mysqli_ping($conn)) {
                printf ("Our connection is ok!\n");
                break;
            } else {
                printf ("Error: %s\n", mysqli_error($conn));
            }
	}
        sleep(5);
	$retry_count++;
    }
}
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
