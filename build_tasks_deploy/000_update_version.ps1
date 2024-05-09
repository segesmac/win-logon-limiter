# Purpose: update the latest version number
$branch_label= $env:CI_COMMIT_BRANCH -replace "\W","_" # replace non-word characters with "_"
$version_branch = 'version_dev'
$project_name = $env:CI_PROJECT_NAME
$temp_path = "/tmp/$project_name"
if ($branch_label.StartsWith('release')){
    $version_branch = 'version_release'
}
# get version file
$method = 'GET'
$uri = "https://api.github.com/repos/segesmac/win-logon-limiter/contents/version.txt?ref=$version_branch"
$headers = @{
    'Authorization' = "Bearer $env:GITHUB_PAT_WLL"
    'Accept' = 'application/vnd.github+json'
    'X-GitHub-Api-Version' = '2022-11-28'
}
$result = Invoke-RestMethod -Uri $uri -Headers $headers -Method $method -SkipHttpErrorCheck # SkipHttpErrorCheck will send the error response to $result instead of erroring out
$version_number = $null
if ($result.name -ne $null){
    $decode = [System.Convert]::FromBase64String($result.content)
    $version_number = [System.Text.Encoding]::UTF8.GetString($decode)
}

# get version stub file
$build_version_stub = $env:BUILD_VERSION_STUB
if ($build_version_stub -eq $null){
    $build_version_stub = "0.0.0"
}
$uri = "https://api.github.com/repos/segesmac/win-logon-limiter/contents/version_stub.txt?ref=$version_branch"
$headers = @{
    'Authorization' = "Bearer $env:GITHUB_PAT_WLL"
    'Accept' = 'application/vnd.github+json'
    'X-GitHub-Api-Version' = '2022-11-28'
}
$result = Invoke-RestMethod -Uri $uri -Headers $headers -Method $method -SkipHttpErrorCheck # SkipHttpErrorCheck will send the error response to $result instead of erroring out
if ($result.name -ne $null){
    $decode = [System.Convert]::FromBase64String($result.content)
    $build_version_stub = [System.Text.Encoding]::UTF8.GetString($decode)
}

$create_new_variable = $false

if ($version_number -eq $null){
    $create_new_variable = $true
    $version_base = ''
    $build_number = '0'
} else {
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

# Setting the environment variables so that subsequent tasks will have the updated number
"BRANCH_LABEL=$branch_label" | Out-File "build.env" -Encoding utf8 -Append
"VERSION_NUMBER=$new_version_number" | Out-File "build.env" -Encoding utf8 -Append
Write-Output "Done!"
