<?php

class GameViewTest extends \PHPUnit\Framework\TestCase {
    private static $site;
    private static $user;
    private static $game;

    public static function setUpBeforeClass() {
        self::$site = new Sorry\Site();
        $localize  = require 'localize.inc.php';
        if(is_callable($localize)) {
            $localize(self::$site);
        }

        self::$user = new Sorry\User([
            'id' => 99,
            'email' => '',
            'name' => '',
            'password' => '',
            'gameId' => ''
        ]);

        self::$game = new Sorry\Game([
            'id' => 0,
            'name' => "Unique Name",
            'status' => 0,
            'state' => serialize(new Sorry\Sorry([Sorry\Team::GREEN, Sorry\Team::YELLOW, Sorry\Team::BLUE])),
            'host' => 0,
            'yellow' => 0,
            'green' => 0,
            'red' => 0,
            'blue' => 0,
            'acknowledge' => 0
        ]);
    }

    public function test_construct() {
        $sorry = unserialize(self::$game->getState());
        $gameView = new Sorry\GameView(self::$site, self::$user, self::$game, $sorry, Sorry\Team::getString($sorry->getTeamTurn()));

        $this->assertInstanceOf('Sorry\GameView', $gameView);
    }

    /**
     * Testing the presentBoard, since the other
     */
    public function test_presentBoard() {
        $sorry = new Sorry\Sorry([Sorry\Team::GREEN, Sorry\Team::YELLOW]); // Sorry object
        $gameView = new Sorry\GameView(self::$site, self::$user, self::$game, $sorry, Sorry\Team::getString($sorry->getTeamTurn())); //GameView Object

        // The initial state of the game
        $board = $gameView->presentBoard();

        //Beginning of the html
        $html = '<div class = \'game\'><div class=\'board\'>';
        $this->assertContains($html, $board);

        // Deck spaces
        $this->assertContains("deckImage",$gameView->display_cell(85));
        $this->assertContains("highlighted",$gameView->display_cell(86));
        $this->assertContains("highlighted",$gameView->display_cell(87));
        $this->assertContains("highlighted",$gameView->display_cell(101));
        $this->assertContains("highlighted",$gameView->display_cell(102));
        $this->assertContains("highlighted",$gameView->display_cell(103));
        $this->assertContains("highlighted",$gameView->display_cell(133));
        $this->assertContains("highlighted",$gameView->display_cell(134));
        $this->assertContains("highlighted",$gameView->display_cell(135));

        //Green pieces - this game has green and yellow pieces
        $this->assertContains( "GreenPiece", $gameView->display_cell(60));
        $this->assertContains( "GreenPiece", $gameView->display_cell(62));
        $this->assertContains( "GreenPiece", $gameView->display_cell(92));
        $this->assertContains( "GreenPiece", $gameView->display_cell(94));

        //Yellow pieces - this game has green and yellow pieces
        $this->assertContains( "YellowPiece", $gameView->display_cell(19));
        $this->assertContains( "YellowPiece", $gameView->display_cell(21));
        $this->assertContains( "YellowPiece", $gameView->display_cell(51));
        $this->assertContains( "YellowPiece", $gameView->display_cell(53));

        //Red start piece should be notClickable
        $this->assertContains("notClickable", $gameView->display_cell(202));
        $this->assertContains("notClickable", $gameView->display_cell(204));
        $this->assertContains("notClickable", $gameView->display_cell(234));
        $this->assertContains("notClickable", $gameView->display_cell(236));

        //Blue start piece should be notClickable
        $this->assertContains("notClickable", $gameView->display_cell(161));
        $this->assertContains("notClickable", $gameView->display_cell(163));
        $this->assertContains("notClickable", $gameView->display_cell(193));
        $this->assertContains("notClickable", $gameView->display_cell(195));


        $sorry = new Sorry\Sorry([Sorry\Team::RED, Sorry\Team::BLUE]); // Sorry object
        $gameView = new Sorry\GameView(self::$site, self::$user, self::$game, $sorry, Sorry\Team::getString($sorry->getTeamTurn())); //GameView Object

        //Green and Yellow are unclickable, red and blue are on the board
        $this->assertContains( "notClickable", $gameView->display_cell(60));
        $this->assertContains( "notClickable", $gameView->display_cell(62));
        $this->assertContains( "notClickable", $gameView->display_cell(92));
        $this->assertContains( "notClickable", $gameView->display_cell(94));

        $this->assertContains("BluePiece", $gameView->display_cell(161));
        $this->assertContains( "BluePiece", $gameView->display_cell(163));
        $this->assertContains( "BluePiece", $gameView->display_cell(193));
        $this->assertContains( "BluePiece", $gameView->display_cell(195));

        $this->assertContains( "RedPiece", $gameView->display_cell(202));
        $this->assertContains( "RedPiece", $gameView->display_cell(204));
        $this->assertContains( "RedPiece", $gameView->display_cell(234));
        $this->assertContains( "RedPiece", $gameView->display_cell(236));

        $this->assertContains("notClickable", $gameView->display_cell(19));
        $this->assertContains("notClickable", $gameView->display_cell(21));
        $this->assertContains("notClickable", $gameView->display_cell(51));
        $this->assertContains("notClickable", $gameView->display_cell(53));


        $sorry = new Sorry\Sorry([Sorry\Team::RED, Sorry\Team::BLUE, Sorry\Team::GREEN]); // Sorry object
        $gameView = new Sorry\GameView(self::$site, self::$user, self::$game, $sorry, Sorry\Team::getString($sorry->getTeamTurn())); //GameView Object

        //Green pieces - this game has green, red and yellow pieces
        $this->assertContains( "GreenPiece", $gameView->display_cell(60));
        $this->assertContains( "GreenPiece", $gameView->display_cell(62));
        $this->assertContains( "GreenPiece", $gameView->display_cell(92));
        $this->assertContains( "GreenPiece", $gameView->display_cell(94));

        //Blue pieces - this game has green, red and yellow pieces
        $this->assertContains("BluePiece", $gameView->display_cell(161));
        $this->assertContains( "BluePiece", $gameView->display_cell(163));
        $this->assertContains( "BluePiece", $gameView->display_cell(193));
        $this->assertContains( "BluePiece", $gameView->display_cell(195));

        //Red pieces - this game has green, red and yellow pieces
        $this->assertContains( "RedPiece", $gameView->display_cell(202));
        $this->assertContains( "RedPiece", $gameView->display_cell(204));
        $this->assertContains( "RedPiece", $gameView->display_cell(234));
        $this->assertContains( "RedPiece", $gameView->display_cell(236));

        //Yellow start piece should be notClickable
        $this->assertContains("notClickable", $gameView->display_cell(19));
        $this->assertContains("notClickable", $gameView->display_cell(21));
        $this->assertContains("notClickable", $gameView->display_cell(51));
        $this->assertContains("notClickable", $gameView->display_cell(53));

        $sorry = new Sorry\Sorry([Sorry\Team::RED, Sorry\Team::BLUE, Sorry\Team::YELLOW, Sorry\Team::GREEN]); // Sorry object
        $gameView = new Sorry\GameView(self::$site, self::$user, self::$game, $sorry, Sorry\Team::getString($sorry->getTeamTurn())); //GameView Object

        //All teams are present on the board at the beginning for this test

        //Green pieces
        $this->assertContains( "GreenPiece", $gameView->display_cell(60));
        $this->assertContains( "GreenPiece", $gameView->display_cell(62));
        $this->assertContains( "GreenPiece", $gameView->display_cell(92));
        $this->assertContains( "GreenPiece", $gameView->display_cell(94));

        //Blue pieces
        $this->assertContains("BluePiece", $gameView->display_cell(161));
        $this->assertContains( "BluePiece", $gameView->display_cell(163));
        $this->assertContains( "BluePiece", $gameView->display_cell(193));
        $this->assertContains( "BluePiece", $gameView->display_cell(195));

        //Red pieces
        $this->assertContains( "RedPiece", $gameView->display_cell(202));
        $this->assertContains( "RedPiece", $gameView->display_cell(204));
        $this->assertContains( "RedPiece", $gameView->display_cell(234));
        $this->assertContains( "RedPiece", $gameView->display_cell(236));

        //Yellow pieces
        $this->assertContains( "YellowPiece", $gameView->display_cell(19));
        $this->assertContains( "YellowPiece", $gameView->display_cell(21));
        $this->assertContains( "YellowPiece", $gameView->display_cell(51));
        $this->assertContains( "YellowPiece", $gameView->display_cell(53));
    }


