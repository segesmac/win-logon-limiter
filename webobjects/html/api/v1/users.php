<?php
# Nothing in this file should require any authentication (except the update timelimit function...)
function get_users($username = ""){
	require(__DIR__ . "/../connect.php");
	$get_all=false;
	if ($username != ""){
		$stmt = mysqli_stmt_init($conn);
		if (mysqli_stmt_prepare($stmt, 'SELECT * FROM usertimetable WHERE username=?')){
			mysqli_stmt_bind_param($stmt, "s", $username);
			mysqli_stmt_execute($stmt);
			#mysqli_stmt_bind_result($stmt, $response);
			$result = mysqli_stmt_get_result($stmt);
			mysqli_stmt_close($stmt);
		} else {
			die ("Failed to prepare statement: SELECT * FROM usertimetable WHERE username=$username\n");
		}
	} else {
		$get_all = true;
		$query="SELECT * FROM usertimetable ORDER BY userorder";
		$result=mysqli_query($conn, $query);
	}
	$response=array();
	while($row=mysqli_fetch_assoc($result)){
		$response[]=$row;
	}
	header('Content-Type: application/json');
	if (count($response) == 1 && $username != ""){
		$return_response = array(
			'status' => 1,
			'status_message' => "$username found successfully!",
			'payload' => $response[0]
        );
		echo json_encode($return_response);
		
	} elseif (count($response) == 0){
		$status_message = "User $username doesn't exist!";
		if ($get_all){
			$status_message = "No users exist!";
		}
		$return_response = array(
			'status' => -1,
			'status_message' => $status_message
		);
		echo json_encode($return_response);
	} else {
		$return_response = array(
			'status' => 1,
			'status_message' => "Found users successfully!",
			'payload' => $response
		);
		echo json_encode($return_response);
	}
	mysqli_close($conn);
}

function insert_user( $username = "" # jdoe
    , $timelimit = -1   # -1 or 60
){
	require(__DIR__ . "/../connect.php");
	$data = json_decode(file_get_contents('php://input'), true);
	if (!empty($data["username"])){
	    $username = strval($data["username"]);
	}
	if (!empty($data["timelimit"])){
		$timelimit = doubleval($data["timelimit"]);
	}
	$stmt = mysqli_stmt_init($conn);
	if (mysqli_stmt_prepare($stmt, 
	    "INSERT INTO usertimetable (
			username
			, isloggedon
			, lastlogon
			, lastheartbeat
			, timelimitminutes
			, timeleftminutes
			, bonustimeminutes
			, userorder
		) 
		SELECT * FROM (
			SELECT ? AS username
				, 0 AS isloggedon
				, NULL AS lastlogon
				, NULL AS lastheartbeat
				, ? AS timelimitminutes
				, ? AS timeleftminutes
				, 0 AS bonustimeminutes
				, 0 AS userorder
			) AS tmp 
		WHERE NOT EXISTS (
			SELECT username FROM usertimetable WHERE username = ?
			) 
		LIMIT 1;
		")){
		mysqli_stmt_bind_param($stmt, "siis", $username, $timelimit, $timelimit, $username);
		mysqli_stmt_execute($stmt);
		#printf("%d Row inserted.\n", mysqli_stmt_affected_rows($stmt));
		$affected_rows = mysqli_stmt_affected_rows($stmt);
		
		if ($affected_rows == 0){
			$response = array(
				'status' => 1,
				'status_message' => "User $username already exists."
			);
		} else {
			$response = array(
				'status' => 1,
				'status_message' => "User $username inserted successfully!"
			);
		}
		mysqli_stmt_close($stmt);
		$stmt = mysqli_stmt_init($conn);
		if (mysqli_stmt_prepare($stmt,
		"INSERT INTO `usertable` (`usertimetableid`,`username`) 
		SELECT * FROM (
			SELECT `usertimetableid`, `username` 
			FROM `usertimetable` WHERE username = ?
		) AS tmp
		WHERE NOT EXISTS (
			SELECT username FROM usertable WHERE username = ?
			) 
		LIMIT 1;
		")) {
			mysqli_stmt_bind_param($stmt, "ss", $username, $username);
			mysqli_stmt_execute($stmt);
			$affected_rows = mysqli_stmt_affected_rows($stmt);
			if ($affected_rows == 0){
				$response['status_message_usertable'] = "User $username already exists in usertable.";
				$response['status_usertable'] = 1;
			} else {
				$response['status_usertable'] = 1;
				$response['status_message_usertable'] = "User $username inserted into usertable successfully!";
			}
			mysqli_stmt_close($stmt);
		} else {
			$response['status_usertable'] = 0;
			$response['status_message_usertable'] =  "Error: \n<br />\n" . mysqli_error($conn);
		}
	} else {
		$response = array(
			'status' => 0,
			'status_message' =>  "Error: \n<br />\n" . mysqli_error($conn)
		);
	}
	header('Content-Type: application/json');
	echo json_encode($response);
	mysqli_close($conn);
}

function update_user( $username = ""
	, $loginstatus = null
){
	require(__DIR__ . "/../connect.php");
	$data = json_decode(file_get_contents('php://input'), true);
	if (!empty($data["username"])) {
		$username = strval($data["username"]);
	}
	if (!empty($data["loginstatus"])) {
		$loginstatus = $data["loginstatus"];
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
	# Update login status
	if (isset($loginstatus) && $username != ""){
		$stmt = mysqli_stmt_init($conn);
		if (mysqli_stmt_prepare($stmt, 
		    "UPDATE usertimetable 
			SET 
			    lastrowupdate = NOW()
				, lastheartbeat = CASE 
				    WHEN ? > 0 
					THEN NOW() 
					ELSE lastheartbeat 
					END
				, lastlogon = CASE 
				    WHEN 1=1
					    AND isloggedon = 0 
						AND ? > 0 
					THEN NOW() 
					ELSE lastlogon 
					END
				, isloggedon = ? 
			WHERE username = ?;"
		)){
			mysqli_stmt_bind_param($stmt, "iiis", $loginstatus, $loginstatus, $loginstatus, $username);
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
					'status_message' => "User $username updated successfully!"
				);
			}
			$return_response["loginstatus"] = $response;
		}
	}
	header('Content-Type: application/json');
	echo json_encode($return_response);
	mysqli_close($conn);
}

$request_method=$_SERVER["REQUEST_METHOD"];
if (isset($request_method)){
	#echo "REQUEST_METHOD SET: $request_method";
	switch($request_method){
		case 'GET':
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
			break;
		case 'PUT':
			if (!empty($_GET["username"])){
				$username=strval($_GET["username"]);
				update_user($username);
			} else {
				update_user();
			}
			break;
		default:
			// Invalid Request Method
			header("HTTP/1.0 405 Method Not Allowed");
			mysqli_close($conn);
			break;
	}
}

