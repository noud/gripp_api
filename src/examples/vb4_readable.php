<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

//includen van de API connector.
require_once('api.class.php');
$apikey = 'HIER DE KEY';
$url = 'https://jouwdomein.whiteworks.nl/public/api2.php';

//Instantieren van de WhiteWorks API connector
$API = new nl_whiteworks_API($apikey, $url);

//Dit zijn de teammembers. Ook al is het er maar 1, deze moet wel meegegeven worden als een array.
//Voor de duidelijkheid is het handig om de varialenaam in een meervoudsvorm te kiezen.
$werknemers = array(
    100 //medewerker_id
);

//Het zelfde geldt voor de tags:
$tags = array(
    4, 5 //tags met id 4 en 5
);


//De opdracht heeft een of meerdere projectlines. Deze zitten in een array.
$projectlines = array();

//Per opdrachtregel voeg je een projectline toe aan de array met projectlines.
//Het is een associatieve array met keys en values.
$projectlines[] = array(
    //De volgorde in de database. Dit bepaald de sortering van de opdrachtregels.
    '_ordering' => 10,

    //Het regeltype. 1=groepregel, 2=normale regel
    'rowtype' => 1,

    //Dit is alleen van toepassing voor groepregels. Op normale regels heeft dit geen invloed. Geeft aan of de onderliggende regels van een groep verborgen of getoond worden.
    'hidedetails' => true,

    //Het aantal
    'amount' => 1.23,

    //De ID van de eenheid. Deze kun je in de instellingen vinden op het tabblad 'Eenheden'.
    'unit' => 3,

    //De verkoopprijs per stuk
    'sellingprice' => 2.34,

    //Het kortingspercentage
    'discount' => 5,

    //Het veld 'Hoe te facturen. Je hebt hier de keuze uit FIXED | COSTING | BUDGETED | NONBILLABLE
    'invoicebasis' => "FIXED",

    //Het ID van het product. Het product ID is een intern ID en is niet direct in de applicatie af te lezen. Je kunt eenmalig een lijstje ophalen en afdrukken via de 2e voorbeeldfunctie hieronder.
    'product' => 42,

    //Het veldje 'toevoeging onderwerp'
    'additionalsubject' => "testtest",

    //Omschrijving
    'description' => 'Dit is de omschrijving',

    //BTW-id. Deze is af te lezen in de instellingen > BTW-tarieven
    'vat' => 27,

    //Dit veld is een verwijzing naar de bovenliggende opdracht. Dit is alleen van toepassing als je de offerprojectline_create() functie aanroep. In dit voorbeeld is het niet nodig omdat we de regels direct meegegeven met een opdracht.
    //'offerprojectbase' = 12345

    //De inkoopprijs
    'buyingprice' => 1.23
);


//Opstellen van de data voor een opdracht
$project = array(
    //Het sjabloonset. Dit verwijst naar het gekozen sjabloonset en is af te lezen in de instellingen
    'templateset' => 1,

    //De onderstaande velden spreken voor zich
    'name' => "name",
    'phase' => 1,
    'deadline' => "2016-11-11",
    'company' => 95595,
    'startdate' => "2016-11-11",
    'deliverydate' => "2016-11-11",
    'enddate' => "2016-11-11",
    'addhoursspecification' => true,
    'description' => "De omschrijving van de opdracht",
    'clientreference' => "Referentie van de klant",
    'archived' => false,
    'archivedon' => null, //alleen setten als het gearchiveer is. Bijv: "2016-11-14",

    //Dit zijn arrays die we hierboven hebben opgebouwd
    'tags' => $tags,
    'employees' => $werknemers,
    'projectlines' => $projectlines
);

//Na het aanroepen wordt de $response gevuld.
$response = $API->project_create($project);
print '<pre>';
print_r($response);

//Het ID van het aangemaakte project is nu op te halen middels
echo "Nieuwe opdracht aangemaakt met ID = ".$response[0]['result']['recordid'];


//===============================================
//Producten ophalen:

//we gaan de producten niet filteren, maar er moet wel altijd een 'filters' parameter meegegeven worden.
//in dit geval is de array leeg aangezien we geen filtering toepassen.
$filters = array();

//sinds v2 van de API is de $options['paging'] verplicht.
$options = array(
    'paging' => array(
        'firstresult' => 0,
        'maxresults' => '10'
    )
);

//Het lijstje met producten en hun ID's zijn op te vragen met:
$response = $API->project_get($filters, $options);

//De lijst met producten
print_r($response);

$watchdog = 5; //Een watchdog om te voorkomen dat de while loop hieronder maximaal 5 keer wordt aangeroepen.

//Wanneer er meer dan 100 producten zijn, dan moet de call nogmaals gemaakt worden. In het resultaat krijg je of er nog meer items in de opgevraagde collectie zitten. Dit kun je zien aan het veld more_items_in_collection.

while ($response[0]['result']['more_items_in_collection'] && $watchdog){
    $options = array(
        'paging' => array(
            'firstresult' => $response[0]['result']['next_start'], //hier stellen we de volgende in.
            'maxresults' => '10'
        )
    );
    $response = $API->project_get($filters, $options);
    print_r($response);

    $watchdog--;
}

//Het is aan te raden deze productenlijst 1x te maken, en in het vervolg de ID's te gebruiken van de producten die je in de opdracht wilt gebruiken.
?>