<?php

include '../src/CssPurger.php';
include '../src/Vendors/Bootstrap.php';

use JBSNewMedia\CssPurger\Vendors\Bootstrap;

$file = realpath('./assets/css/bootstrap.css');
$cssService = new Bootstrap($file);
$cssService->loadContent();
$cssService->prepareContent();
$cssService->runContent();

$cssService->addSelector(':root');
$cssService->addSelector('[data-bs-theme=light]');
$cssService->addSelector('[data-bs-theme=dark]');
$cssService->addSelector('body');
$cssService->addSelector('h1');
$cssService->addSelector('.h1');
$cssService->addSelector('.container');
$cssService->addSelector('.pt-3');
$cssService->addSelector('.pb-3');
$cssService->addSelector('.alert');
$cssService->addSelector('.alert-danger');
$cssService->addSelector('.btn');
//$cssService->addSelector('.btn:hover'); <- this is not possible because the CSS Purger does not support the :hover selector. It will be adding bei "btn" selector
$cssService->addSelector('.btn-primary');

$file_css = './assets/css/bootstrap-purged.css';
$file_min = './assets/css/bootstrap-purged.min.css';
$parsed_css = './assets/parsed/bootstrap-purged.css';
$parsed_min = './assets/parsed/bootstrap-purged.min.css';

file_put_contents($file_css, $cssService->generateOutput(false));
file_put_contents($file_min, $cssService->generateOutput());

function getHash($file) {
    return hash_file('sha256', $file);
}

$hash_css = getHash($file_css);
$hash_parsed_css = getHash($parsed_css);
$hash_min = getHash($file_min);
$hash_parsed_min = getHash($parsed_min);

echo "Checking CSS files:\n";
echo "Current: " . $hash_css . "\n";
echo "Parsed:  " . $hash_parsed_css . "\n";
if ($hash_css === $hash_parsed_css) {
    echo "CSS files match! (SHA256)\n";
} else {
    echo "CSS files DO NOT match!\n";
}

echo "\nChecking Minified CSS files:\n";
echo "Current: " . $hash_min . "\n";
echo "Parsed:  " . $hash_parsed_min . "\n";
if ($hash_min === $hash_parsed_min) {
    echo "Minified CSS files match! (SHA256)\n";
} else {
    echo "Minified CSS files DO NOT match!\n";
}
