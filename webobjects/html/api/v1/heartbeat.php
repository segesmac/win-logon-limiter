<?php

function update_heartbeat( $username = "" # jdoe
        , $loginstatus = "" # 1
        , $computername = "NONE" # DESKTOP-JDOE
    ) {
    include("../connect.php");
    $data = json_decode(file_get_contents('php://input'), true);
    if (!empty(strval($data["username"]))) {
        $username = strval($data["username"]);
    }
    if (!empty(strval($data["loginstatus"]))){
        $loginstatus = $data["loginstatus"];
    }
    if (!empty(strval($data["computername"]))){
        $computername = strval($data["computername"]);
    }
    $return_response = array();

    # Update login status
    if (isset($loginstatus) && $username != ""){
        $stmt = mysqli_stmt_init($conn);
        if (mysqli_stmt_prepare($stmt,
            "UPDATE usertimetable 
            SET 
                bonustimeminutes = CASE 
                    WHEN 1=1
                        AND isloggedon > 0 
                        AND timeleftminutes <= 0 
                        AND bonustimeminutes > 0 
                    THEN 
                        CASE WHEN
                            bonustimeminutes - ROUND((TIME_TO_SEC(TIMEDIFF(NOW(),lastheartbeat))/60),2) <= 0 
                        THEN
                            0 
                        ELSE
                            bonustimeminutes - ROUND((TIME_TO_SEC    (TIMEDIFF(NOW(),lastheartbeat))/60),2) 
                        END 
                    ELSE 
                        bonustimeminutes 
                    END 
                , timeleftminutes = CASE 
                    WHEN 1=1
                        AND isloggedon > 0 
                        AND timeleftminutes > 0 
                    THEN CASE
                        WHEN
                            timeleftminutes - ROUND((TIME_TO_SEC(TIMEDIFF(NOW(),lastheartbeat))/60),2) <= 0 
                        THEN
                            0 
                        ELSE
                            timeleftminutes - ROUND((TIME_TO_SEC(TIMEDIFF(NOW(),lastheartbeat))/60),2) 
                        END
                    ELSE
                        timeleftminutes
                    END
                , lastrowupdate = NOW()
                , lastheartbeat = NOW()
                , isloggedon = ?
                , computername = ?
            WHERE
                username = ?;"
        )){
            mysqli_stmt_bind_param($stmt, "iss", $loginstatus, $computername, $username);
            mysqli_stmt_execute($stmt);
            $affected_rows = mysqli_stmt_affected_rows($stmt);
            $error_msg = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            if ($affected_rows == 0){
                $response = array(
                    'status' => 0,
                    'status_message' => "User $username doesn't exist!"
                );
            } else {
                if (!empty($error_msg)){
                    $response = array(
                        'status' => 1,
                        'status_message' => "Unable to update heartbeat with loginstat: $loginstatus, computername: $computername, username: $username! ERROR: $error_msg"
                    );

                } else {
                    $response = array(
                        'status' => 1,
                        'status_message' => "User $username heartbeat updated successfully with loginstat: $loginstatus, computername: $computername, username: $username!"
                    );
                }
            }
            $return_response["heartbeat"] = $response;
        }
    }
    header('Content-Type: application/json');
    echo json_encode($return_response);
    mysqli_close($conn);
}

$request_method=$_SERVER["REQUEST_METHOD"];
if (isset($request_method)){
    switch($request_method){
        case 'PUT':
            if (!empty($_GET["username"])){
                $username=strval($_GET["username"]);
                update_heartbeat($username);
            } else {
                update_heartbeat();
            }
            break;
        default:
            // Invalid Request Method
            header("HTTP/1.0 405 Method Not Allowed");
            break;
    }
}
