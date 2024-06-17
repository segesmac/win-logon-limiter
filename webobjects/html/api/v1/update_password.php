<?php
//declare(strict_types=1);

require_once(__DIR__ . '/../curl_connect.php');
require(__DIR__ . '/../jwt_auth.php');

# This update password page will update the new usertable table
# If the user doesn't have a password set in the usertable table, he will not be prompted
# for authentication
# Should probably send through authentication in javascript client-side after password is updated
# To update an existing password, old password must be entered and validated before new password 
# When password is updated here, the sql tables with their salt and hashes will be updated
# At the same time, an API call to JumpCloud will update the password for their user (local user)
# JumpCloud API key is stored in BitWarden
# This curl command will get the user one is looking for:
    /*
    # API_URL=https://console.jumpcloud.com/api
    curl --request GET \
        --url ${API_URL}'/systemusers?limit=10&skip=0&sort=&fields=&filter=username:$eq:loginguest' \
        --header "x-api-key: $API_KEY"
    */
# This curl command will update the user's password:
    /*
    # UserID is received from the above command
    # Validate that total count is 1 {"totalCount" : 1} and retrieve your id: {"results": [{"id"}]}
    curl -X PUT ${API_URL}'/systemusers/{UserID}?fullValidationDetails=password' \
        -H 'Accept: application/json' \
        -H 'Content-Type: application/json' \
        -H 'x-api-key: {API_KEY}' \
        -d '{"password":"{PASSWORD_HERE}"}'
    */
function update_password($jwt_username, $jwt_isadmin, $username = "", $password = ""){
    // Validate the credentials in the database, or in other data store.
    require(__DIR__ . "/../connect.php");
    $return_response = array();
    $api_url = 'https://console.jumpcloud.com/api';

    $data = json_decode(file_get_contents('php://input'), true);
	if (!empty($data["username"]) && $username == "") {
		$username = strval($data["username"]);
	}
	if (!empty($data["password"]) && $password == "") {
    	$password = strval($data["password"]);
	}

	header('Content-Type: application/json');
	if (($username != "" && $jwt_username == $username) || ($username != "" && $jwt_isadmin == 1)) {
        # Confirmed user exists - now attempting to update password using ARGON2ID hash
        if ($password != ""){
            # hashing password using ARGON2ID algorithm
            # to verify a password against this algorithm, use:
                # password_verify($password,$passwordhash);
            $passwordhash = password_hash($password, PASSWORD_ARGON2ID);
            $stmt = mysqli_stmt_init($conn);
            if (mysqli_stmt_prepare($stmt,"UPDATE usertable SET passwordhash = ? WHERE username = ?")){
                mysqli_stmt_bind_param($stmt,"ss", $passwordhash, $username);
                mysqli_stmt_execute($stmt);
                $affected_rows = mysqli_stmt_affected_rows($stmt);
                mysqli_stmt_close($stmt);
                if ($affected_rows == 0){
                    $response = array(
                        'status' => 0,
                        'status_message' => "User $username doesn't exist!"
                    );
                } else {
                    # Make call to JumpCloud to update password there, too
                    $user_obj_json = curl_api('GET', $api_url.'/systemusers?limit=10&skip=0&sort=&fields=&filter=username:$eq:'.$username);
                    $user_obj = json_decode($user_obj_json, true);
                    if ($user_obj['totalCount'] == 1){
                        $user_id = $usr_obj['results'][0]['id'];
                        $curl_data = '{"password": "'.$password.'"}';
                        $jumpcloud_response = curl_api('PUT', $api_url.'/systemusers/'.$user_id.'?fullValidationDetails=password', $curl_data);
                    } else {
                        $jumpcloud_response = array(
                            'status'=> 0,
                            'status_message'=> "Unable to set jumpcloud user password - username count returned a result of " . $user_obj['totalCount'],
                        );
                    }
                    
                    $response = array(
                        'status' => $affected_rows,
                        'status_message' => "Set new password for $username successfully!"
                    );
                }
                $return_response["password_set"] = $response;
                $return_response["jumpcloud_pw_set"] = $jumpcloud_response;
            } else {
                mysqli_close($conn);
                die ("Failed to update password.");
            }
        } else {
            mysqli_close($conn);
            die ("Password is required - cannot update password to blank.");
        }
	} else {
        mysqli_close($conn);
        if ($username == ""){
            die ("Username is required - cannot update password for all users.");
        }
        if ($username != $jwt_username && $jwt_isadmin == 0){
            die ("Only admins can change passwords for other users. You '".$jwt_username."' cannot change the password for '".$username."'");
        }
        die ("I don't know what happened...");
    }
    echo json_encode($return_response);
	mysqli_close($conn);
}

$request_method=$_SERVER["REQUEST_METHOD"];
if (isset($request_method)){
	#echo "REQUEST_METHOD SET: $request_method";
	switch($request_method){
		/*case 'GET':
			// retrive users
			if(!empty($_GET["username"]))
			{
				$username=strval($_GET["username"]);
				get_users($username);
			}
			else
			{
				get_users();
			}
			break;
		case 'POST':
			insert_user();
			break;*/
		case 'PUT':
			if (!empty($_GET["username"])){
				$username=strval($_GET["username"]);
				update_password($token->username, $token->is_admin, $username); # $token comes from ../jwt_auth.php
			}
			break;
		default:
			// Invalid Request Method
			header("HTTP/1.0 405 Method Not Allowed");
			break;
	}
}