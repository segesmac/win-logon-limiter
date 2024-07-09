<?php
use Firebase\JWT\JWT;
require_once(__DIR__ . '/../../../vendor/autoload.php');

// Validate the credentials in the database, or in other data store.
require(__DIR__ . "/../connect.php");
// Connect to database and validate against username and salted password
// Also possibly include isadmin in the token for granting access to limited users
// For the purposes of this application, we'll consider that the credentials are valid.
$username = $_POST["username"];
$password = $_POST["password"];

$is_admin = 0;
$has_valid_credentials = false; # After checking database with username and password

if ($username != ""){
    $return_response = array();
    $stmt = mysqli_stmt_init($conn);
    if (mysqli_stmt_prepare($stmt, 'SELECT * FROM usertable WHERE username=?')){
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        #mysqli_stmt_bind_result($stmt, $response);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
    } else {
        die ("Failed to prepare statement: SELECT * FROM usertimetable WHERE username=$username\n");
    }

    $response=array();
    while($row=mysqli_fetch_assoc($result)){
        $response[]=$row;
    }
    
    if (count($response) == 1 && $username != ""){
        $is_admin = $response[0]['isadmin'];
        $temp_admin_minutes = $response[0]['tempadminminutes'];
        $temp_admin_startdate = $response[0]['tempadminstartdate'];
        $is_tempadmin = 0;
        if (null != $temp_admin_startdate) {
            $temp_admin_datediff = date_diff(date_create($temp_admin_startdate),date_create())->format("%i");
            if ($temp_admin_datediff <= $temp_admin_minutes){
                $is_tempadmin = 1;
            }
        }
        $password_hash = $response[0]['passwordhash'];
        if ($password_hash == NULL){
            $status_message = "User $username doesn't yet have a password. Please create one!";
            $response_message = array(
                'status' => -1,
                'status_message' => $status_message
            );
            $return_response["authenticated"] = $response_message;
            $has_valid_credentials = true;
        } elseif (password_verify($password,$password_hash)){
            $status_message = "Password validated.";
            $response_message = array(
                'status' => -1,
                'status_message' => $status_message
            );
            $return_response["authenticated"] = $response_message;
            $has_valid_credentials = true;
        } else {
            $status_message = "Incorrect password.";
            $response_message = array(
                'status' => -1,
                'status_message' => $status_message
            );
            $return_response["authenticated"] = $response_message;
            $return_response["jwtauthenticated"] = $response_message;
            header('Content-Type: application/json');
            die (json_encode($return_response));
        }
    } elseif (count($response) == 0){
        $status_message = "User $username doesn't exist!";
        $response_message = array(
            'status' => -1,
            'status_message' => $status_message
        );
        header('Content-Type: application/json');
        echo json_encode($response_message);
    } else {
        $response_message = array(
            'status' => 0,
            'status_message' => "Not sure what happened!",
            'payload' => $response
        );
        header('Content-Type: application/json');
        echo json_encode($response_message);
    }
} else {
    die ('Username must be included for authentication to happen.');
}
mysqli_close($conn);



// extract credentials from the request
if ($has_valid_credentials) {
    $secret_Key     = file_get_contents(getenv("WLL_JWT_SECRET_FILE"));
    $date           = new DateTimeImmutable();
    $expire_at      = $date->modify('+6 minutes')->getTimestamp();      // Add 6 minutes
    $domainName     = getenv("WEB_DOMAIN_NAME");

    // Create the token as an array
    $request_data = [
        'iat'  => $date->getTimestamp(),                 // Issued at: time when the token was generated
        'iss'  => $domainName,                           // Issuer
        'nbf'  => $date->getTimestamp(),                 // Not before
        'exp'  => $expire_at,                            // Expire
        'username' => $username,                         // User name
        'is_admin' => $is_admin,                         // Is admin user? 1 or 0
        'is_tempadmin' => $is_tempadmin,                 // Is temp admin user? 1 or 0
        'temp_admin_minutes' => $temp_admin_minutes,     // Number of minutes temp admin lasts
        'temp_admin_startdate' => $temp_admin_startdate, // Start date of temp admin state
    ];

    // Encode the array to a JWT string.
    $return_response["jwtauthenticated"] = array(
        'status' => 1,
        'status_message' => "Successfully created JWT!",
        'is_admin' => $is_admin,
        'is_tempadmin' => $is_tempadmin,
        'payload' => JWT::encode(
            $request_data,      //Data to be encoded in the JWT
            $secret_Key, // The signing key
            'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
        )
    );
    header('Content-Type: application/json');
    echo json_encode($return_response);
}
