<?php
require 'lib/site.inc.php';
$view = new Sorry\GameOverView($site);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?php echo $view->head() ?>
</head>

<body>
<div class="content">
<?php
$winner = $_SESSION[Sorry\SessionNames::WINNER];
$color = $winner["color"];
echo $view->header(Sorry\Team::getString($color));
echo $view->presentImage();
echo $view->presentBody();
echo $view->footer();
?>
</div>
</body>