    public function test_formatPieceState() {
        $sorry = new Sorry\Sorry([Sorry\Team::GREEN, Sorry\Team::YELLOW, Sorry\Team::BLUE]); // Sorry object
        $gameView = new Sorry\GameView(self::$site, self::$user, self::$game, $sorry, Sorry\Team::getString($sorry->getTeamTurn())); //GameView Object

        $homes = [
            Sorry\Team::YELLOW => 2,
            Sorry\Team::GREEN => 3,
            Sorry\Team::RED => 3,
            Sorry\Team::BLUE => 2
        ];

        $starts = [
            Sorry\Team::YELLOW => 2,
            Sorry\Team::GREEN => 3,
            Sorry\Team::RED => 3,
            Sorry\Team::BLUE => 2
        ];

        $activePieces = [[Sorry\Board::STATE_INDEX_KEY => new Sorry\BoardIndex(19, 0),
                        Sorry\Board::STATE_TEAM_KEY => Sorry\Team::GREEN],
                        [Sorry\Board::STATE_INDEX_KEY => new Sorry\BoardIndex(2, 0),
                        Sorry\Board::STATE_TEAM_KEY => Sorry\Team::RED],
                        [Sorry\Board::STATE_INDEX_KEY => new Sorry\BoardIndex(34, 0),
                        Sorry\Board::STATE_TEAM_KEY => Sorry\Team::BLUE],
                        [Sorry\Board::STATE_INDEX_KEY => new Sorry\BoardIndex(58, 2),
                        Sorry\Board::STATE_TEAM_KEY => Sorry\Team::YELLOW]];

        $display = $gameView->formatPieceState($activePieces, $starts, $homes);

        $this->assertTrue(count($display) > 0);

        // Actives
        $this->assertArrayHasKey(143, $display);
        $this->assertEquals(Sorry\Team::GREEN, $display[143]);
        $this->assertArrayHasKey(6, $display);
        $this->assertEquals(Sorry\Team::RED, $display[6]);
        $this->assertArrayHasKey(247, $display);
        $this->assertEquals(Sorry\Team::BLUE, $display[247]);
        $this->assertArrayHasKey(34, $display);
        $this->assertEquals(Sorry\Team::YELLOW, $display[34]);

        // Starts
        $this->assertArrayHasKey(19, $display);
        $this->assertEquals(Sorry\Team::YELLOW, $display[19]);
        $this->assertArrayHasKey(21, $display);
        $this->assertArrayHasKey(60, $display);
        $this->assertArrayHasKey(62, $display);
        $this->assertArrayHasKey(92, $display);
        $this->assertArrayHasKey(202, $display);
        $this->assertArrayHasKey(161, $display);

        // Homes
        $this->assertArrayHasKey(97, $display);
        $this->assertEquals(Sorry\Team::YELLOW, $display[97]);
        $this->assertArrayHasKey(23, $display);
        $this->assertArrayHasKey(124, $display);
        $this->assertArrayHasKey(198, $display);

        // Not in starts
        $this->assertArrayNotHasKey(53, $display);
        $this->assertArrayNotHasKey(94, $display);
        $this->assertArrayNotHasKey(236, $display);
        $this->assertArrayNotHasKey(195, $display);

        // Not in homes
        $this->assertArrayNotHasKey(131, $display);
        $this->assertArrayNotHasKey(57, $display);
        $this->assertArrayNotHasKey(158, $display);
        $this->assertArrayNotHasKey(232, $display);
    }

