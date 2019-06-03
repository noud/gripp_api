<?php
require_once('api.class.php');
print '<pre>';

// Initialisatie API connector
$apikey = '[hier API-sleutel invullen]';
$url = 'https://domeinnaam.gripp.com/public/api2.php';

$api = new com_gripp_API($apikey, $url);

// Variabelen; in de praktijk zijn deze afkomstig van een webformulier
$date = date('Y-m-d');
$telnr = '06 12345678';
$bedrijfsnaam = 'Uw Warme Bakker';
$naam = 'Dirk de Jong';

// Enkele velden die afhankelijk zijn van de instellingen in Gripp
// De juiste ID's kunnen in de instellingen afgelezen worden bij het overzicht met Taaktypes en Taakfases.
$taakfase = 1;
$taaktype = 1;

// Terugbeltaak maken
$fields = array(
	'company' => 103216, //the ID of the company. Not the company number.
    'type' => $taaktype,
    'content' => "Terugbelafspraak: $bedrijfsnaam, $naam", //onderwerpregel
	'description' => "$telnr, $bedrijfsnaam, $naam",
    'phase' => $taakfase,
	'date' => $date
);
$responses = $api->task_create($fields);
$response = $responses[0]['result'];

print_r($response);
?>