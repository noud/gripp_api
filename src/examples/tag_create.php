<?php
require_once('api.class.php');
$token = '#APITOKEN#'; //Your API token
$url = 'https://api.gripp.com/public/api3.php';
$API = new com_gripp_API($token, $url);

$fields = array(
    'name' => "My Tag"
);
$response = $API->tag_create($fields);

print '<pre>';
print_r($response);
?>
