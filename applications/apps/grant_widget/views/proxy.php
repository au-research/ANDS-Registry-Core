<?php
$url = $_GET['address'];

$curl = curl_init();

  // Setup headers - I used the same headers from Firefox version 2.0.0.6
  // below was split up because php.net said the line was too long. :/
  $header[0] = "Accept: application/grant+json";
  curl_setopt($curl, CURLOPT_URL, $url);

  curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

  curl_setopt($curl, CURLOPT_AUTOREFERER, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_TIMEOUT, 10);

  $json = curl_exec($curl); // execute the curl command
  curl_close($curl); // close the connection

  echo $json; // and finally, return $html 



?>