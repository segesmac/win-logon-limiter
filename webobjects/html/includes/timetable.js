function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

// JWT STUFF

const storeJWT = {};
const loginBtn = document.querySelector('#loginform');
const logout_button = document.querySelector('#logout_button');
const passwordform = document.querySelector('#passwordform');
const btn_change_pwd = document.querySelector('#btn_change_pwd');
const password_button = document.querySelector('#password_button');
const closelogin = document.querySelector('#closelogin');
const closepassword = document.querySelector('#closepassword');

const formData = document.forms[0];
const formData2 = document.forms[1];

// Inserts the jwt to the store object
storeJWT.setJWT = function (data) {
  this.JWT = data;
};
storeJWT.setUser = function (data) {
  this.jwtuser = data;
};
storeJWT.setAdmin = function (data) {
  this.jwtadmin = data;
};

function compare_elements(element_id1,element_id2){
  $element1 = $('#'+element_id1);
  $element2 = $('#'+element_id2);
  if ($element1.text() == $element2.text()){
    return true;
  }
  return false;
}

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
          storeJWT.setUser(formData.uname.value);
          console.log("is_admin: "+response_obj.jwtauthenticated.is_admin)
          console.log("is_tempadmin: "+response_obj.jwtauthenticated.is_tempadmin)
          if (response_obj.jwtauthenticated.is_admin == 1 || response_obj.jwtauthenticated.is_tempadmin == 1){
            storeJWT.setAdmin(1);
            $('div#refreshtable').show();
            $('span.admin_update').show();
          } else {
            storeJWT.setAdmin(0);
          }
          //frmLogin.style.display = 'none';
          $('div#loginwindow')[0].style.display = 'none';
          $('input[name="uname"]').val(''); // Reset inputs
          $('input[name="psw"]').val(''); // Reset inputs
          btn_change_pwd.style.display = 'inline-flex';
          $('a#userbutton')[0].style.display = 'none';
          $('a#a_logout_wrapper')[0].style.display = 'inline-flex';
          $('div#welcome')[0].style.display = 'inline-flex';
          $('div#welcome')[0].innerHTML = '<p>Welcome '+ storeJWT.jwtuser + '!';

        }
        if (response_obj.authenticated.status_message == "User "+storeJWT.jwtuser+" doesn't yet have a password. Please create one!") {
          $('div#loginwindow')[0].style.display = 'none';
          btn_change_pwd.style.display = 'inline-flex';
          $('div#changepwdwindow')[0].style.display = 'block';
          $('div#alertpassword')[0].style.display = 'block';
          $('div#alertpassword')[0].innerHTML = '<p>'+response_obj.authenticated.status_message+'</p>';
          $('div#oldpwddiv')[0].style.display = 'none';
        } else if (response_obj.authenticated.status_message == 'Password validated.') { 
          console.log("Successfully logged in.");
          $('div#alertlogin')[0].style.display = 'none';
        } else {
          $('div#alertlogin')[0].style.display = 'block';
          $('div#alertlogin')[0].innerHTML = '<p>'+response_obj.authenticated.status_message+'</p>';
          console.log('Something went wrong jwt: ' + response_obj.jwtauthenticated.status_message);
          console.log('Something went wrong auth: ' + response_obj.authenticated.status_message);
          console.log('Something went wrong: ' + response_obj.status_message);
        }
    } else {
        // Handle errors
        console.log(response.status, response.statusText);
    }
    updateTable("");
}
async function send_span_val(element_id, element_name){
  data_value = $('#'+element_id).html();
  data_username = $('#'+element_id).parent().parent().children()[0].children[0].innerHTML
  switch (element_name){
    case "timelimitminutes":
      key_name = "timelimit";
      break;
    case "timeleftminutes":
      key_name = "timeleftminutes";
      break;
    case "bonustimeminutes":
      key_name = "bonusminutes";
      break;
    case "bonuscounters":
      key_name = "bonuscounters";
      break;
    default:
      key_name = "UNDEFINED";
  }
  console.log("Sending username: "+data_username);
  console.log("Sending key_name: "+key_name);
  console.log("Sending key_value: "+data_value);
  var body_obj = encodeURIComponent("username") + '=' + encodeURIComponent(data_username) + '&'
  + encodeURIComponent(key_name) + '=' + encodeURIComponent(data_value)
  if (key_name == 'bonusminutes'){
    
    var matches = element_id.match(/\d+$/);
    if (matches) {
      number = matches[0];
    }
    var bonuscounters = $('#u_bonuscounters'+number).html()
    body_obj += '&bonuscounters=' +  encodeURIComponent(bonuscounters)
  }
  const response = await fetch('api/v1/admin_users.php', {
    method: 'POST',
    headers: {
      'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8',
      'Authorization': `Bearer ${storeJWT.JWT}`
    },
    body: body_obj
  });
  if (response.status >= 200 && response.status <= 299) {
    json_response = await response.text();
    try {
      response_obj = JSON.parse(json_response);
    } catch {
      console.log("ERROR:");
      console.log(json_response);
    }
    if (response_obj[key_name].status_message.endsWith('successfully!')){
      //frmLogin.style.display = 'none'; 
      console.log(response_obj[key_name].status_message);
    } else {
      //$('div#alertpassword')[0].style.display = 'block';
      //$('div#alertpassword')[0].innerHTML = '<p>'+response_obj.password_set.status_message+'</p>';
      console.log('Something went wrong '+key_name+': ' + response_obj[key_name].status_message);
      console.log('Something went wrong HMMMM: ' + response_obj[key_name]);
      console.log('Something went wrong: ' + response_obj.status_message);
      console.log(response_obj.status_message);
    }
  } else {
      // Handle errors
      console.log(response.status, response.statusText);
  }
  updateTable("");
}

