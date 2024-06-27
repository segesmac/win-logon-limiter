$choco_dir = Join-Path $PSScriptRoot '../choco_package'
Set-Location $choco_dir
# Replace __VERSION__ in all files
(Get-Content winlogonlimiter.nuspec).Replace('__VERSION__', $env:VERSION_NUMBER) | Set-Content winlogonlimiter.nuspec
(Get-Content tools/chocolateyInstall.ps1).Replace('__VERSION__', $env:VERSION_NUMBER) | Set-Content tools/chocolateyInstall.ps1
$choco_zip_folder = Join-Path '/' 'choco_zip'
New-Item -ItemType Directory $choco_zip_folder -Force
$binaries_folder = Join-Path $choco_dir "binaries"
New-Item -ItemType Directory $binaries_folder -Force
Copy-Item ../clientobjects/scripts/heartbeat.ps1 $choco_zip_folder/heartbeat.ps1
Copy-Item -Recurse ../clientobjects/scripts/config $choco_zip_folder/
Write-Output "Zipping $choco_zip_folder/* to $binaries_folder/$($env:VERSION_NUMBER)_winlogonlimiter.zip"
Compress-Archive -Path "$choco_zip_folder/*" -DestinationPath "$binaries_folder/$($env:VERSION_NUMBER)_winlogonlimiter.zip"
$file_hash = Get-FileHash -Algorithm SHA256 "$binaries_folder/$($env:VERSION_NUMBER)_winlogonlimiter.zip"
"$($file_hash.Hash)  $($env:VERSION_NUMBER)_winlogonlimiter.zip" | Out-File "$binaries_folder/$($env:VERSION_NUMBER)_SHA256SUMS"

nuget pack