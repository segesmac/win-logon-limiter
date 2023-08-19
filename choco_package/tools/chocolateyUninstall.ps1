$service = Get-Service "winlogonlimiter" -ErrorAction SilentlyContinue

if ($service) {
	if ($service.Status -eq "Running") {
		Write-Host "Stopping winlogonlimiter process ..."
		net stop winlogonlimiter | Out-Null
	}

	$service = Get-WmiObject -Class Win32_Service -Filter "Name='winlogonlimiter'"
	$service.delete() | Out-Null
}

SchTasks.exe /Delete /F /TN "ConsulLogrotate" 2>&1 | Out-Null

Write-Host "Removing C:\Scripts\ ..."
takeown /f "C:\Scripts\" /a /r /d Y | Out-Null
icacls "C:\Scripts" /grant administrators:F /t | Out-Null
Remove-Item -Path "C:\Scripts\" -Force -Recurse -ErrorAction SilentlyContinue | Out-Null
