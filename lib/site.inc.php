<?php
/**
 * @file
 * A file loaded for all pages on the site.
 */
require __DIR__ . "/../vendor/autoload.php";

// Start the session system
session_start();

$site = new Sorry\Site();
$localize = require 'localize.inc.php';
if(is_callable($localize)) {
    $localize($site);
}

// Get the user that's logged in
if(!isset($_SESSION[Sorry\SessionNames::USER])) {
    $_SESSION[Sorry\SessionNames::USER] = null;
}
if ($_SESSION[Sorry\SessionNames::USER] !== null) {
    $_SESSION[Sorry\SessionNames::USER] = Sorry\Database::getUser($site, $_SESSION[Sorry\SessionNames::USER]->getId());
}
$user = $_SESSION[Sorry\SessionNames::USER];

// Ensure the winner of the game is in the session
if(!isset($_SESSION[Sorry\SessionNames::WINNER])) {
    $_SESSION[Sorry\SessionNames::WINNER] = array("color" => Sorry\Team::NONE, "user" => "");
}
$winner = $_SESSION[Sorry\SessionNames::WINNER];


// redirect if user is not logged in
if((!isset($open) || !$open) && $user === null) {
    $root = $site->getRoot();
    header("location: $root/");
    exit;
}