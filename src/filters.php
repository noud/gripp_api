<?php

// Filter example 1:
// Get all companies with a specific tag
$tag_ids = array(1, 2, 3);
$filters = array(
    array(
        "field" => "company.tags",
        "operator" => "in",
        "value" => $tag_id
    )
);

// Filter example 2:
// Get all companies that are between 2 specified id's
$filters = array(
    array(
        "field" => "company.id",
        "operator" => "between",
        "value" => 1,
        "value2" => 5
    )
);

// Filter example 3:
// Get all the not null values (does not work on empty strings)
$filters = array(
    array(
        "field" => "company.foundationdate",
        "operator" => "isnotnull",
        "value" => ""
    )
);

// Filter example 4:
// Get companies with a name that ends with 'B.V.' (More info: SQL LIKE OPERATOR)
$filters = array(
    array(
        "field" => "company.companyname",
        "operator" => "like",
        "value" => "% B.V."
    )
);