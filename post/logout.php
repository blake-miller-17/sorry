<?php
require '../lib/site.inc.php';
unset($_SESSION[Sorry\SessionNames::USER]);
header("location: " . $site->getRoot());