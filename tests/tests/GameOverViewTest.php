<?php

class GameOverViewTest extends \PHPUnit\Framework\TestCase {
    private static $site;

    public static function setUpBeforeClass() {
        self::$site = new Sorry\Site();
        $localize  = require 'localize.inc.php';
        if(is_callable($localize)) {
            $localize(self::$site);
        }

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        } else if(session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
            session_start();
        }

        $_SESSION[Sorry\SessionNames::WINNER] = Sorry\Team::NONE;
    }

    public function test_construct() {
        $gameOverView = new Sorry\GameOverView(self::$site);

        $this->assertInstanceOf('Sorry\GameOverView', $gameOverView);
    }

    public function test_presentImage() {
        //
        // Yellow winner test
        //
        $gameOverView = new Sorry\GameOverView(self::$site);
        $_SESSION[Sorry\SessionNames::WINNER] = ['color' => Sorry\Team::YELLOW, 'user' => Sorry\Team::getString(Sorry\Team::YELLOW)];

        $status = $gameOverView->presentImage();
        $expected =  '<div class="gameover"><p><img src="images/gameover_yellow.png" height="768" width="1024" alt="Yellow Won!"></p></div>';

        $this->assertContains($expected, $status);

        //
        // Green winner test
        //

        $gameOverView = new Sorry\GameOverView(self::$site);
        $_SESSION[Sorry\SessionNames::WINNER] = ['color' => Sorry\Team::GREEN, 'user' => Sorry\Team::getString(Sorry\Team::GREEN)];

        $status = $gameOverView->presentImage();
        $expected =  '<div class="gameover"><p><img src="images/gameover_green.png" height="768" width="1024" alt="Green Won!"></p></div>';

        $this->assertContains($expected, $status);

        //
        // Red winner test
        //

        $gameOverView = new Sorry\GameOverView(self::$site);
        $_SESSION[Sorry\SessionNames::WINNER] = ['color' => Sorry\Team::RED, 'user' => Sorry\Team::getString(Sorry\Team::RED)];

        $status = $gameOverView->presentImage();
        $expected =  '<div class="gameover"><p><img src="images/gameover_red.png" height="768" width="1024" alt="Red Won!"></p></div>';

        $this->assertContains($expected, $status);

        //
        // Blue winner test
        //

        $gameOverView = new Sorry\GameOverView(self::$site);
        $_SESSION[Sorry\SessionNames::WINNER] = ['color' => Sorry\Team::BLUE, 'user' => Sorry\Team::getString(Sorry\Team::BLUE)];

        $status = $gameOverView->presentImage();
        $expected =  '<div class="gameover"><p><img src="images/gameover_blue.png" height="768" width="1024" alt="Blue Won!"></p></div>';

        $this->assertContains($expected, $status);

        //
        // No winner (Tie) test
        //

        $gameOverView = new Sorry\GameOverView(self::$site);
        $_SESSION[Sorry\SessionNames::WINNER] = ['color' => Sorry\Team::NONE, 'user' => Sorry\Team::getString(Sorry\Team::NONE)];

        $status = $gameOverView->presentImage();
        $expected =  '<div class="gameover"><p><img src="images/gameover_tie.png" height="768" width="1024" alt="It is a tie!"></p></div>';

        $this->assertContains($expected, $status);

    }
}