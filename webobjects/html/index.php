<!DOCTYPE html>
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
   <link rel="stylesheet" href="includes/style.css?num=<? echo(rand()); ?>" />
   
</head>
<body>
   <!--Your candidate is: <h1 id=list>-</h1> Make the locals proud.
   <h2 id=list2>-</h2> So-so-->
   <div id="menu">
      <div id="welcome" style="display: none;"></div>
      <a href="#" id="btn_change_pwd" class="head" style="display: none;"><div class="head" id="password_button">Change Password</div></a>
      <a href="#" id="userbutton" class="head"><div class="head" id="user_button">User Login</div></a>
      <a href="#" id="a_logout_wrapper" class="head" style="display: none;"><div class="head" id="logout_button">Logout</div></a>
   </div>
   <div id="alerts" style="display: none;"></div>
   <div id="loginwindow" class="alertwindow" style="display: none;">
      <div id="closelogin" class="close">X</div>
      <h2>Login</h2>
      <div id="alertlogin" style="display: none;"></div>
      <form method="post" action="#" id="loginform">
         <div class="inputoption">
            <label for="uname"><b>Username:</b></label>
            <input type="text" placeholder="Enter Username" name="uname" id="uname" list="userlist" required />
            <datalist id="userlist"></datalist>
         </div>
         <div class="inputoption">
            <label for="psw"><b>Password</b></label>
            <input type="password" placeholder="Enter Password" name="psw" id="psw">
         </div>
         <button id="loginbutton" type="submit">Login</button>
      </form>
   </div>
   <div id="changepwdwindow" class="alertwindow" style="display: none;">
      <div id="closepassword" class="close">X</div>
      <h2>Change Password</h2>
      <div id="alertpassword" style="display: none;"></div>
      <form method="post" action="#" id="passwordform">
         <div class="inputoption" id="oldpwddiv">
            <label for="oldpwd" id="oldpwdlbl"><b>Old Password</b></label>
            <input type="password" id="oldpwd" placeholder="Enter Old Password" name="oldpwd" />
         </div>
         <div class="inputoption">
            <label for="newpsw1"><b>New Password</b></label>
            <input type="password" placeholder="Enter New Password" name="newpsw1" id="newpsw1" required>
         </div>
         <div class="inputoption">
            <label for="newpsw2"><b>Retype New Password</b></label>
            <input type="password" placeholder="Reenter New Password" name="newpsw2" id="newpsw2" required>
         </div>
         <button id="changepwdbutton" type="submit">Submit</button>
      </form>
   </div>
   <div id="timetable">No data for Time Table received.</div>
   <div id="refreshtable" style="display: none;">
      <button id="changepwdbutton" onclick='getTable("")'>Refresh Table</button>
   </div>
   <div id="internetstatus">No data for Internet Status received.</div>
   <div id="logtable" style="display: none; max-height: 384px; overflow-y: auto;">No data for Log Table received.</div>
   <script src="includes/timetable.js?num=<? echo(rand()); ?>"></script>
   <div id="calendar">
      <iframe id="icalendar" src="https://calendar.google.com/calendar/embed?height=500&wkst=1&ctz=America%2FDenver&showPrint=0&mode=AGENDA&showDate=0&title=Family&src=ZmE3ZTJjZTQ3NTM3NzVmNWM1YTBmZGQ5OGNkMGMwOTI1ZDNjYzA1YjU5NjQ2ODQ3YWM3NDM4MmIxY2Q2MmZjNUBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&src=YjBhY2JkMTM3MmVhZGVjYzYzYTM5YzdlNmNlMDJhY2M3ZDQxY2RiMjg5ZWJiYWYzMmZiZWMyYzVjMzdkYTk2ZEBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&src=MDU3ZmMxMjRhYTIwOGU1MWIwNTVlNGFkZmNlYjQ5ZTc1ODlhOTRmZjUzZDBkYTdiMzk5NWNkODBiZmUxM2Y4MkBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&src=MTI3ZDBiNjVjYzI2ZDJmOTY5YTJkYTZmYjJjMzkyN2FmYzE4MjU1M2NiYjg4NjhlNzdjZDBkMWIxMGY1OTE0YUBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&src=cmViZWNjYS5zZWdlc21hbkBnbWFpbC5jb20&src=MGMyODU2NjU5ZTVkY2FjODljYmExZjY0OTdkZmNlMjdlZGU4ZTY4ZTA5NTE2YTllZTNkYzEyNTc1ZDg0YmYwNEBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&src=N2QxNmM4Y2YwYjVkNTE1YWEzNTdmNzFhNmM4M2E2Y2Q1YjgzNWFiYmI3NWI3NGRlMTZiMTFiZjdiYjdmYTAwMUBncm91cC5jYWxlbmRhci5nb29nbGUuY29t&src=ZW4udXNhI2hvbGlkYXlAZ3JvdXAudi5jYWxlbmRhci5nb29nbGUuY29t&color=%23AD1457&color=%238E24AA&color=%23039BE5&color=%23ae4f66&color=%23E4C441&color=%23616161&color=%23D50000&color=%23009688" style="border:solid 1px #777" width="1000" height="500" frameborder="0" scrolling="no"></iframe>
   </div>
   <script>
      window.setInterval("reloadIFrame();", 300000); // 5 minutes

      function reloadIFrame() {
       console.log("Attempting to reload frame...");
       document.getElementById('icalendar').src = document.getElementById('icalendar').src;
      }
   </script>
</body>
<footer>
   <div><p>Version Number: __VERSION__</p></div>
</footer>
</html>
