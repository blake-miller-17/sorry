<?php
$open = true;
require 'lib/site.inc.php';
$view = new Sorry\RegisterView($site, $_SESSION, $_GET);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $view->head() ?>
</head>

<body>
<div class="content">
<?php
    echo $view->header();
    echo $view->displayError();
    echo $view->presentContent();
    echo $view->footer();
?>
</div>
</body>