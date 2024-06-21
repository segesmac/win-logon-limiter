$count = 0
$global:last_message = ''
while ($true){

	Function Write-Log {
		param (
		  $message,
		  $append = $true,
		  $logfile = 'C:\Scripts\logs\winlogonlimiter_log.txt'
		)
		if ($message -ne $global:last_message){
			$global:last_message = $message
			$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
			Write-Output "$timestamp`: $message"
			if ($append){
				Write-Output "$timestamp`: $message" | Out-File $logfile -Append
			} else {
				#if (test-path $logfile){
				#	Copy-Item $logfile "$logfile.$(Get-Date -Format `"yyyy-MM-dd_HH-mm-ss`").bak"
				#}
				Write-Output "$timestamp`: $message" | Out-File $logfile
			}
		}
	}
	# Check if logged-in session exists

	Function Get-ComputerSessions {
	<#
	.SYNOPSIS
		Retrieves tall user sessions from local or remote server/s
	.DESCRIPTION
		Retrieves tall user sessions from local or remote server/s
	.PARAMETER computer
		Name of computer/s to run session query against.
	.NOTES
		Name: Get-ComputerSessions
		Author: Boe Prox
		DateCreated: 01Nov2010
	 
	.LINK
		https://boeprox.wordpress.org
		https://learn-powershell.net/2010/11/01/quick-hit-find-currently-logged-on-users/
	.EXAMPLE
	Get-ComputerSessions -computer "server1"
	 
	Description
	-----------
	This command will query all current user sessions on 'server1'.
	 
	#>
	[cmdletbinding(
		DefaultParameterSetName = 'session',
		ConfirmImpact = 'low'
	)]
		Param(
			[Parameter(
				Mandatory = $false,
				Position = 0,
				ValueFromPipeline = $True)]
				[string[]]$computer = "localhost"
				)
	Begin {
		$report = @()
		}
	Process {
		ForEach($c in $computer) {
			# Parse 'query session' and store in $sessions:
			$sessions = query session /server:$c
				1..($sessions.count -1) | ForEach-Object {
					$temp = "" | Select-Object Computer,SessionName, Username, Id, State, Type, Device
					$temp.Computer = $c
					$temp.SessionName = $sessions[$_].Substring(1,18).Trim()
					$temp.Username = $sessions[$_].Substring(19,20).Trim()
					$temp.Id = $sessions[$_].Substring(39,9).Trim()
					$temp.State = $sessions[$_].Substring(48,8).Trim()
					$temp.Type = $sessions[$_].Substring(56,12).Trim()
					$temp.Device = $sessions[$_].Substring(68).Trim()
					$report += $temp
				}
			}
		}
	End {
		$report
		}
	}


	function Set-Permissions {

		Param(
			[Parameter(
				Mandatory = $false,
				Position = 0,
				ValueFromPipeline = $True)]
				[string]$scripts_folder = "C:\Scripts"
				)
		if ($env:USERDOMAIN -eq $env:COMPUTERNAME){
			# Gets the Access Conrol List from the scripts folder
			Write-Log "Getting ACL from $scripts_folder."
			$acl = Get-Acl $scripts_folder

			# Check to see if the correct permissions are applied already
			$admin_access = $acl.Access | Where-Object IsInherited -eq $false | Where-Object FileSystemRights -eq "FullControl" | Where-Object IdentityReference -eq "BUILTIN\Administrators"

			$superuser_access = $acl.Access | Where-Object IsInherited -eq $false | Where-Object FileSystemRights -eq "FullControl" | Where-Object IdentityReference -eq "$env:USERDOMAIN\$env:USERNAME"

			# If some permissions are missing, add them
			if (!$admin_access -or !$superuser_access){
				Write-Log "Removing inheritence and associated permissions."
				# Removes inheritence and any inherited permissions (first parameter, if true, blocks inheritence. second parameter, if false, removes inherited permissions)
				$acl.SetAccessRuleProtection($true,$false)

				if (!$admin_access) {
					Write-Log "Adding Builtin\Administrators access."
					$accessrule = New-Object  system.security.accesscontrol.filesystemaccessrule("BUILTIN\Administrators","FullControl","Allow")
					$acl.SetAccessRule($accessrule)
				}

				if (!$superuser_access) {
					Write-Log "Adding Superuser access."
					$accessrule = New-Object  system.security.accesscontrol.filesystemaccessrule("$env:USERDOMAIN\$env:USERNAME","FullControl","Allow")
					$acl.SetAccessRule($accessrule)
				}

				Write-Log "Committing changes."
				$acl | Set-Acl $scripts_folder

			} else {
				Write-Log "Permissions are already set appropriately."
			}
		} else {
			Write-Log "Assuming running user is not a real user and skipping permissions setting."
		}

	}

	$scripts_folder = Split-Path $MyInvocation.MyCommand.Path -Parent # The folder in which heartbeat.ps1 resides
	$config_folder = Join-Path $scripts_folder 'config'
	if (!(Test-Path $config_folder)){
		New-Item -ItemType Directory -Path "$config_folder"
	}
	$config_path = Join-Path $config_folder 'config.json'
	if (!(Test-Path $config_path)){
		# Let's lay down the default config
		$default_config = @{ 'address' = 'timeleft.us'; 'protocol' = 'https' }
		ConvertTo-Json $default_config | Out-File $config_path -Encoding utf8
	}
	$config_obj = Get-Content $config_path | ConvertFrom-Json
	if ($config_obj.protocol.toLower() -eq "http" -or $config_obj.protocol.toLower() -eq "https"){
		$uri = "$($config_obj.protocol.toLower())://$([uri]::EscapeUriString($config_obj.address.toLower()))/api/v1" #"http://timeleft.us/api/v1"
	} else {
		Write-Log "'protocol' must be either 'http' or 'https'. You entered '$($config_obj.protocol.toLower())'"
		Write-Log 'Falling back to default config of "address" = "timeleft.us" and "protocol" = "https".'
		$uri = 'https://timeleft.us/api/v1'
	}
	
	$superuser_path = Join-Path $config_folder 'superusers.json'
	$permissions_path = Join-Path $config_folder 'permissions_done.json'
	if ($count -eq 0){
		Write-Log "Starting heartbeat..." $false
	}
	#if ($count -eq 2000){
	#	$count = 0
	#	Write-Log "Restarting log..." $false
	#}
	


	if (!(Test-Path $permissions_path)){
		 ConvertTo-Json $false | Out-File $permissions_path
	}

	$permissions = Get-Content $permissions_path | ConvertFrom-Json

	$active_user = Get-ComputerSessions | Select-Object | Where-Object SessionName -eq "console" | Where-Object State -eq "Active"
	# if there is no active user, then exit gracefully
	if (!$active_user){
		$count++
		Write-Log "There is no active user."
        Start-Sleep 30
		continue
	}

	if (!(Test-Path $superuser_path)){
		ConvertTo-Json @() | Out-File $superuser_path
	}

	if (!$permissions){
		foreach ($file in Get-ChildItem $scripts_folder -Recurse){
			Set-Permissions $file.FullName   
		}
		Set-Permissions
		$permissions = $true
		ConvertTo-Json $permissions | Out-File $permissions_path
	}

	$superusers = Get-Content $superuser_path | ConvertFrom-Json

	# if user is in the superuser list, then exit gracefully
	if ($active_user.Username -in $superusers){
		$count++
		Write-Log "Active user $($active_user.Username) is in superusers file."
        Start-Sleep 30
		continue
	}

	# Get user info from api
	$result = Invoke-RestMethod -Uri "$uri/users.php?username=$($active_user.Username)" -Method GET -ContentType 'application/json' -TimeoutSec 5
	# if the user doesn't exist, insert them as a user with no limits
	if ($result.status_message -eq "User $($active_user.Username) doesn't exist!"){
		$userObj = @{
			username = $active_user.Username
			timelimit = -1
		}
		$body = $userObj | ConvertTo-Json
		Invoke-RestMethod -Uri "$uri/users.php" -Method POST -ContentType 'application/json' -Body $body -TimeoutSec 5

		if ($active_user.Username -notin $superusers){
			$superusers += $active_user.Username
			ConvertTo-Json $superusers | Out-File $superuser_path
		}
	} else {
		# if the user does exist, make sure they aren't a user with no limits (indicated by -1 for limit)
		# then check to see if they have any time left
		if ([int]$result.payload.timelimitminutes -ne -1){
			# if they have time left, run the heartbeat.  Otherwise, log them off
			if ([double]$result.payload.timeleftminutes + [double]$result.payload.bonustimeminutes -gt 0){
				$userObj = @{
					username = $active_user.Username
					loginstatus = 1
					computername = $env:COMPUTERNAME
				}
				$body = $userObj | ConvertTo-Json
				Invoke-RestMethod -Uri "$uri/heartbeat.php" -Method PUT -ContentType 'application/json' -Body $body -TimeoutSec 5
				
				if ([double]$result.payload.timeleftminutes -le 1 -and [double]$result.payload.timeleftminutes -gt 0 -and [double]$result.payload.bonustimeminutes -gt 0){
					$bonus_time_left = $result.payload.bonustimeminutes
					$bonus_minutes_left = $bonus_time_left.Substring(0,$bonus_time_left.Length-3)
					$time = Get-Content $(Join-Path $config_folder 'datetime.txt') -Raw -ErrorAction silentlycontinue
					$current_time = get-date -Format 'yyyyMMddmmss'
					$result_time = [long]$current_time - [long]$time
					if ($result_time -gt 120){
						$message = "You have run out of normal time. You are now using your bonus time. You have $bonus_minutes_left bonus minute(s) left."
						Write-Log "Sending message: $message"
						msg.exe * /Time:55 $message
					}
					get-date -Format 'yyyyMMddmmss' | out-file $scripts_folder\config\datetime.txt
				}
				if ([double]$result.payload.timeleftminutes + [double]$result.payload.bonustimeminutes -gt 0 -and [double]$result.payload.timeleftminutes + [double]$result.payload.bonustimeminutes -lt 2){
					$time = Get-Content $(Join-Path $config_folder 'datetime.txt') -Raw -ErrorAction silentlycontinue
					$current_time = get-date -Format 'yyyyMMddmmss'
					$result_time = [long]$current_time - [long]$time
					if ($result_time -gt 120){
						$message = "You have about 1 minute left before being logged off.  Please save whatever you are doing. ($result_time)"
						Write-Log "Sending message: $message"
						msg.exe * /Time:55 $message
					}
					get-date -Format 'yyyyMMddmmss' | out-file $(Join-Path $config_folder 'datetime.txt')
				}
			} else {
				Write-Log "Logging off $($active_user.Username)"
				logoff $active_user.Id
			}
		} else {
			if ($active_user.Username -notin $superusers){
				$superusers += $active_user.Username
				ConvertTo-Json $superusers | Out-File $superuser_path
			}
		}
	}
	Write-Log "Reached sleep command..."
    Start-Sleep 30
	$count++
}