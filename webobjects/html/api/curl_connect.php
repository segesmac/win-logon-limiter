<?php
function curl_api($method, $url, $data = NULL){
   $curl = curl_init();
   switch ($method){
      case 'POST':
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'x-api-key: ' . file_get_contents(getenv('WLL_JC_API_KEY_FILE')),
        ));
        if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        break;
      case 'PUT':
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'x-api-key: ' . file_get_contents(getenv('WLL_JC_API_KEY_FILE')),
        ));
        if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        break;
      case 'GET':
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'x-api-key: ' . file_get_contents(getenv('WLL_JC_API_KEY_FILE')),
        ));
        break;
      default:
         if ($data)
            $url = sprintf("%s?%s", $url, http_build_query($data));
   }
   // OPTIONS:
   curl_setopt($curl, CURLOPT_URL, $url);
   
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
   //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
   // EXECUTE:
   $result = curl_exec($curl);
   if(!$result){die("Connection Failure");}
   curl_close($curl);
   return $result;
}