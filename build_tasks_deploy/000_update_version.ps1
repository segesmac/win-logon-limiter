# Purpose: update the latest version number
param (
    $product = ''
)

$branch_label= $env:BRANCH_LABEL
$version_branch = 'version_dev'
$project_name = $env:CI_PROJECT_NAME
if ($branch_label.StartsWith('release')){
    $version_branch = 'version_release'
}

$version_file_name = "version$product-$branch_label.txt"

# Set Headers
$headers = @{
    'Authorization' = "Bearer $env:GITHUB_PAT_WLL"
    'Accept' = 'application/vnd.github+json'
    'X-GitHub-Api-Version' = '2022-11-28'
}

# Convert VERSION_NUMBER to base64
$Bytes = [System.Text.Encoding]::UTF8.GetBytes($env:VERSION_NUMBER)
$version_number_base64 = [Convert]::ToBase64String($Bytes)


$data = @{
    message = "Updating version number to $env:VERSION_NUMBER";
    content = $version_number_base64;
    branch = $version_branch;
};
# If this is true, we should be updating an existing file
if ($null -ne $env:VERSION_SHA){
    $data = @{
        message = "Updating version number to $env:VERSION_NUMBER";
        content = $version_number_base64;
        sha = $env:VERSION_SHA;
        branch = $version_branch;
    };
}
$json_data = ConvertTo-Json $data

$method = 'PUT'
$uri = "https://api.github.com/repos/segesmac/$project_name/contents/$version_file_name"

Write-Output "Running: Invoke-RestMethod -Uri $uri -Headers $headers -Method $method -Form $data"
$result = Invoke-RestMethod -Uri $uri -Headers $headers -Method $method -Body $json_data

Write-Output $result
