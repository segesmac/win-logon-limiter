$count = 0
$global:last_message = ''
while ($true){

	Function Write-Log {
		param (
		  $message,
		  $append = $true,
		  $logfile = "$PSScriptRoot\logs\winlogonlimiter_log.txt"
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

	Function Get-LoggedInUser {
	<#
	.SYNOPSIS
		This will check the specified machine to see all users who are logged on.
		For updated help and examples refer to -Online version.
	 
	.NOTES
		Name: Get-LoggedInUser
		Author: Paul Contreras
		Version: 3.0
		DateUpdated: 2021-Sep-21
	 
	.LINK
		https://thesysadminchannel.com/get-logged-in-users-using-powershell/ -
		For updated help and examples refer to -Online version.
	 
	.PARAMETER ComputerName
		Specify a computername to see which users are logged into it.  If no computers are specified, it will default to the local computer.
	 
	.PARAMETER UserName
		If the specified username is found logged into a machine, it will display it in the output.
	 
	.EXAMPLE
		Get-LoggedInUser -ComputerName Server01
		Display all the users that are logged in server01
	 
	.EXAMPLE
		Get-LoggedInUser -ComputerName Server01, Server02 -UserName jsmith
		Display if the user, jsmith, is logged into server01 and/or server02
	 
	 
	#>
	 
		[CmdletBinding()]
			param(
				[Parameter(
					Mandatory = $false,
					ValueFromPipeline = $true,
					ValueFromPipelineByPropertyName = $true,
					Position=0
				)]
				[string[]] $ComputerName = $env:COMPUTERNAME,
	 
	 
				[Parameter(
					Mandatory = $false
				)]
				[Alias("SamAccountName")]
				[string]   $UserName
			)
	 
		BEGIN {}
	 
		PROCESS {
			foreach ($Computer in $ComputerName) {
				try {
					$Computer = $Computer.ToUpper()
					$SessionList = quser /Server:$Computer 2>$null
					if ($SessionList) {
						$UserInfo = foreach ($Session in ($SessionList | select -Skip 1)) {
							$Session = $Session.ToString().trim() -replace '\s+', ' ' -replace '>', ''
							if ($Session.Split(' ')[3] -eq 'Active') {
								[PSCustomObject]@{
									ComputerName = $Computer
									UserName     = $session.Split(' ')[0]
									SessionName  = $session.Split(' ')[1]
									SessionID    = $Session.Split(' ')[2]
									SessionState = $Session.Split(' ')[3]
									IdleTime     = $Session.Split(' ')[4]
									LogonTime    = $session.Split(' ')[5, 6, 7] -as [string] -as [datetime]
								}
							} else {
								[PSCustomObject]@{
									ComputerName = $Computer
									UserName     = $session.Split(' ')[0]
									SessionName  = $null
									SessionID    = $Session.Split(' ')[1]
									SessionState = 'Disconnected'
									IdleTime     = $Session.Split(' ')[3]
									LogonTime    = $session.Split(' ')[4, 5, 6] -as [string] -as [datetime]
								}
							}
						}
	 
						if ($PSBoundParameters.ContainsKey('Username')) {
							$UserInfo | Where-Object {$_.UserName -eq $UserName}
						  } else {
							$UserInfo | Sort-Object LogonTime
						}
					}
				} catch {
					Write-Log $_.Exception.Message
	 
				}
			}
		}
	 
		END {}
	}


	function Set-Permissions {

		Param(
			[Parameter(
				Mandatory = $false,
				Position = 0,
				ValueFromPipeline = $True)]
				[string]$scripts_folder = $PSScriptRoot
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

	$scripts_folder = $PSScriptRoot # The folder in which heartbeat.ps1 resides
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

	$active_user = Get-LoggedInUser | Select-Object | Where-Object SessionName -eq "console" | Where-Object SessionState -eq "Active"
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
				logoff $active_user.SessionID
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