function update_span_val(element_id, differential, element_id_to_compare, element_id_to_update){
  if (null != differential) {
    var current_val_elem = document.getElementById(element_id);
    var current_val = Number(current_val_elem.innerHTML);
    var new_val = current_val + differential;
    if (new_val < 0){
      new_val = 0
    }
    current_val_elem.innerHTML = new_val.toString();
  }
  if (compare_elements(element_id,element_id_to_compare)){
    $('#'+element_id_to_update).children().css('color', 'white');
  } else {
    $('#'+element_id_to_update).children().css('color', 'lightgreen');
    
  }
  if (element_id.startsWith("u_bonustimeminutes") && (new_val > 0 || current_val > 0)){
    var matches = element_id.match(/\d+$/);
    if (matches) {
      number = matches[0];
    }
    update_span_val("u_bonuscounters"+number,differential/10, "bonuscounters"+number, "c_bonuscounters"+number)
  }
}
async function updateTable(datastring_prev) {
  await $.getJSON( "api/v1/users.php", function( data ) {
  var items = [];
  var columns_to_exclude = ["usertimetableid","timelimitminutes","lastrowupdate","isloggedon","lastlogon","lastheartbeat","computername","userorder"];
  console.log(data);
  data_to_parse = data.payload;
  internet_on = false;
  datastring = JSON.stringify(data);
  if (data.status == 1 && datastring_prev != datastring){
    console.log("Updating data");
    if (storeJWT.jwtadmin == 1) {
      $('span.admin_update').show();
    }
    var count = 0;
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
              //row_header +=  "<th id='"+new_key+"_th'>" + column_header + "</th>";
            };
            console.log('IS_ADMIN: ' + storeJWT.jwtadmin)
            
              //console.log("updating #u_"+new_key+count+" to display inline")
              //$('#u_'+new_key+count)[0].style.display = 'inline'
              //$('#b_'+new_key+count)[0].style.display = 'inline'
              //$('#c_'+new_key+count)[0].style.display = 'inline'
              
            
            if (new_key != "username"){
              $('#'+new_key+count).html(new_val.split(".")[0])
              update_span_val("u_"+new_key+count, null, new_key+count, "c_"+new_key+count);
              //row += "<td><span id='"+new_key+count+"'>" + new_val.split(".")[0] + "</span><span id='u_"+new_key+count+"' style='display: none;'> U: " + new_val.split(".")[0] + "</span></td>";
            } else {
              //row += "<td><span id='"+new_key+count+"'>" + new_val + "</span></td>";
              console.log("update_span_val(\"u_"+new_key+count+"\", null, \"h_"+new_key+count+"\", \"c_"+new_key+count+"\")")
              update_span_val("u_"+new_key+count, null, "h_"+new_key+count, "c_"+new_key+count);
              var optionExists = ($('#userlist option[value=' + new_val + ']').length > 0);
              if(!optionExists)
              {
                  $('#userlist').append("<option value='"+new_val+"'>"+new_val+"</option>");
              }
            }
          // Check to see if the computername column is populated with something - that means the internet is on
          } else if (new_key == "computername" && new_val != null){
            internet_on = true;
          } else if (new_key == "timelimitminutes"){
            $('#h_username'+count).html(new_val.split(".")[0]);
            if ($('#u_username'+count).html() == ""){
              $('#u_username'+count).html(new_val.split(".")[0]);
            }
            update_span_val("u_username"+count, null, "h_username"+count, "c_username"+count);
          }
          
        });
        //if (key == 0){
        //  items.push( "<tr>" + row_header + "</tr>" );
        //}
        //var row_class = "odd"
        //if (count % 2 == 0){
        //  row_class = "even"
        //}
        //items.push( "<tr class='"+row_class+"'>" + row + "</tr>" );
        count++
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
    //$("#timetable").html($( "<table/>", {
    //  "class": "my-new-list",
    //  html: items.join( "" )
    //}));
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
  if (storeJWT.jwtadmin == 1) {
      $('#logtable').show();
      const response = await fetch('api/v1/admin_users.php', {
            method: 'GET',
            headers: {
                'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'Authorization': `Bearer ${storeJWT.JWT}`
            }
            //body: encodeURIComponent("username") + '=' + encodeURIComponent(storeJWT.jwtuser) + '&'
            //      + encodeURIComponent("oldpassword") + '=' + encodeURIComponent(formData2.oldpwd.value) + '&'
            //      + encodeURIComponent("newpassword") + '=' + encodeURIComponent(formData2.newpsw2.value)
      });
      if (response.status >= 200 && response.status <= 299) {
          json_response = await response.text();
          try {
            response_obj = JSON.parse(json_response);
          } catch {
            console.log("ERROR:");
            console.log(json_response);
          }
          console.log("LOG_INFO:");
          console.log(response_obj);
	  updateLogTable(response_obj);
      }
  } else {
      $('#logtable').hide();
  }
  return datastring;
  //updateTable(datastring);
  //})();
}

