<?php

include("/app/cron/db/connect.php");

function update_timelimits(){

	global $conn;
	$query="UPDATE usertimetable SET timeleftminutes = timelimitminutes;";
	$result=mysqli_query($conn, $query);
	#$affected_rows = mysqli_affected_rows($result);
	mysqli_close($conn);
}

update_timelimits();
