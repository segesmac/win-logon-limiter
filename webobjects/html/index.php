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
<script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
<style>
table, th, td {
  border: 2px solid;
  padding: 5px;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 30px;
}
th {
  background: #ddd;
}
table {
  border-collapse: collapse;
  margin-left: auto;
  margin-right: auto;
}
#internetstatus {
  padding: 5px;
  text-align: center;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 30px;
}
.on {
  color: green;
  background-color: lightgreen;
  font-weight: bold;
}
.off {
  color: red;
  background-color: pink;
  font-weight: bold;
}
</style>
</head>
<body>
<!--Your candidate is: <h1 id=list>-</h1> Make the locals proud.
<h2 id=list2>-</h2> So-so-->
<div id="timetable"></div>
<div id="internetstatus"></div>
<script>

  // NOTE: window.RTCPeerConnection is "not a constructor" in FF22/23
  var RTCPeerConnection = /*window.RTCPeerConnection ||*/ window.webkitRTCPeerConnection || window.mozRTCPeerConnection;

  if (RTCPeerConnection) (function () {
    var rtc = new RTCPeerConnection({iceServers:[]});
    if (1 || window.mozRTCPeerConnection) {      // FF [and now Chrome!] needs a channel/stream to proceed
        rtc.createDataChannel('', {reliable:false});
    };
    
    rtc.onicecandidate = function (evt) {
        // convert the candidate to SDP so we can run it through our general parser
        // see https://twitter.com/lancestout/status/525796175425720320 for details
        if (evt.candidate) grepSDP("a="+evt.candidate.candidate);
    };
    rtc.createOffer(function (offerDesc) {
        grepSDP(offerDesc.sdp);
        rtc.setLocalDescription(offerDesc);
    }, function (e) { console.warn("offer failed", e); });
    
    
    var addrs = Object.create(null);
    addrs["0.0.0.0"] = false;
    function updateDisplay(newAddr) {
        if (newAddr in addrs) return;
        else addrs[newAddr] = true;
        var displayAddrs = Object.keys(addrs).filter(function (k) { return addrs[k]; });
        document.getElementById('list').textContent = displayAddrs.join(" or perhaps ") || "n/a";
    }
    
    function grepSDP(sdp) {
        var hosts = [];
        sdp.split('\r\n').forEach(function (line) { // c.f. http://tools.ietf.org/html/rfc4566#page-39
            if (~line.indexOf("a=candidate")) {     // http://tools.ietf.org/html/rfc4566#section-5.13
                var parts = line.split(' '),        // http://tools.ietf.org/html/rfc5245#section-15.1
                    addr = parts[4],
                    type = parts[7];
                var candidate = parts[0].split(':')[1];
                //if (type === 'host') updateDisplay(candidate);
            } else if (~line.indexOf("c=")) {       // http://tools.ietf.org/html/rfc4566#section-5.7
                var parts = line.split(' '),
                    addr = parts[2];
                //updateDisplay(addr);
            }
        });
    }
  })(); else {
      document.getElementById('list').innerHTML = "<code>ifconfig | grep inet | grep -v inet6 | cut -d\" \" -f2 | tail -n1</code>";
      document.getElementById('list').nextSibling.textContent = "In Chrome and Firefox your IP should display automatically, by the power of WebRTCskull.";
  }

  function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
  async function getTable(datastring_prev) {
    $.getJSON( "api/v1/users.php", function( data ) {
    var items = [];
    var columns_to_exclude = ["usertimetableid","timelimitminutes","lastrowupdate","isloggedon","lastlogon","lastheartbeat","computername"];
    console.log(data);
    data_to_parse = data.payload;
    internet_on = false;
    datastring = JSON.stringify(data);
    if (data.status == 1 && datastring_prev != datastring){
      console.log("Updating data");
      $.each( data_to_parse, function( key, val ) {
        var row_header = "";
        var row = "";
        if (val.timelimitminutes != "-1.00"){
          $.each( val, function ( new_key, new_val) {
            // Check list of columns to exclude from above and exclude them
            if (!columns_to_exclude.includes(new_key)){ 
              if (key == 0){
                var column_header = "";
                switch (new_key){
                  case "username":
                    column_header = "Username";
                    break;
                  case "lastlogon":
                    column_header = "Last Logon";
                    break;
                  //case "timelimitminutes":
                  //   column_header = "Time Limit (Minutes)";
                  //   break;
                  case "timeleftminutes":
                     column_header = "Time Left (Minutes)";
                     break;
                  case "bonustimeminutes":
                     column_header = "Bonus Time (Minutes)";
                     break;
                  case "bonuscounters":
                     column_header = "Counters Owed";
                     break;
                  default:
                    column_header = "UNDEFINED";
                }
                row_header +=  "<th>" + column_header + "</th>";
              };
              if (new_key != "username" && new_key != "bonuscounters"){
                row += "<td>" + new_val.split(".")[0] + "</td>";
              } else {
                row += "<td>" + new_val + "</td>";
              }
            // Check to see if the computername column is populated with something - that means the internet is on
            } else if (new_key == "computername" && new_val != null){
              internet_on = true;
            }
          });
          if (key == 0){
            items.push( "<tr>" + row_header + "</tr>" );
          }
          items.push( "<tr>" + row + "</tr>" );
        }
      });
      $("#timetable").html($( "<table/>", {
        "class": "my-new-list",
        html: items.join( "" )
      }));
      // Set the internet status div
      if (internet_on){
        $("#internetstatus").html("Internet is <span class=\"on\">ON</span>");
      } else {
        $("#internetstatus").html("Internet is <span class=\"off\">OFF</span>");
      }
    }
    });
    console.log("Sleeping 10 seconds...");
    await sleep(10000);
    console.log("Done sleeping...");
    
    getTable(datastring);
    //})();
  }

  //getTable();
  //count = 0;
  //while (count<=10) {

    getTable("");
    //console.log("Sleeping 10 seconds...");
    //await sleep(10000);
    //console.log("Done sleeping...");
  //}
</script>
</body>

</html>
