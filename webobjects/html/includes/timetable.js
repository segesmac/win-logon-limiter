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
                var optionExists = ($('#userlist option[value=' + new_val + ']').length > 0);
                if(!optionExists)
                {
                    $('#userlist').append("<option value='"+new_val+"'>"+new_val+"</option>");
                }
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
        } else {
          $.each( val, function ( new_key, new_val) {
            if (new_key == "username"){
              var optionExists = ($('#userlist option[value=' + new_val + ']').length > 0);
              if(!optionExists)
              {
                  $('#userlist').append("<option value='"+new_val+"'>"+new_val+"</option>");
              }
            }
          });
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

$('div#userbutton').click(function(){
  $('div#loginwindow')[0].style.display = 'block';
});

// JWT STUFF

const storeJWT = {};
const loginBtn = document.querySelector('#loginform');
const passwordform = document.querySelector('#passwordform');
const btn_change_pwd = document.querySelector('#btn_change_pwd');
const formData = document.forms[0];
const formData2 = document.forms[1];

// Inserts the jwt to the store object
storeJWT.setJWT = function (data) {
  this.JWT = data;
};
async function authenticate() {
    const response = await fetch('api/v1/authenticate.php', {
        method: 'POST',
        headers: {
          'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: encodeURIComponent("username") + '=' + encodeURIComponent(formData.uname.value) + '&' 
              + encodeURIComponent("password") + '=' + encodeURIComponent(formData.psw.value)
    });
    console.log(response);
    if (response.status >= 200 && response.status <= 299) {
        json_response = await response.text();
        response_obj = JSON.parse(json_response);
        if (response_obj.jwtauthenticated.status_message == 'Successfully created JWT!'){
          storeJWT.setJWT(response_obj.jwtauthenticated.payload);
        
          //frmLogin.style.display = 'none';
          $('div#loginwindow')[0].style.display = 'none';
          btn_change_pwd.style.display = 'block';
          $('div#userbutton')[0].innerHTML = formData.uname.value + ' Logout';

        }
        if (response_obj.authenticated.status_message == "User "+formData.uname.value+" doesn't yet have a password. Please create one!") {
          $('div#loginwindow')[0].style.display = 'none';
          btn_change_pwd.style.display = 'block';
          $('div#changepwdwindow')[0].style.display = 'block';
          $('div#alertpassword')[0].style.display = 'block';
          $('div#alertpassword')[0].innerHTML = '<p>'+response_obj.authenticated.status_message+'</p>';
          $('label#oldpwdlbl')[0].style.display = 'none';
          $('input#oldpwdinput')[0].style.display = 'none';
        } else if (response_obj.authenticated.status_message == 'Password validated.') { 
          console.log("Successfully logged in.");
        } else {
          console.log('Something went wrong jwt: ' + response_obj.jwtauthenticated.status_message);
          console.log('Something went wrong auth: ' + response_obj.authenticated.status_message);
          console.log('Something went wrong: ' + response_obj.status_message);
        }
    } else {
        // Handle errors
        console.log(response.status, response.statusText);
    }
}
loginBtn.addEventListener('submit', async (e) => {
    e.preventDefault();
    authenticate();
});

async function change_password() {
  if (formData2.newpsw1.value != formData2.newpsw2.value){
    $('div#alertpassword')[0].innerHTML = '<p>Passwords do not match. Please try again!</p>';
    return false;
  }
  const response = await fetch('api/v1/update_password.php', {
      method: 'POST',
      headers: {
        'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'Authorization': `Bearer ${storeJWT.JWT}`
      },
      body: encodeURIComponent("username") + '=' + encodeURIComponent(formData.uname.value) + '&' 
            + encodeURIComponent("oldpassword") + '=' + encodeURIComponent(formData2.oldpwd.value) + '&'
            + encodeURIComponent("newpassword") + '=' + encodeURIComponent(formData2.newpsw2.value)
  });
  if (response.status >= 200 && response.status <= 299) {
      json_response = await response.text();
      try {
        response_obj = JSON.parse(json_response);
      } catch {
        console.log("ERROR:");
        console.log(json_response);
      }
      if (response_obj.password_set.status_message.startsWith('Set new password for') && response_obj.password_set.status_message.endsWith('successfully!')){
        //frmLogin.style.display = 'none'; 
        $('div#changepwdwindow')[0].style.display = 'none';
        btn_change_pwd.style.display = 'block';
        console.log(response_obj.jumpcloud_pw_set);
        alert('Password changed successfully!');
      } else {
        console.log('Something went wrong jwt: ' + response_obj.password_set.status_message);
        console.log('Something went wrong auth: ' + response_obj.jumpcloud_pw_set);
        console.log('Something went wrong: ' + response_obj.status_message);
        console.log(response_obj.status_message);
      }
  } else {
      // Handle errors
      console.log(response.status, response.statusText);
  }
}

passwordform.addEventListener('submit', async (e) => {
  e.preventDefault();
  change_password();
});

btn_change_pwd.addEventListener('click', function (e){
  $('div#changepwdwindow')[0].style.display = 'block';
  $('label#oldpwdlbl')[0].style.display = 'block';
  $('input#oldpwdinput')[0].style.display = 'block';
});

/* // Need to create a form for changing the password
btn_change_pwd.addEventListener('click', async (e) => {
  res = await fetch('api/v1/update_password.php', {
    headers: {
      'Authorization': `Bearer ${storeJWT.JWT}`
    }
  });
  timeStamp = await res.text();
  if (res.status == 401 && (timeStamp == 'Expired token' || timeStamp == 'Signature Verification failed')) {
      console.log("Reauthenticating...");
      await authenticate();
      res = await fetch('./resource.php', {
        headers: {
          'Authorization': `Bearer ${storeJWT.JWT}`
        }
      });
      timeStamp = await res.text();
  }
  console.log(timeStamp);
});
*/

// END JWT STUFF


//getTable();
//count = 0;
//while (count<=10) {

getTable("");
//console.log("Sleeping 10 seconds...");
//await sleep(10000);
//console.log("Done sleeping...");
//}