<?php
require_once('api.class.php');
print '<pre>';

// Initialisatie API connector
$apikey = '[hier API-sleutel invullen]';
$url = 'https://[hier jouw domeinnaam].gripp.com/public/api2.php';

$api = new com_gripp_API($apikey, $url);

$customernumber = 105781; // Bakker Van De Akker. Het klantnummer kan gevonden worden in het overzicht met relaties.

// Eerst moeten we het ID van de relatie achterhalen
$filters = array();
$filters[] = array(
    "field" => "company.customernumber",
    "operator" => "equals",
    "value" => $customernumber
);

// Pagineer en sorteer op verloopdatum
$options = array();

// Verzenden en resultaat verwerken
$responses = $api->company_getone($filters, $options);
$response = $responses[0]['result'];
$customer = $response['rows'][0];
if ($response['count'] == 0){
    die('Geen relatie gevonden met nummer '.$customernumber);
}
$customer_id = $customer['id'];



// Filter op openstaande facturen van Gripp B.V.
$filters = array();
$filters[] = array(
	"field" => "company.id",
	"operator" => "equals",
	"value" => $customer_id
);
$filters[] = array(
	"field" => "invoice.totalopeninclvat",
	"operator" => "notequals",
	"value" => "0"
);

// Pagineer en sorteer op verloopdatum
$options = array(
	"paging" => array(
		"firstresult" => 0, //begin bij zoekresultaat 0
		"maxresults" => 10  //toon maximaal 10 zoekresultaten
    ),
    "orderings" => array(
        array(
            "field" => "invoice.expirydate",
            "direction" => "asc"
        )
	)
);

// Verzenden en resultaat verwerken
$responses = $api->invoice_get($filters, $options);
$response = $responses[0]['result'];



echo '
<h1>Openstaande facturen</h1>
<table style="border: 1px solid grey;">
    <thead style="font-weight: bold;">
        <td width="70px">Status</td>
        <td width="100px">Nummer</td>
        <td width="100px">Datum</td>
        <td width="140px">Factuurbedrag</td>
        <td width="140px">Nog open</td>
        <td width="140px">Bekijk online</td>
    </thead>

';

//print_r($response['rows']);

foreach($response['rows'] as $row){
	$dateobj = $row['date'] ? new \DateTime($row['date']['date']) : null;
    echo '
        <tr>
            <td><div style="background-color: #cc0000; padding: 2px; border-radius: 5px; color: white;">Open</div></td>
            <td>'.$row['number'].'</td>
            <td>'.(is_object($dateobj) ? $dateobj->format('d-m-Y') : '').'</td>
            <td>'.$row['totalinclvat'].'</td>
            <td>'.$row['totalopeninclvat'].'</td>
            <td><a target="_blank" href="'.$row['viewonlineurl'].'">Openen</a></td>
        </tr>
    ';
}

echo '</table>';


// Filter op betaalde facturen van Gripp B.V.
$filters = array();
$filters[] = array(
	"field" => "company.id",
	"operator" => "equals",
	"value" => $customer_id
);
$filters[] = array(
	"field" => "invoice.totalopeninclvat",
	"operator" => "equals",
	"value" => "0"
);

// Verzenden en resultaat verwerken
$responses = $api->invoice_getone($filters, $options); // we gebruiken dezelfde opties ($options) voor paginering en sortering
$response = $responses[0]['result'];

echo '
<h1>Betaalde facturen</h1>
<table style="border: 1px solid grey;">
    <thead style="font-weight: bold;">
        <td width="70px">Status</td>
        <td width="100px">Nummer</td>
        <td width="100px">Datum</td>
        <td width="140px">Factuurbedrag</td>
        <td width="140px">Nog open</td>
        <td width="140px">Bekijk online</td>
    </thead>

';

//print_r($response);

foreach($response['rows'] as $row){
	$dateobj = $row['date'] ? new \DateTime($row['date']['date']) : null;

    echo '
        <tr>
            <td><div style="background-color: yellowgreen; padding: 2px; border-radius: 5px; color: white;">Voldaan</div></td>
            <td>'.$row['number'].'</td>
            <td>'.(is_object($dateobj) ? $dateobj->format('d-m-Y') : '').'</td>
            <td>'.$row['totalincldiscountinclvat'].'</td>
            <td>'.$row['totalopeninclvat'].'</td>
            <td><a target="_blank" href="'.$row['viewonlineurl'].'">Openen</a></td>
        </tr>
    ';
}

echo '</table>';
?>