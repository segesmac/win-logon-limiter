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
   <!-- Favicon stuff begin -->
   <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
   <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
   <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
   <link rel="manifest" href="/site.webmanifest">
   <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
   <meta name="msapplication-TileColor" content="#da532c">
   <meta name="theme-color" content="#ffffff">

   <!-- Favicon stuff end -->
   <script src="includes/jquery.min.js"></script>
   <link rel="stylesheet" href="includes/style.css" />
   <a href="#"><div id="userbutton">User Login</div></a>
   <a href="admin/"><div id="adminconsolebutton">Admin Login</div></a>
   <a href="#"><div id="btn_change_pwd" style="display: none;">Change Password</div></a>
</head>
<body>
<!--Your candidate is: <h1 id=list>-</h1> Make the locals proud.
<h2 id=list2>-</h2> So-so-->
<div id="loginwindow" style="display: none;">
   <form method="post" action="#" id="loginform">
        <label for="uname"><b>Username:</b></label>
        <input type="text" placeholder="Enter Username" name="uname" list="userlist" required />
        <datalist id="userlist"></datalist>
        <label for="psw"><b>Password</b></label>
        <input type="password" placeholder="Enter Password" name="psw">
        <button id="loginbutton" type="submit">Login</button>
   </form>
</div>
<div id="changepwdwindow" style="display: none;">
   <div id="alertpassword" style="display: none;"></div>
   <form method="post" action="#" id="passwordform">
        <label for="oldpwd" id="oldpwdlbl"><b>Old Password</b></label>
        <input type="password" id="oldpwdinput" placeholder="Enter Old Password" name="oldpwd" />
        <label for="newpsw1"><b>New Password</b></label>
        <input type="password" placeholder="Enter New Password" name="newpsw1" required>
        <label for="newpsw2"><b>Retype New Password</b></label>
        <input type="password" placeholder="Reenter New Password" name="newpsw2" required>
        <button id="changepwdbutton" type="submit">Submit</button>
   </form>
</div>
<div id="timetable">No data received.</div>
<div id="internetstatus">No data received.</div>
<script src="includes/timetable.js"></script>
</body>
<footer>
   <div><p>Version Number: __VERSION__</p></div>
</footer>
</html>
