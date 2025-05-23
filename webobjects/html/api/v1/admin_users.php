<?php
# Yes, I'm aware this is set up so that anyone can change the time limits, etc.  
# If my children learn how to "hack" this system,
# then I would call that a win! I'll set up authentication later
if (null == @$is_test){
	require(__DIR__ . '/../jwt_auth.php');
	if ($token->is_tempadmin == 0 && $token->is_admin == 0){
		header('HTTP/1.1 401 Unauthorized');
		echo 'Admin access only';
		exit;
	}
}
# require(__DIR__ . '/../jwt_auth.php'); # Commenting this out because the tests can't generate jwt tokens yet
function modify_user( $username = ""
    , $timelimit = null
	, $bonusminutesadd = null
	, $loginstatus = null
	, $bonusminutes = null
	, $timeleftminutes = null
	, $timeleftminutesadd = null
	, $bonuscounters = null
){
	require(__DIR__ . "/../connect.php");
	$data = json_decode(file_get_contents('php://input'), true);
	if (!empty($data["username"])) {
		$username = strval($data["username"]);
	}
	if (!empty($data["timelimit"])) {
    	$timelimit = $data["timelimit"];
	}
	if (!empty($data["bonusminutesadd"])) {
		$bonusminutesadd = $data["bonusminutesadd"];
	}
	if (!empty($data["loginstatus"])) {
		$loginstatus = $data["loginstatus"];
	}
	if (!empty($data["bonusminutes"])) {
		$bonusminutes = $data["bonusminutes"];
	}
	if (!empty($data["timeleftminutes"])) {
		$timeleftminutes = $data["timeleftminutes"];
	}
	if (!empty($data["timeleftminutesadd"])) {
		$timeleftminutesadd = $data["timeleftminutesadd"];
	}
	if (!empty($data["bonuscounters"])) {
		$bonuscounters = $data["bonuscounters"];
	}
	$return_response = array();


    # Return status if username is null
	if (empty($username)){
		$response = array(
			'status' => 0,
			'status_message' => "You must include a username!"
		);
		$return_response = $response;
	}

	# Set bonus minutes to some value
	if (!empty($username) && isset($bonusminutes)){
		$stmt = mysqli_stmt_init($conn);
		if (mysqli_stmt_prepare($stmt, "UPDATE usertimetable SET lastrowupdate = NOW(), bonustimeminutes = ? WHERE username = ?")){
			mysqli_stmt_bind_param($stmt, "ds", $bonusminutes, $username);
			mysqli_stmt_execute($stmt);
			$affected_rows = mysqli_stmt_affected_rows($stmt);
			mysqli_stmt_close($stmt);
			if ($affected_rows == 0){
				$response = array(
					'status' => 0,
					'status_message' => "User $username doesn't exist!"
				);
			} else {
				$response = array(
					'status' => $bonusminutes,
					'status_message' => "Set bonusminutes to $bonusminutes for $username successfully!"
				);
			}
			$return_response["bonusminutes"] = $response;
		}
	}

	# Set bonus counters to some value
	if (!empty($username) && isset($bonuscounters)){
		$stmt = mysqli_stmt_init($conn);
		if (mysqli_stmt_prepare($stmt, "UPDATE usertimetable SET lastrowupdate = NOW(), bonuscounters = ? WHERE username = ?")){
			mysqli_stmt_bind_param($stmt, "ds", $bonuscounters, $username);
			mysqli_stmt_execute($stmt);
			$affected_rows = mysqli_stmt_affected_rows($stmt);
			mysqli_stmt_close($stmt);
			if ($affected_rows == 0){
				$response = array(
					'status' => 0,
					'status_message' => "User $username doesn't exist!"
				);
			} else {
				$response = array(
					'status' => $bonuscounters,
					'status_message' => "Set bonuscounters to $bonuscounters for $username successfully!"
				);
			}
			$return_response["bonuscounters"] = $response;
		}
	}

	# Add minutes to the bonus pool
	if (!empty($username) && isset($bonusminutesadd)){
		$stmt = mysqli_stmt_init($conn);
		if (mysqli_stmt_prepare($stmt, "UPDATE usertimetable SET lastrowupdate = NOW(), bonustimeminutes = bonustimeminutes + ? WHERE username = ?")){
			mysqli_stmt_bind_param($stmt, "ds", $bonusminutesadd, $username);
			mysqli_stmt_execute($stmt);
			$affected_rows = mysqli_stmt_affected_rows($stmt);
			mysqli_stmt_close($stmt);
			if ($affected_rows == 0){
				$response = array(
					'status' => 0,
					'status_message' => "User $username doesn't exist!"
				);
			} else {
				$response = array(
					'status' => $bonusminutesadd,
					'status_message' => "Added $bonusminutesadd bonus minute(s) to $username successfully!"
				);
			}
			$return_response["bonusminutesadd"] = $response;
		}
	}
	# Set regular minutes to some value
	if (!empty($username) && isset($timeleftminutes)){
		$stmt = mysqli_stmt_init($conn);
		if (mysqli_stmt_prepare($stmt, "UPDATE usertimetable SET lastrowupdate = NOW(), timeleftminutes = ? WHERE username = ?")){
			mysqli_stmt_bind_param($stmt, "ds", $timeleftminutes, $username);
			mysqli_stmt_execute($stmt);
			$affected_rows = mysqli_stmt_affected_rows($stmt);
			mysqli_stmt_close($stmt);
			if ($affected_rows == 0){
				$response = array(
					'status' => 0,
					'status_message' => "User $username doesn't exist!"
				);
			} else {
				$response = array(
					'status' => $timeleftminutes,
					'status_message' => "Set timeleftminutes to $timeleftminutes for $username successfully!"
				);
			}
			$return_response["timeleftminutes"] = $response;
		}
	}
	# Add minutes to the regular pool
	if (!empty($username) && isset($timeleftminutesadd)){
		$stmt = mysqli_stmt_init($conn);
		if (mysqli_stmt_prepare($stmt, "UPDATE usertimetable SET lastrowupdate = NOW(), timeleftminutes = timeleftminutes + ? WHERE username = ?")){
			mysqli_stmt_bind_param($stmt, "ds", $timeleftminutesadd, $username);
			mysqli_stmt_execute($stmt);
			$affected_rows = mysqli_stmt_affected_rows($stmt);
			mysqli_stmt_close($stmt);
			if ($affected_rows == 0){
				$response = array(
					'status' => 0,
					'status_message' => "User $username doesn't exist!"
				);
			} else {
				$response = array(
					'status' => $timeleftminutesadd,
					'status_message' => "Added $timeleftminutesadd timeleft minute(s) to $username successfully!"
				);
			}
			$return_response["timeleftminutesadd"] = $response;
		}
	}
	# Update the time limit to some value
	if (!empty($username) && isset($timelimit)){
		$stmt = mysqli_stmt_init($conn);
		if (mysqli_stmt_prepare($stmt, "UPDATE usertimetable SET lastrowupdate = NOW(), timelimitminutes = ? WHERE username = ?")){
			mysqli_stmt_bind_param($stmt, "ds", $timelimit, $username);
			mysqli_stmt_execute($stmt);
			$affected_rows = mysqli_stmt_affected_rows($stmt);
			mysqli_stmt_close($stmt);
			if ($affected_rows == 0){
				$response = array(
					'status' => 0,
					'status_message' => "User $username doesn't exist!"
				);
			} else {
				$response = array(
					'status' => $timelimit,
					'status_message' => "Set timelimitminutes to $timelimit for $username successfully!"
				);
			}
			$return_response["timelimit"] = $response;
		}
	}
	header('Content-Type: application/json');
	echo json_encode($return_response);
	mysqli_close($conn);
}

