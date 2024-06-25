# Set the base API endpoint
$apiUrl = "http://192.168.1.220"
$feedName = "chocolatey"
$apiKey = $env:CHOCO_API_KEY
$folderPath = "../choco_package"

$nupkgFiles = Get-ChildItem -Path $folderPath -Filter *.nupkg

foreach ($nupkgFile in $nupkgFiles) {
    $fileName = $nupkgFile.Name
    #$packageName = $nupkgFile.BaseName
    Write-Output "Attempting to push '$folderPath/$fileName'"
    nuget push "$folderPath/$fileName" -ApiKey "$apiKey" -Source "$apiUrl/nuget/$feedName/" -NonInteractive -Verbosity detailed
    #if ($result.StatusCode -eq 200) {
    #    Write-Host "Package '$packageName' uploaded successfully."
    #} else {
    #    Write-Host "Failed to upload package '$packageName'. Status code: $($result.StatusCode)"
    #}
}

