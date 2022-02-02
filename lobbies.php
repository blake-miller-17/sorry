<?php
$currentPage = "lobbies.php";
$noGame = true;
require 'lib/site.inc.php';
require 'lib/game.inc.php';
$view = new Sorry\LobbiesView($site, $_GET);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $view->head() ?>
</head>
<body>
<div class="content">
    <?php echo $view->header(); ?>
    <?php echo $view->presentBody() ?>
    <?php echo $view->footer(); ?>
</div>
</body>
</html>