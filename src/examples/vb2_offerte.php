<?php
require_once('api.class.php');
print '<pre>';

// Initialisatie API connector
$apikey = '[hier API-sleutel invullen]';
$url = 'https://[hier jouw domeinnaam].gripp.com/public/api2.php';

$api = new com_gripp_API($apikey, $url);

// Variabelen; in de praktijk zijn deze afkomstig van een webformulier waarmee
// men een offerte aanvraagt voor de levering van een x aantal terrastegels
$company = "Bakker Van De Akker";
$email = "mail@example.com";
$aantalTegels = 50;

// Enkele velden die afhankelijk zijn van de instellingen in Gripp
// De juiste ID's kunnen in de instellingen afgelezen worden bij het overzicht met Taaktypes en Taakfases.
$fase_id = 1;
$sjabloon_id = 10;
$productnummer = 20062; //Het productnummer kan worden gevonden in het overzicht met producten.

// We gaan er in dit voorbeeld vanuit dat het bedrijf
// nog niet bij ons bekend is en we het aan moeten maken
// Als alternatief zou een relatie opgezocht kunnen worden op het klantnummer.
$fields = array(
    "companyname" => "$company",
    "email" => "$email",
    "relationtype" => "COMPANY",
	"invoicesendby" => "EMAIL", //verplicht
	"invoiceemail" => "$email", //verplicht
);
$responses = $api->company_create($fields);
$response = $responses[0]['result'];
$company_id = $response["recordid"];


// Voor het opstellen van de offerte moeten we de
// inkoopprijs, verkoopprijs en product_id van een terrastegel (productnummer 20030) weten
$filters = array();
$filters[] = array(
		"field" => "product.number",
		"operator" => "equals",
		"value" => $productnummer //dit is het nummer van terrastegels
);
$responses = $api->product_getone($filters);
$response = $responses[0]['result'];
$product_id = $response['rows'][0]['id'];
$sellingprice = $response['rows'][0]['sellingprice'];
$byingprice = $response['rows'][0]['buyingprice'];

// Offerte maken
$fields = array(
	"company" => $company_id,
	"template" => $sjabloon_id,
	"name" => "Terrastegels",
	"offerlines" => array(
						array(
							"product" => $product_id,
							"amount" => $aantalTegels,
							"vat" => 27,
							"invoicebasis" => "FIXED",
							"sellingprice" => $sellingprice,
							"discount" => 0,
							"buyingprice" => $byingprice
						)
					),
	"phase" => $fase_id
);
$responses = $api->offer_create($fields);
$response = $responses[0]['result'];
$offer_id = $response["recordid"];

// Offerte verzenden per e-mail

// URL opvragen
$filters = array();
$filters[] = array(
	"field" => "offer.id",
	"operator" => "equals",
	"value" => $offer_id
);
$responses = $api->offer_getone($filters);
$response = $responses[0]['result'];
$offer_url = $response['rows'][0]['viewonlineurl'];

// E-mail verzenden
$to      = $email;
$subject = 'Offerte van Ons Bedrijf';
$message = "De door u aangevraagde offerte kunt u bekijken via de volgende link: \r\n $offer_url";
$headers = 	'From: service@onsbedrijf.nl' . "\r\n";
mail($to, $subject, $message, $headers);
// [foutafhandeling]

// Status offerte wijzigen als e-mail succesvol is verstuurd
$fields = array(
	"status" => "SENT"
);
$responses = $api->offer_update($offer_id, $fields);
$response = $responses[0]['result'];

echo "Done!";
?>