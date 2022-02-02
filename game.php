<?php
$currentPage = 'game.php';
require 'lib/site.inc.php';
require 'lib/game.inc.php';
$view = new Sorry\GameView($site, $user, $game, $sorry, $turnName);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $view->head() ?>
</head>

<body>
<div class="content">
<?php echo $view->header(Sorry\Team::getString($sorry->getTeamTurn())); ?>

<form method='post' action='post/game.php'>
<?php
echo $view->presentContent();
?>
</form>
<?php echo $view->footer() ?>
</div>
</body>
</html>

