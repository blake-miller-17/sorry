<?php
$open = true;		// Can be accessed when not logged in
require '../lib/site.inc.php';
$controller = new Sorry\RegisterController($site, $_SESSION, $_POST);
header("location: " . $controller->getRedirect());
exit;