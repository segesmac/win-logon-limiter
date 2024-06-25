# Defaults
$serviceName = "WinLogOnLimiter"
$binariesPath = $(Join-Path $PSScriptRoot "..\binaries\")
#$toolsPath = (Split-Path -Parent $MyInvocation.MyCommand.Definition)
$wrapperExe = "$env:ChocolateyInstall\bin\nssm.exe"
$serviceInstallationDirectory = "C:\Scripts"
$serviceLogDirectory = "$serviceInstallationDirectory\logs"
$serviceConfigDirectory = "$serviceInstallationDirectory\config"
$serviceDataDirectory = "$serviceInstallationDirectory\data"

$packageParameters = $env:chocolateyPackageParameters
if (-not ($packageParameters)) {
  $packageParameters = ""
  Write-Debug "No Package Parameters Passed in"
}
$service = Get-Service WinLogOnLimiter -ErrorAction SilentlyContinue
if ($null -ne $service){
	if ($service.Status -ne 'Stopped'){
		Stop-Service WinLogOnLimiter
	}
}

# winlogonlimiter related variables
$app_version = '__VERSION__'
$sourcePath = $(Join-Path $binariesPath "$($app_version)_winlogonlimiter.zip")

# Create Service Directories
Write-Host "Creating $serviceLogDirectory"
New-Item -ItemType directory -Path "$serviceLogDirectory" -ErrorAction SilentlyContinue | Out-Null
Write-Host "Creating $serviceConfigDirectory"
New-Item -ItemType directory -Path "$serviceConfigDirectory" -ErrorAction SilentlyContinue | Out-Null

# Unzip and move winlogonlimiter
Get-ChocolateyUnzip  $sourcePath "$serviceInstallationDirectory"

# Create event log source
# User -Force to avoid "A key at this path already exists" exception. Overwrite not an issue since key is not further modified
$registryPath = 'HKLM:\SYSTEM\CurrentControlSet\services\eventlog\Application'
New-Item -Path $registryPath -Name winlogonlimiter -Force | Out-Null
# Set EventMessageFile value
Set-ItemProperty $registryPath\winlogonlimiter EventMessageFile "C:\Windows\Microsoft.NET\Framework64\v2.0.50727\EventLogMessages.dll" | Out-Null

# Set up task scheduler for log rotation
#$logrotate = ('%SYSTEMROOT%\System32\forfiles.exe /p \"{0}\" /s /m *.* /c \"cmd /c Del @path\" /d -7' -f "$serviceLogDirectory")
#SchTasks.exe /Create /SC DAILY /TN ""winlogonlimiterLogrotate"" /TR ""$($logrotate)"" /ST 09:00 /F | Out-Null

# Set up task scheduler for log rotation. Only works for Powershell 4 or Server 2012R2 so this block can replace
# using SchTasks.exe for registering services once machines have retired the older version of PS or upgraded to 2012R2
$command = ('$now = Get-Date; dir "{0}" | where {{$_.LastWriteTime -le $now.AddDays(-7)}} | del -whatif' -f $serviceLogDirectory)
$action = New-ScheduledTaskAction -Execute 'Powershell.exe' -Argument "-NoProfile -WindowStyle Hidden -command $($command)"
$trigger = New-ScheduledTaskTrigger -Daily -At 9am
Register-ScheduledTask -Action $action -Trigger $trigger -TaskName "winlogonlimiterLogrotate" -Description "Log rotation for winlogonlimiter" -Force

#Uninstall service if it already exists. Stops the service first if it's running
$service = Get-Service $serviceName -ErrorAction SilentlyContinue
if ($service) {
  Write-Host "Uninstalling existing service"
  if($service.Status -ne "Stopped" -and $service.Status -ne "Stopping") {
    Write-Host "Stopping winlogonlimiter process ..."
    $service.Stop();
  }

  $service.WaitForStatus("Stopped", (New-TimeSpan -Minutes 1));
  if($service.Status -ne "Stopped") {
    throw "$serviceName could not be stopped within the allotted timespan.  Stop the service and try again."
  }

  $service = Get-WmiObject -Class Win32_Service -Filter "Name='$serviceName'"
  $service.delete() | Out-Null
}

Write-Host "Installing service: $serviceName"
# Install the service
$powershellPath = ( Get-Command powershell ).Source
$service_ps1 = join-path $serviceInstallationDirectory "heartbeat.ps1"
$service_args = '-ExecutionPolicy Bypass -NoProfile -File "{0}"' -f $service_ps1
& $wrapperExe install $serviceName "$powershellPath" "$service_args agent -ui -config-dir=$serviceConfigDirectory -data-dir=$serviceDataDirectory $packageParameters" | Out-Null
& $wrapperExe set $serviceName AppStdout "$serviceLogDirectory\winlogonlimiter-output.log" | Out-Null
& $wrapperExe set $serviceName AppStderr "$serviceLogDirectory\winlogonlimiter-error.log" | Out-Null
& $wrapperExe set $serviceName AppRotateBytes 10485760 | Out-Null
& $wrapperExe set $serviceName AppRotateFiles 1 | Out-Null
& $wrapperExe set $serviceName AppRotateOnline 1 | Out-Null
& $wrapperExe set $serviceName Application $service_exe | Out-Null
& $wrapperExe set $serviceName AppDirectory $serviceInstallationDirectory | Out-Null
& $wrapperExe set $serviceName ObjectName LocalSystem | Out-Null
& $wrapperExe set $serviceName AppRestartDelay 0 | Out-Null
& $wrapperExe set $serviceName AppStopMethodSkip 0 | Out-Null
& $wrapperExe set $serviceName AppStopMethodConsole 1500 | Out-Null
& $wrapperExe set $serviceName DisplayName "WinLogOnLimiter" | Out-Null
& $wrapperExe set $serviceName Description  "This service limits a user's time logged on to this computer." | Out-Null
& $wrapperExe set $serviceName Start SERVICE_AUTO_START | Out-Null

# When nssm fully supports Rotate/Post Event hooks
# $command = ('$now = Get-Date; dir "{0}" | where {{$_.LastWriteTime -le $now.AddDays(-7)}} | del -whatif' -f $serviceLogDirectory)
# $action = ("Powershell.exe -NoProfile -WindowStyle Hidden -command '$({{0}})'" -f $command)
# & $wrapperExe set winlogonlimiter AppEvents "Rotate/Post" $action | Out-Null

# Restart service on failure natively via Windows sc. There is a memory leak if service restart is performed via NSSM
# The NSSM configuration will set the default behavior of NSSM to stop the service if
# winlogonlimiter fails (for example, unable to resolve cluster) and end the nssm.exe and winlogonlimiter.exe process.
# The sc configuration will set Recovery under the winlogonlimiter service properties such that a new instance will be started on failure,
# spawning new nssm.exe and winlogonlimiter.exe processes. In short, nothing changed from a functionality perspective (the service will
# still attempt to restart on failure) but this method kills the nssm.exe process thus avoiding memory hog.
& $wrapperExe set $serviceName AppExit Default Exit | Out-Null
cmd.exe /c "sc failure $serviceName reset= 0 actions= restart/5000" | Out-Null

# Let this call to Get-Service throw if the service does not exist
$service = Get-Service $serviceName
if($service.Status -ne "Stopped" -and $service.Status -ne "Stopping") {
  $service.Stop()
}

$service.WaitForStatus("Stopped", (New-TimeSpan -Minutes 1));
& $wrapperExe start $serviceName | Out-Null

Write-Host "Installed service: $serviceName"
