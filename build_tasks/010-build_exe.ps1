Install-Module ps2exe
Invoke-PS2EXE .\heartbeat.ps1 .\winlogonlimiter.exe -noConsole -noOutput -noError
# Compress-Archive winlogonlimiter.exe and config folder
$file_zip_name = "$($version_number)_winlogonlimiter.zip"
$file_hash = get-filehash -Algorithm sha256 .\$file_zip_name
"$($file_hash.Hash)  $file_zip_name`r`n" | Out-File "$($version_number)_SHA256SUMS"