    public function test_formatClickableSpaces() {
        $sorry = new Sorry\Sorry([Sorry\Team::GREEN, Sorry\Team::YELLOW, Sorry\Team::BLUE]); // Sorry object
        $gameView = new Sorry\GameView(self::$site, self::$user, self::$game, $sorry, Sorry\Team::getString($sorry->getTeamTurn())); //GameView Object

        //
        // No clickable indices
        //

        $clickableIndices = [];
        $clickableSpaces = $gameView->formatClickableSpaces($clickableIndices);

        $this->assertCount(0, $clickableSpaces);


        //
        // Simple clickableIndices test
        //

        // Contains: Normal perimeter, home, start and safe zone indexes
        $clickableIndices = [new Sorry\BoardIndex(19, 0),
            new Sorry\BoardIndex(13, 6),
            new Sorry\BoardIndex(45, 1),
            new Sorry\BoardIndex(58, 3)
            ];

        $clickableSpaces = $gameView->formatClickableSpaces($clickableIndices);

        $this->assertCount(20, $clickableSpaces);
        $this->assertTrue(in_array(50, $clickableSpaces));
        $this->assertTrue(in_array(143, $clickableSpaces));
        $this->assertTrue(in_array(25, $clickableSpaces));
        $this->assertTrue(in_array(39, $clickableSpaces));


        //
        // Start index
        //

        $clickableIndices = [new Sorry\BoardIndex(45, 1)];

        $clickableSpaces = $gameView->formatClickableSpaces($clickableIndices);
        $this->assertCount(9, $clickableSpaces);
        $this->assertEquals(Sorry\Conversions::START_GRIDS[Sorry\Team::BLUE], $clickableSpaces);


        //
        // Home index
        //

        $clickableIndices = [new Sorry\BoardIndex(13, 6)];
        $clickableSpaces = $gameView->formatClickableSpaces($clickableIndices);
        $this->assertCount(9, $clickableSpaces);
        $this->assertEquals(Sorry\Conversions::HOME_GRIDS[Sorry\Team::GREEN], $clickableSpaces);

        //
        // Safe space
        //

        $clickableIndices = [new Sorry\BoardIndex(58, 3)];
        $clickableSpaces = $gameView->formatClickableSpaces($clickableIndices);
        $this->assertCount(1, $clickableSpaces);
        $this->assertEquals(50, $clickableSpaces[0]);

    }

