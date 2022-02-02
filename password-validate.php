<?php
$open = true;
require 'lib/site.inc.php';
$view = new Sorry\PasswordValidateView($site, $_GET);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $view->head(); ?>
</head>

<body>
<div class="content">
    <?php echo $view->header(); ?>
    <?php echo $view->presentContent(); ?>
    <?php echo $view->footer(); ?>
</div>

</body>
</html>