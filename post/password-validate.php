<?php
$open = true;
require '../lib/site.inc.php';
$controller = new Sorry\PasswordValidateController($site, $_POST);
header("location: " . $controller->getRedirect());
exit;