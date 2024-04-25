#!/usr/local/bin/php
<?php
include("/var/www/html/api/connect.php");

$path = "/tools/dbupdater";
$original_path = $path;
$query="SELECT dbversion FROM dbconfigtable";
$result=mysqli_query($conn, $query);
$response=array();
while($row=mysqli_fetch_assoc($result)){
    $response[]=$row;
}
if (count($response) == 1){
    $version_number = $response[0];
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
    echo "Unable to obtain version number.  Creating config db.";
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
    echo "DB is already up to date.";
}
?>