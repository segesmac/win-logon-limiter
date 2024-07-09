<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once(__DIR__ . '/../../vendor/autoload.php');
# Get headers to extract "authorization"
$headers = apache_request_headers();
#var_dump($headers);
if (! preg_match('/Bearer\s(\S+)/', $headers['authorization'], $matches)) {
    header('HTTP/1.0 400 Bad Request');
    echo 'Token not found in request';
    exit;
}
// JWT token will be in array number 1 based on the match above
$jwt = $matches[1];
if (! $jwt) {
    // No token was able to be extracted from the authorization header
    header('HTTP/1.0 400 Bad Request');
    exit;
}
// Getting the secret key from file
$secretKey  = file_get_contents(getenv("WLL_JWT_SECRET_FILE"));
// Adding a leeway of 60 seconds
JWT::$leeway += 60;
try {
    $token = JWT::decode((string)$jwt, new Key($secretKey, 'HS512'));
    if (null != $token->temp_admin_startdate) {
        $temp_admin_datediff = date_diff(date_create($token->temp_admin_startdate),date_create())->format("%i");
        if ($temp_admin_datediff <= $token->temp_admin_minutes){
            $token->is_tempadmin = 1;
        } else {
            $token->is_tempadmin = 0;
        }
    }
} catch (Firebase\JWT\ExpiredException $e) {
    header('HTTP/1.1 401 Unauthorized');
    echo 'Expired token';
    exit;
} catch (Firebase\JWT\SignatureInvalidException $e) {
    header('HTTP/1.1 401 Unauthorized');
    echo 'Signature Verification failed';
    exit;
}
$now = new DateTimeImmutable();
$serverName = getenv("WEB_DOMAIN_NAME");
// not valid if iss doesn't match the servername
if ($token->iss !== $serverName ||
    $token->nbf > $now->getTimestamp())
{
    header('HTTP/1.1 401 Unauthorized');
    exit;
}
