<?php
$open = true;
require 'lib/site.inc.php';
$view = new Sorry\InstructionsView($site);
?>

<!DOCTYPE>
<html lang="en">

<head>
    <?php echo $view->head() ?>
</head>
<body>
<div class="content">
<?php
echo $view->header();
echo $view->presentBody();
echo $view->footer();
?>
</div>
</body>
</html>
