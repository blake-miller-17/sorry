<?php
//$open = true;
$currentPage = 'lobby.php';
require 'lib/site.inc.php';
require 'lib/game.inc.php';

$view = new Sorry\LobbyView($site, $game->getId(), $user);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $view->head() ?>
</head>

<body>
<div class="content">
<?php echo $view->header() ?>
<?php echo $view->presentContent(); ?>
<?php echo $view->footer() ?>
</div>
</body>