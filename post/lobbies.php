<?php
require '../lib/site.inc.php';

$controller = new Sorry\LobbiesController($site, $user, $_POST);
$root = $site->getRoot();
header("location: $root/" . $controller->getRedirect());
exit;