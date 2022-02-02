<?php
$currentPage = 'post/game.php';
$noRedirect = true;
require '../lib/site.inc.php';
require '../lib/game.inc.php';

$controller = new Sorry\GameController($site, $user, $game, $sorry, $_POST);
$root = $site->getRoot();
header("Location: $root/" . $controller->getPage());
exit;