function updateLogTable(data_object) {
  var data = data_object.payload
  const targetDiv = document.getElementById("logtable");
  // Clear any existing content in the div
  if (targetDiv) {
      targetDiv.innerHTML = '';
  } else {
      console.error(`Target div with ID '${targetDivId}' not found.`);
      return;
  }
  
  // If no data or empty data, display a message
  if (!data || data.length === 0) {
      const noDataMessage = document.createElement('p');
      noDataMessage.className = 'text-center text-gray-600 p-4';
      noDataMessage.textContent = 'No log data available to display.';
      targetDiv.appendChild(noDataMessage);
      return;
  }
  
  // Create table elements
  const table = document.createElement('table');
  table.className = 'min-w-full divide-y divide-gray-200 rounded-md overflow-hidden'; // Tailwind classes

  const thead = document.createElement('thead');
  thead.className = 'bg-gray-50'; // Tailwind class

  const tbody = document.createElement('tbody');
  tbody.className = 'bg-white divide-y divide-gray-200'; // Tailwind classes

  // --- Create Table Headers ---
  const headers = Object.keys(data[0]); // Get keys from the first object for headers
  const headerRow = document.createElement('tr');

  headers.forEach(headerText => {
      const th = document.createElement('th');
      th.className = 'px-4 py-2 sm:px-6 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'; // Tailwind classes
      // Format header names for better readability (e.g., "logdatetime" -> "Log Datetime")
      th.textContent = headerText.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase());
      headerRow.appendChild(th);
  });
  thead.appendChild(headerRow);
  table.appendChild(thead);

  // --- Create Table Body (Rows and Cells) ---
  data.forEach(rowData => {
      const tr = document.createElement('tr');
      tr.className = 'hover:bg-gray-50'; // Tailwind class
      headers.forEach(header => {
          const td = document.createElement('td');
          td.className = 'px-4 py-2 sm:px-6 sm:py-4 whitespace-nowrap text-sm text-gray-900'; // Tailwind classes
          td.innerHTML = rowData[header]; // Get value for the current header
          tr.appendChild(td);
      });
      tbody.appendChild(tr);
  });
  table.appendChild(tbody);

  // Wrap the table in a responsive container
  const tableContainer = document.createElement('div');
  tableContainer.className = 'overflow-x-auto'; // Makes table scrollable on small screens
  tableContainer.appendChild(table);

  // Append the complete table to the target div
  targetDiv.appendChild(tableContainer);
}

