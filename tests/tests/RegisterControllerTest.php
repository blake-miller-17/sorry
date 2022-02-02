<?php

class RegisterControllerTest extends \PHPUnit\Framework\TestCase {
    private static $site;

    protected function setUp() {
        $users = new Sorry\Users(self::$site);
        $tableName = $users->getTableName();

        //passwords:
        //noob
        //journey
        //diverdown
        //pyromania1983!
        $sql = <<<SQL
delete from $tableName;
insert into $tableName(id, email, name, password, salt, gameid)
values (12, "noob@gmail.com", "noob", "c1715f912b916be0aa152a02d35b65d814b305260723a654533acc44993c2642", 
        "4qf^I7~O%rHqVomn", 420),
       (13, "journey@yahoo.com", "journeyrulez", "e9d0d5a650b157f43f207a8917e401653c6dc727ed1727e3fb0474fda08576ab", 
        "K~7ZeVO!4(kic2rG", 421),
       (14, "vanhalen@music.com", "vanhalen4life", "31d4927660dd518800c68cab76254a4c4921eb1eaf7edeaa44aa9129c5eca57b", 
        "nsUI9~W@+A6!rXpP", 420),
       (15, "defleppard@aol.com", "leppard123", "7108fc7beb60f392c24b09dcdde7a9c32fc44badaa67da2df32f245e371c4006", 
        "lDKt~W%DGLA$!dEZ", 421)
SQL;

        self::$site->pdo()->query($sql);
    }


    public static function setUpBeforeClass() {
        self::$site = new Sorry\Site();
        $localize  = require 'localize.inc.php';
        if(is_callable($localize)) {
            $localize(self::$site);
        }
    }

    public function test_construct() {
        $session = array();	// Fake session
        $root = self::$site->getRoot();

        // Valid register
        $controller = new Sorry\RegisterController(self::$site, $session,
            array("email" => "racecars9912@aol.com", "name" => "racecars9912", 'register' => ''));
        $this->assertInstanceOf('Sorry\RegisterController', $controller);

        $this->assertEquals("$root/", $controller->getRedirect());
        $this->assertNull($controller->getRegisterMessage());

        $controller = new Sorry\RegisterController(self::$site, $session,
            array("email" => "doge@dogecoin.com", "name" => "dogecoin", 'register' => ''));

        $this->assertEquals("$root/", $controller->getRedirect());
        $this->assertNull($controller->getRegisterMessage());


        // Email already registered
        $controller = new Sorry\RegisterController(self::$site, $session,
            array("email" => "racecars9912@aol.com", "name" => "racecars9912", 'register' => ''));

        $this->assertEquals("$root/register.php?e", $controller->getRedirect());
        $this->assertEquals("Email address already exists.", $controller->getRegisterMessage());

        $controller = new Sorry\RegisterController(self::$site, $session,
            array("email" => "defleppard@aol.com", "name" => "leppard123456789", 'register' => ''));

        $this->assertEquals("$root/register.php?e", $controller->getRedirect());
        $this->assertEquals("Email address already exists.", $controller->getRegisterMessage());

        // Name already registered
        $controller = new Sorry\RegisterController(self::$site, $session,
            array("email" => "unique@test.com", "name" => "racecars9912", 'register' => ''));

        $this->assertEquals("$root/register.php?e", $controller->getRedirect());
        $this->assertEquals("Name already exists.", $controller->getRegisterMessage());

        $controller = new Sorry\RegisterController(self::$site, $session,
            array("email" => "1984album@music.com", "name" => "vanhalen4life", 'register' => ''));

        $this->assertEquals("$root/register.php?e", $controller->getRedirect());
        $this->assertEquals("Name already exists.", $controller->getRegisterMessage());

    }
}