function delete_user($username = "") {
	require(__DIR__ . "/../connect.php");
	$data = json_decode(file_get_contents('php://input'), true);
	if (!empty($data["username"])) {
		$username = strval($data["username"]);
	}
	if (!empty($username)){
		$stmt = mysqli_stmt_init($conn);
		if (mysqli_stmt_prepare($stmt, "DELETE FROM usertimetable WHERE username = ?")){
			mysqli_stmt_bind_param($stmt, "s", $username);
			mysqli_stmt_execute($stmt);
			$affected_rows = mysqli_stmt_affected_rows($stmt);
			mysqli_stmt_close($stmt);
			if ($affected_rows == 0){
				$response = array(
					'status' => 0,
					'status_message' => "User $username doesn't exist!"
				);
			} else {
				$response = array(
					'status' => 1,
					'status_message' => "Deleted $username successfully!"
				);
			}
		} else {
			$response = array(
				'status' => 0,
				'status_message' =>  "Error: \n" . mysqli_error($conn)
			);
		}
	}  else {
		$response = array(
			'status' => 0,
			'status_message' => "You must include a username!"
		);
	}
	header('Content-Type: application/json');
	echo json_encode($response);
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
		case 'POST':
			if (isset($_POST["username"])){
				$username=strval($_POST["username"]);
			}
			if (isset($_POST["timelimit"])){
                $timelimit=strval($_POST["timelimit"]);
			}
			if (isset($_POST["bonusminutesadd"])){
                $bonusminutesadd=strval($_POST["bonusminutesadd"]);
			}
			if (isset($_POST["loginstatus"])){
				$loginstatus = strval($_POST["loginstatus"]);
			}
			if (isset($_POST["bonusminutes"])){
				$bonusminutes = strval($_POST["bonusminutes"]);
			}
			if (isset($_POST["timeleftminutes"])){
				$timeleftminutes = strval($_POST["timeleftminutes"]);
			}
			if (isset($_POST["timeleftminutesadd"])){
				$timeleftminutesadd = strval($_POST["timeleftminutesadd"]);
			}
			if (isset($_POST["bonuscounters"])){
				$bonuscounters = strval($_POST["bonuscounters"]);
			}
			modify_user(@$username, @$timelimit, @$bonusminutesadd, @$loginstatus, @$bonusminutes, @$timeleftminutes, @$timeleftminutesadd, @$bonuscounters);
			
			break;
		case 'DELETE':
			if (!empty($_GET["username"])){
				$username=strval($_GET["username"]);
				delete_user($username);
			} else {
				delete_user();
			}
			break;
		default:
			// Invalid Request Method
			header("HTTP/1.0 405 Method Not Allowed");
			mysqli_close($conn);
			break;
	}
}