async function getTable(datastring_prev) {
  await $.getJSON( "api/v1/users.php", function( data ) {
  var items = [];
  var columns_to_exclude = ["usertimetableid","timelimitminutes","lastrowupdate","isloggedon","lastlogon","lastheartbeat","computername","userorder"];
  console.log(data);
  data_to_parse = data.payload;
  internet_on = false;
  datastring = JSON.stringify(data);
  if (data.status == 1 && datastring_prev != datastring){
    console.log("Updating data");
    var count = 0;
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
              row_header +=  "<th id='"+new_key+"_th'>" + column_header + "</th>";
            };
            if (new_key != "username"){
              var amount = 10;
              if (new_key == "bonuscounters"){
                amount = 1;
              }
              row += "<td><span id='"+new_key+count+"'>" + new_val.split(".")[0] + "</span> \
                    <span id='c_"+new_key+count+"' class='admin_update' style='display:none;'> \
                        <button onclick='send_span_val(\"u_"+new_key+count+"\", \""+new_key+"\")' style='font-weight: bold;'>✓</button> \
                    </span> \
                    <span id='u_"+new_key+count+"' class='admin_update' style='display: none;'>" + new_val.split(".")[0] + "</span> \
                    <span id='b_"+new_key+count+"' class='admin_update' style='display:none;'> \
                        <button onclick='update_span_val(\"u_"+new_key+count+"\",-"+amount+", \""+new_key+count+"\", \"c_"+new_key+count+"\")'>-"+amount+"</button> \
                        <button onclick='update_span_val(\"u_"+new_key+count+"\","+amount+", \""+new_key+count+"\", \"c_"+new_key+count+"\")'>+"+amount+"</button> \
                    </span></td>";
            } else {
              row += "<td><span id='"+new_key+count+"'>" + new_val + "</span> \
              <span id='h_"+new_key+count+"' style='display: none;'></span> \
              <span id='c_"+new_key+count+"' class='admin_update' style='display:none;'> \
                  <button onclick='send_span_val(\"u_"+new_key+count+"\", \"timelimitminutes\")' style='font-weight: bold;'>✓</button> \
              </span> \
              <span id='u_"+new_key+count+"' class='admin_update' style='display: none;'></span> \
              <span id='b_"+new_key+count+"' class='admin_update' style='display:none;'> \
                  <button onclick='update_span_val(\"u_"+new_key+count+"\",-10, \"h_"+new_key+count+"\", \"c_"+new_key+count+"\")'>-10</button> \
                  <button onclick='update_span_val(\"u_"+new_key+count+"\",10, \"h_"+new_key+count+"\", \"c_"+new_key+count+"\")'>+10</button> \
              </span></td>";
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
        var row_class = "odd"
        if (count % 2 == 0){
          row_class = "even"
        }
        items.push( "<tr class='"+row_class+"'>" + row + "</tr>" );
        count++
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
  updateTable("");
  return datastring;
}

$('div#user_button').click(function(){
  $('div#loginwindow')[0].style.display = 'block';
});

loginBtn.addEventListener('submit', async (e) => {
    e.preventDefault();
    authenticate();
});

async function change_password() {
  if (formData2.newpsw1.value != formData2.newpsw2.value){
    $('div#alertpassword')[0].style.display = 'block';
    $('div#alertpassword')[0].innerHTML = '<p>Passwords do not match. Please try again!</p>';
    return false;
  }
  const response = await fetch('api/v1/update_password.php', {
      method: 'POST',
      headers: {
        'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'Authorization': `Bearer ${storeJWT.JWT}`
      },
      body: encodeURIComponent("username") + '=' + encodeURIComponent(storeJWT.jwtuser) + '&' 
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
        $('div#alertpassword')[0].style.display = 'none';
        $('div#alertpassword')[0].innerHTML = '';
        btn_change_pwd.style.display = 'inline-flex';
        console.log(response_obj.jumpcloud_pw_set);
        jumpcloud_response_obj = JSON.parse(response_obj.jumpcloud_pw_set);
        $('input[name="oldpwd"]').val(''); // Reset inputs
        $('input[name="newpsw1"]').val(''); // Reset inputs
        $('input[name="newpsw2"]').val(''); // Reset inputs
        // if username returned is the correct username and the date password was changed is less than 10 minutes ago
        if (jumpcloud_response_obj.username == storeJWT.jwtuser && ((Date.now() - Date.parse(jumpcloud_response_obj.password_date)) / 60000) <= 10){
          alert('Password changed successfully!');
        }
      } else {
        $('div#alertpassword')[0].style.display = 'block';
        $('div#alertpassword')[0].innerHTML = '<p>'+response_obj.password_set.status_message+'</p>';
        console.log('Something went wrong password_set: ' + response_obj.password_set.status_message);
        console.log('Something went wrong jumpcloud: ' + response_obj.jumpcloud_pw_set);
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

password_button.addEventListener('click', function (e){
  $('div#changepwdwindow')[0].style.display = 'block';
  $('div#oldpwddiv')[0].style.display = 'flex';
});

closelogin.addEventListener('click', function (e){
  $('div#loginwindow')[0].style.display = 'none';
});

closepassword.addEventListener('click', function (e){
  $('div#changepwdwindow')[0].style.display = 'none';
});

logout_button.addEventListener('click', function (e){
  $('a#a_logout_wrapper')[0].style.display = 'none';
  $('a#userbutton')[0].style.display = 'inline-flex';
  storeJWT.setJWT('');
  storeJWT.setUser('');
  storeJWT.setAdmin('');
  btn_change_pwd.style.display = 'none';
  $('div#welcome')[0].style.display = 'none';
  $('div#welcome')[0].innerHTML = '';
  $('span.admin_update').hide();
  $('div#refreshtable').hide();
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

async function run_updates(tabledata) {
  console.log("Sleeping 10 seconds...");
  await sleep(10000);
  console.log("Done sleeping...");
  try {
    new_tabledata = await updateTable(tabledata);
  } catch (error) {
    console.error(error);
  }
  run_updates(new_tabledata);
}

run_updates(getTable(""));
//console.log("Sleeping 10 seconds...");
//await sleep(10000);
//console.log("Done sleeping...");
//}
