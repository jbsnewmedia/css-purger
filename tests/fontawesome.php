<?php

include '../src/CssPurger.php';
include '../src/Vendors/FontAwesome.php';

use JBSNewMedia\CssPurger\Vendors\FontAwesome;

$file = realpath('./assets/css/fontawesome.css');
$cssService = new FontAwesome($file);
$cssService->loadContent();
$cssService->prepareContent();
$cssService->runContent();

// Basis-Selektoren werden automatisch durch FontAwesome::prepareContent() hinzugefügt.
// Hier fügen wir spezifische Icon-Klassen hinzu, die im HTML verwendet werden würden.
$cssService->addSelector('.fa-house');
$cssService->addSelector('.fa-user');
$cssService->addSelector('.fa-check');
$cssService->addSelector('.fa-lg'); // Eine Utility-Klasse

$purgedCss = $cssService->generateOutput(false);
file_put_contents('./assets/css/fontawesome-purged.css', $purgedCss);
file_put_contents('./assets/css/fontawesome-purged.min.css', $cssService->generateOutput());

echo "FontAwesome purging completed.\n";

// Kurze Verifikation
if (strpos($purgedCss, '.fa-house') !== false) {
    echo "SUCCESS: .fa-house found in purged CSS.\n";
} else {
    echo "FAILED: .fa-house NOT found in purged CSS.\n";
}

if (strpos($purgedCss, '.fa-solid') !== false) {
    echo "SUCCESS: .fa-solid found in purged CSS.\n";
} else {
    echo "FAILED: .fa-solid NOT found in purged CSS.\n";
}
