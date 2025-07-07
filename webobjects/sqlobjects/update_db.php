#!/usr/local/bin/php
<?php
include("/var/www/html/api/connect.php");
#include("/var/www/password.php");
/*
$servername = "wlldb";
$username = "timeuser";
$dbname = "winlogonlimiter";

if (empty($password)){
    die ("You must include a password variable in the ../../password.php file.");
}

// Create connection
$retry_max = 10;
$retry_count = 0;
$conn = null;
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
 */
while (1==1) {
    $path = "/tools/dbupdater";
    $original_path = $path;
    $query="SELECT dbconfigvalue FROM dbconfigtable WHERE dbconfigkey = 'version'";
    $response_successful = true;
    $response=array();
    try {
        $result=mysqli_query($conn, $query);
        while($row=mysqli_fetch_assoc($result)){
            $response[]=$row;
        }
    #    echo "Response: \n";
    #    echo var_export($response[0],true);
    } catch (Exception $e){
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        $response_successful = false;
    }
    if ($response_successful){
        $version_number = $response[0]["dbconfigvalue"];
        echo "Version Number: " . $version_number . "\n";
        $scriptfolders = array_diff(scandir($path), array('.', '..', 'update_db.php'));
        $found_version = false;
        # If we have the version number, let's pick the next version number above the number we found.
        foreach ($scriptfolders as $scriptfolder){
            if ($found_version){
                $path = "$path/$scriptfolder";
                break;
            }
            if ($scriptfolder == $version_number){
                $found_version = true;
            }
        }
    } else {
        echo "Unable to obtain version number.  Creating config db.\n";
        $scriptfolders = array_diff(scandir($path), array('.', '..', 'update_db.php'));
        $found_version = true;
        foreach ($scriptfolders as $scriptfolder){
            if ($found_version){
                $path = "$path/$scriptfolder";
                break;
            }
            if ($scriptfolder == $version_number){
                $found_version = true;
            }
        }
    }
    # If path == original_path, then it means the db is already up to date
    if ($path != $original_path){
        $scriptnames = array_diff(scandir($path), array('.', '..'));
        foreach($scriptnames as $scriptname) {
            $statement = file_get_contents("$path/$scriptname");
            mysqli_query($conn, $statement);
        }
    } else {
        echo "DB is already up to date.\n";
        break;
    }
}
mysqli_close($conn);

?>
