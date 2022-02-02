<?php


/**
 * Function to localize our site
 * @param $site The Site object
 */
return function(Sorry\Site $site) {
// Set the time zone
    date_default_timezone_set('America/Detroit');

    $site->setEmail('mill3231@cse.msu.edu');
    $site->setRoot('/~mill3231/project2');
    $site->dbConfigure('mysql:host=mysql-user.cse.msu.edu;dbname=mill3231',
        'mill3231',       // Database user
        'newpassword',     // Database password
        'test_sorry_');            // Table prefix
};