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

file_put_contents('./assets/css/bootstrap-purged.css', $cssService->generateOutput(false));
file_put_contents('./assets/css/bootstrap-purged.min.css', $cssService->generateOutput());
