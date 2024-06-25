# Set the base API endpoint
$apiUrl = "https://chocolatey.segesman.us"
$feedName = "chocolatey"
$apiKey = $env:CHOCO_API_KEY
$folderPath = "../choco_package"

$nupkgFiles = Get-ChildItem -Path $folderPath -Filter *.nupkg

foreach ($nupkgFile in $nupkgFiles) {
    $fileFullPath = $nupkgFile.FullName
    $packageName = $nupkgFile.BaseName

    $fullApiUrl = "$apiUrl/api/packages/$feedName/upload/$packageName.nupkg"

    $result = Invoke-WebRequest -Uri $fullApiUrl -Headers @{"X-ApiKey" = $apiKey} -Method POST -InFile $fileFullPath

    if ($result.StatusCode -eq 200) {
        Write-Host "Package '$packageName' uploaded successfully."
    } else {
        Write-Host "Failed to upload package '$packageName'. Status code: $($result.StatusCode)"
    }
}