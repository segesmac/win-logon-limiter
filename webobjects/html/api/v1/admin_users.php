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
	, $userorder = null
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
	if (!empty($data["userorder"])) {
		$userorder = $data["userorder"];
	}
	$return_response = array();
	$response = array();


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
		// Begin a transaction
                mysqli_begin_transaction($conn);
                try {
			$stmt = mysqli_stmt_init($conn);
			if (!mysqli_stmt_prepare($stmt, "UPDATE usertimetable SET lastrowupdate = NOW(), bonustimeminutes = ? WHERE username = ?")){
				throw new Exception("Error preparing update statement: " . mysqli_error($conn));
			}
			mysqli_stmt_bind_param($stmt, "ds", $bonusminutes, $username);
			mysqli_stmt_execute($stmt);
			$affected_rows = mysqli_stmt_affected_rows($stmt);
			mysqli_stmt_close($stmt);
			$logmessage = "";
			if ($affected_rows == 0){
				$logmessage = "User $username doesn't exist!";
				$response = array(
					'status' => 0,
					'status_message' => $logmessage
				);
			} else {
				$logmessage = "Set bonusminutes to $bonusminutes for $username successfully!";
				$response = array(
					'status' => $bonusminutes,
					'status_message' => $logmessage
				);
			}
			$log_stmt = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($log_stmt, "INSERT INTO logtable (usertableid, logmessage) VALUES ((SELECT usertableid FROM usertable WHERE username = ?), ?)")) {
                                throw new Exception("Error preparing log statement: " . mysqli_error($conn));
                        }
                        mysqli_stmt_bind_param($log_stmt, "ss", $token->username, $logmessage);
                        mysqli_stmt_execute($log_stmt);
                        if (mysqli_stmt_affected_rows($log_stmt) === 0) {
                                // This could mean the username wasn't found in the 'user' table,
                                // or the log insert genuinely failed for another reason.
                                throw new Exception("Failed to insert log entry (possibly user " . $token->username . " not found).");
                        }
                        mysqli_stmt_close($log_stmt);

                        // If all operations succeeded, commit the transaction
                        mysqli_commit($conn);
		} catch (Exception $e){
                        // ERROR - rollback transaction
                        mysqli_rollback($conn);
                        $response = array(
                                'status' => -1,
                                'status_message' => "Transaction failed: " . $e->getMessage();
                        );

                }
                $return_response["bonusminutes"] = $response;
	}

	# Set bonus counters to some value
	if (!empty($username) && isset($bonuscounters)){
		// Begin a transaction
                mysqli_begin_transaction($conn);
                try {
			$stmt = mysqli_stmt_init($conn);
			if (!mysqli_stmt_prepare($stmt, "UPDATE usertimetable SET lastrowupdate = NOW(), bonuscounters = ? WHERE username = ?")){
				throw new Exception("Error preparing update statement: " . mysqli_error($conn));
			}
			mysqli_stmt_bind_param($stmt, "ds", $bonuscounters, $username);
			mysqli_stmt_execute($stmt);
			$affected_rows = mysqli_stmt_affected_rows($stmt);
			mysqli_stmt_close($stmt);
			$logmessage = "";
			if ($affected_rows == 0){
				$logmessage = "User $username doesn't exist!";
				$response = array(
					'status' => 0,
					'status_message' => $logmessage
				);
			} else {
				$logmessage = "Set bonuscounters to $bonuscounters for $username successfully!";
				$response = array(
					'status' => $bonuscounters,
					'status_message' => $logmessage
				);
			}
			$log_stmt = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($log_stmt, "INSERT INTO logtable (usertableid, logmessage) VALUES ((SELECT usertableid FROM usertable WHERE username = ?), ?)")) {
                                throw new Exception("Error preparing log statement: " . mysqli_error($conn));
                        }
                        mysqli_stmt_bind_param($log_stmt, "ss", $token->username, $logmessage);
                        mysqli_stmt_execute($log_stmt);
                        if (mysqli_stmt_affected_rows($log_stmt) === 0) {
                                // This could mean the username wasn't found in the 'user' table,
                                // or the log insert genuinely failed for another reason.
                                throw new Exception("Failed to insert log entry (possibly user " . $token->username . " not found).");
                        }
                        mysqli_stmt_close($log_stmt);

                        // If all operations succeeded, commit the transaction
                        mysqli_commit($conn);
		} catch (Exception $e){
                        // ERROR - rollback transaction
                        mysqli_rollback($conn);
                        $response = array(
                                'status' => -1,
                                'status_message' => "Transaction failed: " . $e->getMessage();
                        );

                }
                $return_response["bonuscounters"] = $response;
	}

	# Add minutes to the bonus pool
	if (!empty($username) && isset($bonusminutesadd)){
		// Begin a transaction
                mysqli_begin_transaction($conn);
                try {
			$stmt = mysqli_stmt_init($conn);
			if (!mysqli_stmt_prepare($stmt, "UPDATE usertimetable SET lastrowupdate = NOW(), bonustimeminutes = bonustimeminutes + ? WHERE username = ?")){
				throw new Exception("Error preparing update statement: " . mysqli_error($conn));
			}
			mysqli_stmt_bind_param($stmt, "ds", $bonusminutesadd, $username);
			mysqli_stmt_execute($stmt);
			$affected_rows = mysqli_stmt_affected_rows($stmt);
			mysqli_stmt_close($stmt);
			$logmessage = "";
			if ($affected_rows == 0){
				$logmessage = "User $username doesn't exist!";
				$response = array(
					'status' => 0,
					'status_message' => $logmessage
				);
			} else {
				$logmessage = "Added $bonusminutesadd bonus minute(s) to $username successfully!";
				$response = array(
					'status' => $bonusminutesadd,
					'status_message' => $logmessage
				);
			}
			$log_stmt = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($log_stmt, "INSERT INTO logtable (usertableid, logmessage) VALUES ((SELECT usertableid FROM usertable WHERE username = ?), ?)")) {
                                throw new Exception("Error preparing log statement: " . mysqli_error($conn));
                        }
                        mysqli_stmt_bind_param($log_stmt, "ss", $token->username, $logmessage);
                        mysqli_stmt_execute($log_stmt);
                        if (mysqli_stmt_affected_rows($log_stmt) === 0) {
                                // This could mean the username wasn't found in the 'user' table,
                                // or the log insert genuinely failed for another reason.
                                throw new Exception("Failed to insert log entry (possibly user " . $token->username . " not found).");
                        }
                        mysqli_stmt_close($log_stmt);

                        // If all operations succeeded, commit the transaction
                        mysqli_commit($conn);
		} catch (Exception $e){
                        // ERROR - rollback transaction
                        mysqli_rollback($conn);
                        $response = array(
                                'status' => -1,
                                'status_message' => "Transaction failed: " . $e->getMessage();
                        );

                }
                $return_response["bonusminutesadd"] = $response;
	}
	# Set regular minutes to some value
	if (!empty($username) && isset($timeleftminutes)){
		// Begin a transaction
                mysqli_begin_transaction($conn);
                try {
			$stmt = mysqli_stmt_init($conn);
			if (!mysqli_stmt_prepare($stmt, "UPDATE usertimetable SET lastrowupdate = NOW(), timeleftminutes = ? WHERE username = ?")){
				throw new Exception("Error preparing update statement: " . mysqli_error($conn));
			}
			mysqli_stmt_bind_param($stmt, "ds", $timeleftminutes, $username);
			mysqli_stmt_execute($stmt);
			$affected_rows = mysqli_stmt_affected_rows($stmt);
			mysqli_stmt_close($stmt);
			$logmessage = "";
			if ($affected_rows == 0){
				$logmessage = "User $username doesn't exist!";
				$response = array(
					'status' => 0,
					'status_message' => $logmessage
				);
			} else {
				$logmessage = "Set timeleftminutes to $timeleftminutes for $username successfully!";
				$response = array(
					'status' => $timeleftminutes,
					'status_message' => $logmessage
				);
			}
			$log_stmt = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($log_stmt, "INSERT INTO logtable (usertableid, logmessage) VALUES ((SELECT usertableid FROM usertable WHERE username = ?), ?)")) {
                                throw new Exception("Error preparing log statement: " . mysqli_error($conn));
                        }
                        mysqli_stmt_bind_param($log_stmt, "ss", $token->username, $logmessage);
                        mysqli_stmt_execute($log_stmt);
                        if (mysqli_stmt_affected_rows($log_stmt) === 0) {
                                // This could mean the username wasn't found in the 'user' table,
                                // or the log insert genuinely failed for another reason.
                                throw new Exception("Failed to insert log entry (possibly user " . $token->username . " not found).");
                        }
                        mysqli_stmt_close($log_stmt);

                        // If all operations succeeded, commit the transaction
                        mysqli_commit($conn);
		} catch (Exception $e){
                        // ERROR - rollback transaction
                        mysqli_rollback($conn);
                        $response = array(
                                'status' => -1,
                                'status_message' => "Transaction failed: " . $e->getMessage();
                        );

                }
                $return_response["timeleftminutes"] = $response;
	}
	# Add minutes to the regular pool
	if (!empty($username) && isset($timeleftminutesadd)){
		// Begin a transaction
                mysqli_begin_transaction($conn);
                try {
			$stmt = mysqli_stmt_init($conn);
			if (!mysqli_stmt_prepare($stmt, "UPDATE usertimetable SET lastrowupdate = NOW(), timeleftminutes = timeleftminutes + ? WHERE username = ?")){
				throw new Exception("Error preparing update statement: " . mysqli_error($conn));
			}
			mysqli_stmt_bind_param($stmt, "ds", $timeleftminutesadd, $username);
			mysqli_stmt_execute($stmt);
			$affected_rows = mysqli_stmt_affected_rows($stmt);
			mysqli_stmt_close($stmt);
			$logmessage = "";
			if ($affected_rows == 0){
				$logmessage = "User $username doesn't exist!";
				$response = array(
					'status' => 0,
					'status_message' => $logmessage
				);
			} else {
				$logmessage = "Added $timeleftminutesadd timeleft minute(s) to $username successfully!";
				$response = array(
					'status' => $timeleftminutesadd,
					'status_message' => $logmessage
				);
			}
			$log_stmt = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($log_stmt, "INSERT INTO logtable (usertableid, logmessage) VALUES ((SELECT usertableid FROM usertable WHERE username = ?), ?)")) {
                                throw new Exception("Error preparing log statement: " . mysqli_error($conn));
                        }
                        mysqli_stmt_bind_param($log_stmt, "ss", $token->username, $logmessage);
                        mysqli_stmt_execute($log_stmt);
                        if (mysqli_stmt_affected_rows($log_stmt) === 0) {
                                // This could mean the username wasn't found in the 'user' table,
                                // or the log insert genuinely failed for another reason.
                                throw new Exception("Failed to insert log entry (possibly user " . $token->username . " not found).");
                        }
                        mysqli_stmt_close($log_stmt);

                        // If all operations succeeded, commit the transaction
                        mysqli_commit($conn);
		} catch (Exception $e){
                        // ERROR - rollback transaction
                        mysqli_rollback($conn);
                        $response = array(
                                'status' => -1,
                                'status_message' => "Transaction failed: " . $e->getMessage();
                        );

                }
                $return_response["timeleftminutesadd"] = $response;
	}
	# Update the time limit to some value
	if (!empty($username) && isset($timelimit)){
		// Begin a transaction
		mysqli_begin_transaction($conn); 
		try {
			$stmt = mysqli_stmt_init($conn);
			if (!mysqli_stmt_prepare($stmt, "UPDATE usertimetable SET lastrowupdate = NOW(), timelimitminutes = ? WHERE username = ?")){
				throw new Exception("Error preparing update statement: " . mysqli_error($conn));
			}
			mysqli_stmt_bind_param($stmt, "ds", $timelimit, $username);
			mysqli_stmt_execute($stmt);
			$affected_rows = mysqli_stmt_affected_rows($stmt);
			mysqli_stmt_close($stmt);
			$logmessage = "";
			if ($affected_rows == 0){
				$logmessage = "User $username doesn't exist!";
				$response = array(
					'status' => 0,
					'status_message' => $logmessage
				);
			} else {
				$logmessage = "Set timelimitminutes to $timelimit for $username successfully!";
				$response = array(
					'status' => $timelimit,
					'status_message' => $logmessage
				);
			}
			$log_stmt = mysqli_stmt_init($conn);
			if (!mysqli_stmt_prepare($log_stmt, "INSERT INTO logtable (usertableid, logmessage) VALUES ((SELECT usertableid FROM usertable WHERE username = ?), ?)")) {
				throw new Exception("Error preparing log statement: " . mysqli_error($conn));
			}
			mysqli_stmt_bind_param($log_stmt, "ss", $token->username, $logmessage);
			mysqli_stmt_execute($log_stmt);
			if (mysqli_stmt_affected_rows($log_stmt) === 0) {
				// This could mean the username wasn't found in the 'user' table,
				// or the log insert genuinely failed for another reason.
				throw new Exception("Failed to insert log entry (possibly user " . $token->username . " not found).");
			}
			mysqli_stmt_close($log_stmt);

			// If all operations succeeded, commit the transaction
			mysqli_commit($conn);
		} catch (Exception $e){
			// ERROR - rollback transaction
			mysqli_rollback($conn);
			$response = array(
				'status' => -1,
				'status_message' => "Transaction failed: " . $e->getMessage();
			);

		}
		$return_response["timelimit"] = $response;
	}
	# Update user order
        if (!empty($username) && isset($userorder)){
                $stmt = mysqli_stmt_init($conn);
                if (mysqli_stmt_prepare($stmt,
                        "UPDATE usertimetable
                            SET
                                userorder = ?
                            WHERE username = ?;"
                )){
                        mysqli_stmt_bind_param($stmt, "is", $userorder, $username);
                        mysqli_stmt_execute($stmt);
                        $affected_rows = mysqli_stmt_affected_rows($stmt);
                        mysqli_stmt_close($stmt);
                        if ($affected_rows == 0){
                                $response = array(
                                        'status' => 0,
                                        'status_message' => "User $username doesn't exist! Unable to update order."
                                );
                        } else {
                                $response = array(
                                        'status' => 1,
                                        'status_message' => "User order for $username updated successfully!"
                                );
                        }
                        $return_response["userorder"] = $response;
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

function get_logs() {
        require(__DIR__ . "/../connect.php");
        $data = json_decode(file_get_contents('php://input'), true);
        $loginterval = "48";
        if (!empty($data["loginterval"])) {
                $logintervaltest = strval($data["username"]);
                // To prevent sql injection
                if (is_numeric($logintervaltest)){
                        $loginterval = $logintervaltest;
                }
        }
        $query = "SELECT lt.logdatetime,
                    ut.username,
                    lt.logmessage
             FROM logtable AS lt
             JOIN usertable AS ut ON lt.usertableid = ut.usertableid
             WHERE lt.logdatetime >= NOW() - INTERVAL " . $loginterval . " HOUR
             ORDER BY lt.logdatetime DESC;";
        $result=mysqli_query($conn, $query);
        $response = array();
        while($row=mysqli_fetch_assoc($result)){
                $response[]=$row;
        }
        if (count($response) == 0){
                $return_response = array (
                        'status' => 0,
                        'status_message' => "No logs exist for given time range: " . $loginterval . "!"
                );
                echo json_encode($return_response);
        } elseif (count($response) > 0){
                $return_response = array(
                        'status' => 1,
                        'status_message' => "Found logs successfully!",
                        'payload' => $response
                );
        } else {
                $return_response = array(
                        'status' => 0,
                        'status_message' =>  "Error: \n" . mysqli_error($conn)
                );
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
			// retrieve logs
			get_logs();
			break;
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
			if (isset($_POST["userorder"])){
				$userorder = strval($_POST["userorder"]);
			}
			modify_user(@$username, @$timelimit, @$bonusminutesadd, @$loginstatus, @$bonusminutes, @$timeleftminutes, @$timeleftminutesadd, @$bonuscounters, @$userorder);
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

