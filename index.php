<?php
$open = true;
require __DIR__ . "/vendor/autoload.php";
require 'lib/site.inc.php';

if ($user === null) {
    header('location: login.php');
} else {
    header('location: lobbies.php');
}