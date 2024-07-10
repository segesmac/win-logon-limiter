<?php

include("/app/cron/db/connect.php");

function update_logoffs(){

        global $conn;
        # Had to add a DATEDIFF to make sure the TIMEDIFF function doesn't encounter anything that makes the time difference function exceed its threshold (probably around 30 days)
        $query="UPDATE usertimetable SET isloggedon = 0, computername = NULL WHERE DATEDIFF(NOW(), lastheartbeat) < 10 AND timelimitminutes >= 0 AND ROUND((TIME_TO_SEC(TIMEDIFF(NOW(),lastheartbeat))/60),2) > 1;";
        $result=mysqli_query($conn, $query);
        #$affected_rows = mysqli_affected_rows($result);
        mysqli_close($conn);
}

update_logoffs();
