<html>
<head>
<?php  
   ## Get Computername - if computer name is in admin list, then allow access to UI
   ## yes, I realize this is not secure.  If a child can get around this, they deserve the computer time
   // Get ip
   #if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) { $ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; } else { $ip = $_SERVER["REMOTE_ADDR"]; }
   #$computerid = gethostbyaddr($ip);
   /*
   // Send ReqPacket
   $fp = fsockopen('udp://'.$ip, 137); 
   fwrite($fp, "\x80b\0\0\0\1\0\0\0\0\0\0 CKAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\0\0!\0\1");
   // Get Data (2 sec timeout)
   socket_set_timeout($fp, 2); 
   $data = fread($fp, 256);
   // Get NetBios ID
   $nbrec = ord($data[56]); 
   // Display nebios records : the username is a record of type 3
   for($i = 0; $i < $nbrec; $i++) { 
        $offset = 18 * $i; 
        #echo "O R D :" . ord($data[72 + $offset]) . "<br />";
        #echo "D A T A : $data<br />";
        if (ord($data[72 + $offset]) == 32) 
           { 
              #echo "in there <br />";
              $computerid = trim(substr($data, 57 + $offset, 15)); 
           }      

        } 
	// Put variable in place
   $computerid = str_replace('$','',$computerid);
   */
   #echo "<br />Computer ID: $computerid<br />";
   #echo "<br />IP Address: $ip<br />";
   #if (in_array($computerid,array("DESKTOP-OVD88IV","DESKTOP-0OE6SKF","DESKTOP-9E7C3CL"))){
   #    echo "Your computer is authenticated.";
   #}
   #phpinfo(32);
?>
<script src="includes/jquery.min.js"></script>
<link rel="stylesheet" href="includes/style.css" />

</head>
<body>
<!--Your candidate is: <h1 id=list>-</h1> Make the locals proud.
<h2 id=list2>-</h2> So-so-->
<div id="timetable">No data received.</div>
<script src="includes/admintimetable.js"></script>
</body>

</html>
