# Purpose: update the latest version number
param (
    $product = ''
)

$branch_label= $env:CI_COMMIT_BRANCH -replace "\W","_" # replace non-word characters with "_"
$version_branch = 'version_dev'
$project_name = $env:CI_PROJECT_NAME
if ($branch_label.StartsWith('release')){
    $version_branch = 'version_release'
}

$version_file_name = "version$product-$branch_label.txt"
# get version file
Write-Output 'Getting version...'
$method = 'GET'
$uri = "https://api.github.com/repos/segesmac/$project_name/contents/$version_file_name`?ref=$version_branch"
$headers = @{
    'Authorization' = "Bearer $env:GITHUB_PAT_WLL"
    'Accept' = 'application/vnd.github+json'
    'X-GitHub-Api-Version' = '2022-11-28'
}
Write-Output "Using this URI: $uri"
$result = Invoke-RestMethod -Uri $uri -Headers $headers -Method $method -SkipHttpErrorCheck # SkipHttpErrorCheck will send the error response to $result instead of erroring out
Write-Output "RESULT_VERSION:"
Write-Output $result
if ($result.status -eq 404) {
	Write-Output "$version_file_name file not found - skipping."
	$result.status = 200
}
if ($result.status -ge 400) {
	Write-Error "Failed Invoke-RestMethod"
	throw $result
}
$version_number = $null
if ($null -ne $result.name){
    $decode = [System.Convert]::FromBase64String($result.content)
    $version_number = [System.Text.Encoding]::UTF8.GetString($decode).Trim()
    $version_number_sha = $result.sha
}

# get version stub file
Write-Output 'Getting version stub...'
$build_version_stub = $env:BUILD_VERSION_STUB
if ($null -eq $build_version_stub){
    $build_version_stub = "0.0.0"
}
$uri = "https://api.github.com/repos/segesmac/$project_name/contents/version_stub$product.txt?ref=$version_branch"
$headers = @{
    'Authorization' = "Bearer $env:GITHUB_PAT_WLL"
    'Accept' = 'application/vnd.github+json'
    'X-GitHub-Api-Version' = '2022-11-28'
}
$result = Invoke-RestMethod -Uri $uri -Headers $headers -Method $method -SkipHttpErrorCheck # SkipHttpErrorCheck will send the error response to $result instead of erroring out
Write-Output "RESULT:"
Write-Output $result
if ($result.status -ge 400) {
        Write-Error "Failed Invoke-RestMethod"
        throw $result
}
if ($null -ne $result.name){
    $decode = [System.Convert]::FromBase64String($result.content)
    $build_version_stub = [System.Text.Encoding]::UTF8.GetString($decode).Trim()
}

if ($null -eq $version_number){
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
    Write-Output "Version number is currently set to $version_number and base is set to $version_base"
}

Write-Output "Version_base: $version_base"
Write-Output "Build_version_stub: $build_version_stub"
if ($version_base.StartsWith($build_version_stub)){
    # Increment the build number
    Write-Output 'Incrementing build number...'
    $new_build_number = [int]"$build_number" + 1
} else {
    # Set build number to 0
    Write-Output 'Setting build number to 0'
    $version_base = "$($build_version_stub.Trim())."
    $new_build_number = 0
}
# Update the uri and version number

$new_version_number = "$version_base$new_build_number"
Write-Output "Old version number: '$version_number' - New version number: '$new_version_number'"

# Setting the environment variables so that subsequent tasks will have the updated number
"BRANCH_LABEL=$branch_label" | Out-File "build.env" -Encoding utf8 -Append
"VERSION_NUMBER=$new_version_number" | Out-File "build.env" -Encoding utf8 -Append
if ($null -ne $version_number_sha){
    "VERSION_SHA=$version_number_sha" | Out-File "build.env" -Encoding utf8 -Append
}
Write-Output "Done!"
