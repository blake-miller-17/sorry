<?php
$currentPage = 'post/game.php';
$noRedirect = true;
require '../lib/site.inc.php';
require '../lib/game.inc.php';

$controller = new Sorry\LobbyController($site, $user, $game, $_POST);
$root = $site->getRoot();
//phpinfo();
header("location: $root/" . $controller->getRedirect());
exit;