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
              if (new_key != "username"){
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
    } else {
        if (data.status_message.startsWith("User  doesn't exist!"){
            $("#timetable").html("Error: "+data.status_message+"<br />Have you set up the client on any machines yet?");
        } else {
            $("#timetable").html("Error: "+data.status_message);
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