# Purpose: update the latest version number
$branch_label= $env:CI_COMMIT_BRANCH -replace "\W","_" # replace non-word characters with "_"
$version_number_key = "$($branch_label)_BUILD_VERSION"
$create_new_variable = $false
$version_number_object = Get-ChildItem "env:" | Where { $_.Name -eq "$version_number_key" } | Select-Object -First 1 # Getting the first 1 in case there are multiple matches
$build_version_stub = $env:BUILD_VERSION_STUB
if ($version_number_object -eq $null){
	$create_new_variable = $true
	$version_base = ""
	$build_number = "0"
} else {
	$version_number = $version_number_object.Value
	# Get the build number
    $pattern = '^\d+\..*\d+\.'
    Write-Output 'Getting build number'
    $build_number = $version_number -replace $pattern
    # Get the version number base
    $m = $version_number | Select-String -Pattern $pattern
    $version_base = $m.Matches[0].Value
	Write-Output "Version number is currently set to $version_number"
}

if ($version_base.StartsWith($build_version_stub)){
    # Increment the build number
    Write-Output 'Incrementing build number...'
    $new_build_number = [int]"$build_number" + 1
} else {
    # Set build number to 0
    Write-Output 'Setting build number to 0'
    $version_base = "$build_version_stub."
    $new_build_number = 0
}
# Update the uri and version number

$new_version_number = "$version_base$new_build_number"
Write-Output "Old version number: '$version_number' - New version number: '$new_version_number'"
#$artifact_path = "$env:CI_PROJECT_DIR"
#$version_path = Join-Path $artifact_path "version.txt"
#$new_version_number | Out-File $version_path

$method = 'PUT'
$uri = "https://gitlab.com/api/v4/projects/$env:CI_PROJECT_ID/variables/$version_number_key"
if ($create_new_variable){
  $method = 'POST'
  $uri = "https://gitlab.com/api/v4/projects/$env:CI_PROJECT_ID/variables"
}

$data = @{        
    key = $version_number_key;
    value = $new_version_number;
};

$headers = @{'PRIVATE-TOKEN' = $env:ACCESS_TOKEN}
Write-Output "Running: Invoke-RestMethod -Uri $uri -Headers $headers -Method $method -Form $data"
Invoke-RestMethod -Uri $uri -Headers $headers -Method $method -Form $data

# Setting the environment variables so that subsequent tasks will have the updated number
"BRANCH_LABEL=$branch_label" | Out-File "build.env" -Encoding utf8 -Append
"VERSION_NUMBER=$new_version_number" | Out-File "build.env" -Encoding utf8 -Append
Write-Output "Done!"