    public function test_formatSelectedSpace() {
        $sorry = new Sorry\Sorry([Sorry\Team::GREEN, Sorry\Team::YELLOW, Sorry\Team::BLUE]); // Sorry object
        $gameView = new Sorry\GameView(self::$site, self::$user, self::$game, $sorry, Sorry\Team::getString($sorry->getTeamTurn())); //GameView Object

        //
        // Normal perimeter space
        //

        $boardIndex = new Sorry\BoardIndex(19, 0);
        $selectedSpaces = $gameView->formatSelectedSpace($boardIndex);

        $this->assertNotNull($selectedSpaces);
        $this->assertNotEquals([], $selectedSpaces);
        $this->assertCount(1, $selectedSpaces);
        $this->assertEquals(143, $selectedSpaces[0]);

        //
        // Home space (Green)
        //

        $boardIndex = new Sorry\BoardIndex(13, 6);
        $selectedSpaces = $gameView->formatSelectedSpace($boardIndex);

        $this->assertNotNull($selectedSpaces);
        $this->assertNotEquals([], $selectedSpaces);
        $this->assertCount(9, $selectedSpaces);
        $this->assertEquals(Sorry\Conversions::HOME_GRIDS[Sorry\Team::GREEN], $selectedSpaces);

        //
        // Start space (Blue)
        //

        $boardIndex = new Sorry\BoardIndex(45, 1);
        $selectedSpaces = $gameView->formatSelectedSpace($boardIndex);

        $this->assertNotNull($selectedSpaces);
        $this->assertNotEquals([], $selectedSpaces);
        $this->assertCount(9, $selectedSpaces);
        $this->assertEquals(Sorry\Conversions::START_GRIDS[Sorry\Team::BLUE], $selectedSpaces);

        //
        // Safe space (Yellow)
        //

        $boardIndex = new Sorry\BoardIndex(58, 3);
        $selectedSpaces = $gameView->formatSelectedSpace($boardIndex);

        $this->assertNotNull($selectedSpaces);
        $this->assertNotEquals([], $selectedSpaces);
        $this->assertCount(1, $selectedSpaces);
        $this->assertEquals(50, $selectedSpaces[0]);

    }

    public function test_formatDeckSpaces() {
        $sorry = new Sorry\Sorry([Sorry\Team::GREEN, Sorry\Team::YELLOW, Sorry\Team::BLUE]); // Sorry object
        $gameView = new Sorry\GameView(self::$site, self::$user, self::$game, $sorry, Sorry\Team::getString($sorry->getTeamTurn())); //GameView Object

        $deckSpaces = $gameView->formatDeckSpaces(true);

        // Size of array should be 12 for deck spaces if it can be clickable
        $this->assertCount(12, $deckSpaces);
        $this->assertEquals(Sorry\GameView::DECK_PILE, $deckSpaces);

        $deckSpaces = $gameView->formatDeckSpaces(false);

        // Size of array should be 0 if deck cannot be drawn from
        $this->assertCount(0, $deckSpaces);
        $this->assertFalse(in_array(86, $deckSpaces));
        $this->assertFalse(in_array(118, $deckSpaces));
        $this->assertFalse(in_array(135, $deckSpaces));
    }

    public function test_presentMessage() {
        $sorry = new Sorry\Sorry([Sorry\Team::YELLOW]); //Sorry Object
        $gameView =  new Sorry\GameView(self::$site, self::$user, self::$game, $sorry, Sorry\Team::getString($sorry->getTeamTurn())); // GameView Object

        $this->assertEquals("<p class='message'>&nbsp;</p>", $gameView->presentMessage());

//        $gameView->message = "Whats up";
//        $this->assertEquals("<p class='message'>Whats up</p>", $gameView->presentMessage());

    }
}