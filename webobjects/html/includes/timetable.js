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
    } else if (data.status < 1) {
        if (data.status_message.startsWith("No users exist!")){
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