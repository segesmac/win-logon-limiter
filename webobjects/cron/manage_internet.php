<?php

	include("/app/cron/db/connect.php");

	function get_computers(){

		global $conn;
		$query="SELECT count(*) as computers FROM usertimetable WHERE computername IS NOT NULL;";
		$result=mysqli_query($conn, $query);
		$data=mysqli_fetch_assoc($result);
		$is_enabled = file_get_contents("/app/cron/is_enabled.txt");
		print("is_enabled = $is_enabled\n");
		if ($data['computers'] > 0){
			print("Found computers\n");
			if ($is_enabled == "false"){
				print("Enabling wifi...\n");
				shell_exec("ssh ubnt@".$_ENV["WLL_ROUTER_IP"]." /home/ubnt/scripts/enable-wifi.sh");
				file_put_contents("/app/cron/is_enabled.txt","true");
			}
		} else {
			print("No computers found\n");
			if ($is_enabled == "true"){
				print("Disabling wifi...\n");
				shell_exec("ssh ubnt@".$_ENV["WLL_ROUTER_IP"]." /home/ubnt/scripts/disable-wifi.sh");
				file_put_contents("/app/cron/is_enabled.txt","false");
			}
		}
		mysqli_close($conn);
	}

	get_computers();

