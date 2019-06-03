<?php
require_once('api.class.php');
$token = '#APITOKEN#'; //Your API token
$url = 'https://api.gripp.com/public/api3.php';
$API = new com_gripp_API($token, $url);

//batch processing, 1 request to server, 1 serverthread, fast!
$API->setBatchmode(true);
for($i = 0; $i < 10; $i++){
    $fields = array(
        'name' => "My Tag ".$i
    );
    $API->tag_create($fields);
}
$responses = $API->run();
print '<pre>';
print_r($responses);
?>