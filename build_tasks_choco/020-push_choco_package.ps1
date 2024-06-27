# Set the base API endpoint
$apiUrl = $env:CHOCO_URL
$apiKey = $env:CHOCO_API_KEY
$folderPath = "../choco_package"

$nupkgFiles = Get-ChildItem -Path $folderPath -Filter *.nupkg

foreach ($nupkgFile in $nupkgFiles) {
    $fileName = $nupkgFile.Name
    #$packageName = $nupkgFile.BaseName
    Write-Output "Attempting to push '$folderPath/$fileName'"
    dotnet nuget push "$folderPath/$fileName" -k "$apiKey" -s "$apiUrl"
    #if ($result.StatusCode -eq 200) {
    #    Write-Host "Package '$packageName' uploaded successfully."
    #} else {
    #    Write-Host "Failed to upload package '$packageName'. Status code: $($result.StatusCode)"
    #}
}

