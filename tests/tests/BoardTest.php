<?php

class BoardTest extends \PHPUnit\Framework\TestCase {
    public function test_construct() {
        // Test correct type
        $board = new Sorry\Board([]);
        $this->assertInstanceOf('Sorry\Board', $board);

        // Test inputting teams
        $board = new Sorry\Board([Sorry\Team::GREEN, Sorry\Team::BLUE, Sorry\Team::NONE]);
        $this->assertEquals(0, $board->getStarts()[Sorry\Team::YELLOW]);
        $this->assertEquals(Sorry\Board::PIECES_PER_TEAM, $board->getStarts()[Sorry\Team::GREEN]);
        $this->assertEquals(0, $board->getStarts()[Sorry\Team::RED]);
        $this->assertEquals(Sorry\Board::PIECES_PER_TEAM, $board->getStarts()[Sorry\Team::BLUE]);
    }

    public function test_getClickableIndices() {
        // Should be no clickable indices because no card is set
        $board = new Sorry\Board([]);
        $this->assertEquals([], $board->getClickableIndices(0, null, null, 1, 0, []));

        // If there's a drawn card and a selected index, return should be selected merged with possible moves
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);
        $card = new Sorry\Card(Sorry\CardType::ONE);
        $indexHome = new Sorry\BoardIndex(0, 1);    // Index to click
        $indexOut = new Sorry\BoardIndex(0, 0);     // Index outside of Home
        $this->assertEquals([$indexHome, $indexOut], $board->getClickableIndices(0, $indexHome, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));

        // If there's a drawn card but no selected index, test that the start zone can be clicked with a 1
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);
        $card = new Sorry\Card(Sorry\CardType::ONE);
        $indexHome = new Sorry\BoardIndex(0, 1);    // Index to click
        $this->assertEquals([$indexHome], $board->getClickableIndices(0, null, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));

        // If there's a drawn card but no selected index, test that the start zone cannot be clicked with a 5
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);
        $card = new Sorry\Card(Sorry\CardType::FIVE);
        $this->assertEquals([], $board->getClickableIndices(0, null, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));

        // Start a piece
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);
        $board->start(Sorry\Team::YELLOW);
        // If there's a drawn card but no selected index, test that you can select a piece on the board with a 5
        $card = new Sorry\Card(Sorry\CardType::FIVE);
        $indexOut = new Sorry\BoardIndex(0, 0);     // Index outside of Home
        $this->assertEquals([$indexOut], $board->getClickableIndices(0, null, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));
        // If there's a drawn card but and a selected index, test that you can select a piece on the board with a 5 or the places it can move
        $indexFiveAhead = new \Sorry\BoardIndex(5, 0);
        $this->assertEquals([$indexOut, $indexFiveAhead], $board->getClickableIndices(0, $indexOut, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));

        // Start a blue piece
        $board->start(Sorry\Team::BLUE);
        // Check that this blue can swap with the Yellow that's out
        $card = new Sorry\Card(Sorry\CardType::ELEVEN);
        $blueOut = new Sorry\BoardIndex(45, 0);
        $blueElevenAhead = new Sorry\BoardIndex(56, 0);
        $this->assertEquals([$blueOut], $board->getClickableIndices(3, null, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));
        $this->assertEquals([$blueOut,$blueElevenAhead, $indexOut], $board->getClickableIndices(3, $blueOut, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));

        // Check that the yellow can move to any of the next 7 spaces with a 7
        $card = new Sorry\Card(Sorry\CardType::SEVEN);
        $this->assertEquals([$indexOut,
            new \Sorry\BoardIndex(1, 0),
            new \Sorry\BoardIndex(2, 0),
            new \Sorry\BoardIndex(3, 0),
            new \Sorry\BoardIndex(4, 0),
            new \Sorry\BoardIndex(5, 0),
            new \Sorry\BoardIndex(6, 0),
            new \Sorry\BoardIndex(7, 0)], $board->getClickableIndices(0, $indexOut, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));
    }

    public function test_canTeamNormalMove() {
        // If there's no drawn card, return should be false
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);
        $this->assertFalse($board->canTeamNormalMove(0, null, 0, 0, []));

        // If there's no valid team, return should be false
        $card = new \Sorry\Card(Sorry\CardType::FIVE);
        $this->assertFalse($board->canTeamNormalMove(42, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));

        // If no active pieces, return should be false
        $this->assertFalse($board->canTeamNormalMove(0, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));

        // Move a piece out
        $board->start(Sorry\Team::YELLOW);
        // Five should now work
        $this->assertTrue($board->canTeamNormalMove(0, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));

        // Sorry shouldn't work since no enemies have pieces out
        $card = new \Sorry\Card(Sorry\CardType::SORRY);
        $this->assertFalse($board->canTeamNormalMove(0, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));
    }

    public function test_canMove() {
        // If there's no drawn card, return should be false
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);
        $this->assertFalse($board->canMove(0, null, 0, 0, []));

        // If you still have pieces in home and home isn't blocked return should be true
        $card = new \Sorry\Card(Sorry\CardType::ONE);
        $this->assertTrue($board->canMove(0, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));

        // If the drawn card is a split (a 7)
        $card = new \Sorry\Card(Sorry\CardType::SEVEN);
        // Can't use 7 if no pawns are out
        $this->assertFalse($board->canMove(0, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));
        // Move a pawn out
        $board->start(Sorry\Team::YELLOW);
        // 7 should now work
        $this->assertTrue($board->canMove(0, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));
        // Move that piece back
        $board->move(new \Sorry\BoardIndex(0, 0), -2);
        // 7 Should no longer work since there's only 6 spaces for it to move forward
        $this->assertFalse($board->canMove(0, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));

        // Move another piece out, and 7 should work again
        $board->start(Sorry\Team::YELLOW);
        $this->assertTrue($board->canMove(0, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));

        // Move the first piece further in and put the second behind it, so their combined potential moves is less than 7
        $board->move(new \Sorry\BoardIndex(58, 0), 5);
        $board->move(new \Sorry\BoardIndex(0, 0), -2);
        $board->move(new \Sorry\BoardIndex(58, 0), 4);
        $this->assertFalse($board->canMove(0, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));

        // 5 shouldn't work because of lack of space
        $card = new \Sorry\Card(Sorry\CardType::FIVE);
        $this->assertFalse($board->canMove(0, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));

        // 1 should work because there's enough space
        $card = new \Sorry\Card(Sorry\CardType::ONE);
        $this->assertTrue($board->canMove(0, $card, $card->getForwardSpaces(), $card->getBackwardSpaces(), []));
    }

    public function test_start() {
        // Reset board
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);

        // Start a piece
        $this->assertEquals(4, $board->getStarts()[Sorry\Team::YELLOW]);
        $this->assertTrue($board->start(Sorry\Team::YELLOW));
        $correctDest = new Sorry\BoardIndex(0, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));
        $this->assertEquals(3, $board->getStarts()[Sorry\Team::YELLOW]);

        // Start a piece and kill a friendly piece
        $this->assertEquals(3, $board->getStarts()[Sorry\Team::YELLOW]);
        $this->assertTrue($board->start(Sorry\Team::YELLOW));
        $correctDest = new Sorry\BoardIndex(0, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));
        $this->assertEquals(3, $board->getStarts()[Sorry\Team::YELLOW]);

        // Move to opponent start zone
        $board->move(new Sorry\BoardIndex(0, 0), 15);
        $this->assertEquals(3, $board->getStarts()[Sorry\Team::YELLOW]);
        $this->assertEquals(4, $board->getStarts()[Sorry\Team::GREEN]);
        $board->start(Sorry\Team::GREEN);
        $this->assertEquals(4, $board->getStarts()[Sorry\Team::YELLOW]);
        $this->assertEquals(3, $board->getStarts()[Sorry\Team::GREEN]);

    }

    public function test_swap() {
        // Setup board
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);
        $board->start(Sorry\Team::YELLOW);
        $board->move(new Sorry\BoardIndex(0, 0), 2);
        $board->start(Sorry\Team::YELLOW);
        $board->move(new Sorry\BoardIndex(0, 0), -4);
        $board->move(new Sorry\BoardIndex(56, 0), 4);
        $board->start(Sorry\Team::YELLOW);
        $board->start(Sorry\Team::GREEN);
        $board->move(new Sorry\BoardIndex(15, 0), 2);
        $board->start(Sorry\Team::GREEN);
        $board->move(new Sorry\BoardIndex(15, 0), -4);
        $board->move(new Sorry\BoardIndex(11, 0), 4);
        $board->start(Sorry\Team::GREEN);

        // Swap with empty spaces and the same space
        $this->assertFalse($board->swap(new Sorry\BoardIndex(1, 0), new Sorry\BoardIndex(1, 0)));
        $this->assertFalse($board->swap(new Sorry\BoardIndex(1, 0), new Sorry\BoardIndex(3, 0)));
        $this->assertFalse($board->swap(new Sorry\BoardIndex(0, 0), new Sorry\BoardIndex(1, 0)));
        $this->assertFalse($board->swap(new Sorry\BoardIndex(1, 0), new Sorry\BoardIndex(0, 0)));

        // Swap perimeter friendly to perimeter friendly
        $this->assertFalse($board->swap(new Sorry\BoardIndex(0, 0), new Sorry\BoardIndex(2, 0)));

        // Swap perimeter friendly to safe friendly and vice versa
        $this->assertFalse($board->swap(new Sorry\BoardIndex(0, 0), new Sorry\BoardIndex(58, 2)));
        $this->assertFalse($board->swap(new Sorry\BoardIndex(58, 2), new Sorry\BoardIndex(0, 0)));

        // Swap perimeter friendly with safe opponent
        $this->assertFalse($board->swap(new Sorry\BoardIndex(0, 0), new Sorry\BoardIndex(13, 2)));
        $this->assertFalse($board->swap(new Sorry\BoardIndex(13, 2), new Sorry\BoardIndex(0, 0)));

        // Swap safe friendly with perimeter opponent
        $this->assertFalse($board->swap(new Sorry\BoardIndex(58, 2), new Sorry\BoardIndex(15, 0)));
        $this->assertFalse($board->swap(new Sorry\BoardIndex(15, 0), new Sorry\BoardIndex(58, 2)));

        // Swap safe friendly with safe with safe opponent
        $this->assertFalse($board->swap(new Sorry\BoardIndex(58, 2), new Sorry\BoardIndex(13, 2)));
        $this->assertFalse($board->swap(new Sorry\BoardIndex(13, 2), new Sorry\BoardIndex(58, 2)));

        // Swap perimeter friendly with opponent perimeter
        $this->assertTrue($this->containsIndex($board->getActivePieces(), new Sorry\BoardIndex(0, 0), Sorry\Team::YELLOW));
        $this->assertTrue($this->containsIndex($board->getActivePieces(), new Sorry\BoardIndex(15, 0), Sorry\Team::GREEN));
        $this->assertTrue($board->swap(new Sorry\BoardIndex(0, 0), new Sorry\BoardIndex(15, 0)));
        $this->assertTrue($this->containsIndex($board->getActivePieces(), new Sorry\BoardIndex(0, 0), Sorry\Team::GREEN));
        $this->assertTrue($this->containsIndex($board->getActivePieces(), new Sorry\BoardIndex(15, 0), Sorry\Team::YELLOW));
    }

    public function test_sorry() {
        // Reset board
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);

        // Place pieces onto the board
        $board->start(Sorry\Team::YELLOW);
        $board->move(new Sorry\BoardIndex(0, 0), 2);
        $board->start(Sorry\Team::YELLOW);
        $board->move(new Sorry\BoardIndex(0, 0), -4);
        $board->move(new Sorry\BoardIndex(56, 0), 4);
        $board->start(Sorry\Team::YELLOW);
        $board->start(Sorry\Team::GREEN);
        $board->move(new Sorry\BoardIndex(15, 0), 2);
        $board->start(Sorry\Team::GREEN);
        $board->move(new Sorry\BoardIndex(15, 0), -4);
        $board->move(new Sorry\BoardIndex(11, 0), 4);
        $board->start(Sorry\Team::GREEN);

        $this->assertEquals(1, $board->getStarts()[Sorry\Team::YELLOW]);

        // Sorry empty piece
        $this->assertFalse($board->sorry(new Sorry\BoardIndex(1, 0), Sorry\Team::YELLOW));
        $this->assertEquals(1, $board->getStarts()[Sorry\Team::YELLOW]);

        // Sorry friendly perimeter piece
        $this->assertFalse($board->sorry(new Sorry\BoardIndex(2, 0), Sorry\Team::YELLOW));
        $this->assertEquals(1, $board->getStarts()[Sorry\Team::YELLOW]);

        // Sorry friendly safe piece
        $this->assertFalse($board->sorry(new Sorry\BoardIndex(58, 2), Sorry\Team::YELLOW));
        $this->assertEquals(1, $board->getStarts()[Sorry\Team::YELLOW]);

        // Sorry opponent safe space
        $this->assertFalse($board->sorry(new Sorry\BoardIndex(13, 2), Sorry\Team::YELLOW));
        $this->assertEquals(1, $board->getStarts()[Sorry\Team::YELLOW]);

        // Sorry enemy perimeter piece
        $this->assertTrue($this->containsIndex($board->getActivePieces(), new Sorry\BoardIndex(17, 0), Sorry\Team::GREEN));
        $this->assertEquals(1, $board->getStarts()[Sorry\Team::GREEN]);
        $this->assertTrue($board->sorry(new Sorry\BoardIndex(17, 0), Sorry\Team::YELLOW));
        $this->assertEquals(2, $board->getStarts()[Sorry\Team::GREEN]);
        $this->assertEquals(0, $board->getStarts()[Sorry\Team::YELLOW]);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), new Sorry\BoardIndex(17, 0), Sorry\Team::YELLOW));

        // Sorry with no people in start
        $this->assertFalse($board->sorry(new Sorry\BoardIndex(15, 0), Sorry\Team::YELLOW));
    }

    public function test_move() {

        //
        // Test moving around solo
        //

        // Reset board
        $board = new Sorry\Board([Sorry\Team::YELLOW]);

        // Move piece onto the board
        $this->assertTrue($board->start(Sorry\Team::YELLOW));
        $correctDest = new Sorry\BoardIndex(0, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Pass the start making sure it does not turn into the start
        $this->assertTrue($board->move($correctDest, 1) != null);
        $correctDest = new Sorry\BoardIndex(1, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Move backwards to right in front of the intersection (causing wraparound)
        $this->assertTrue($board->move($correctDest, -2) != null);
        $correctDest = new Sorry\BoardIndex(59, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Move forward again to the start spot (should not turn)
        $this->assertTrue($board->move($correctDest, 1) != null);
        $correctDest = new Sorry\BoardIndex(0, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Move backwards to intersection (causing wraparound)
        $this->assertTrue($board->move($correctDest, -2) != null);
        $correctDest = new Sorry\BoardIndex(58, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Move forward (should move full amount into safe zone)
        $this->assertTrue($board->move($correctDest, 2) != null);
        $correctDest = new Sorry\BoardIndex(58, 2);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Move forward too far into safe zone (should not be allowed)
        $this->assertFalse($board->move($correctDest, 5) != null);
        $correctDest = new Sorry\BoardIndex(58, 2);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Move exact amount to get to home (should remove piece from board)
        $this->assertEquals(0, $board->getHomes()[Sorry\Team::YELLOW]);
        $this->assertTrue($board->move($correctDest, 4) != null);
        $this->assertTrue(count($board->getActivePieces()) == 0);
        $this->assertEquals(1, $board->getHomes()[Sorry\Team::YELLOW]);

        // Start new piece
        $this->assertTrue($board->start(Sorry\Team::YELLOW));
        $correctDest = new Sorry\BoardIndex(0, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Move backwards behind intersection
        $this->assertTrue($board->move($correctDest, -3) != null);
        $correctDest = new Sorry\BoardIndex(57, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Move forward (should turn into safe zone move amount - 1)
        $this->assertTrue($board->move($correctDest, 5) != null);
        $correctDest = new Sorry\BoardIndex(58, 4);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Back out of the safe zone
        $this->assertTrue($board->move($correctDest, -5) != null);
        $correctDest = new Sorry\BoardIndex(57, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Start a new piece
        $this->assertTrue($board->start(Sorry\Team::YELLOW));
        $correctDest = new Sorry\BoardIndex(0, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Land on friendly slide beginning (should not slide)
        $this->assertTrue($board->move($correctDest, 5) != null);
        $correctDest = new Sorry\BoardIndex(5, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Land on friendly slide middle (should not slide)
        $this->assertTrue($board->move($correctDest, 1) != null);
        $correctDest = new Sorry\BoardIndex(6, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Land on friendly slide end (should not slide)
        $this->assertTrue($board->move($correctDest, 3) != null);
        $correctDest = new Sorry\BoardIndex(9, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Move past opponent safe zone and don't turn into it
        $this->assertTrue($board->move($correctDest, 5) != null);
        $correctDest = new Sorry\BoardIndex(14, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Move past opponent start zone and don't turn into it
        $this->assertTrue($board->move($correctDest, 2) != null);
        $correctDest = new Sorry\BoardIndex(16, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Land on opponent slide beginning (should slide)
        $this->assertTrue($board->move($correctDest, 4) != null);
        $correctDest = new Sorry\BoardIndex(24, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Land on opponent slide middle (should slide)
        $this->assertTrue($board->move($correctDest, -1) != null);
        $correctDest = new Sorry\BoardIndex(23, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Land on opponent slide end (should slide)
        $this->assertTrue($board->move($correctDest, 1) != null);
        $correctDest = new Sorry\BoardIndex(24, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Move to nearly the safe zone
        $this->assertTrue($board->move($correctDest, 33) != null);
        $correctDest = new Sorry\BoardIndex(57, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Enter safe zone after naturally going around the board
        $this->assertTrue($board->move($correctDest, 2) != null);
        $correctDest = new Sorry\BoardIndex(58, 1);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        //
        // Test interactions between pieces
        //

        // Reset board
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);

        // Start a piece
        $this->assertTrue($board->start(Sorry\Team::YELLOW));
        $correctDest = new Sorry\BoardIndex(0, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Move away from start spot
        $this->assertTrue($board->move($correctDest, 2) != null);
        $correctDest = new Sorry\BoardIndex(2, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Start another piece
        $this->assertTrue($board->start(Sorry\Team::YELLOW));
        $correctDest = new Sorry\BoardIndex(0, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Test landing on top of a friendly piece
        $this->assertCount(2, $board->getActivePieces());
        $this->assertEquals(2, $board->getStarts()[Sorry\Team::YELLOW]);
        $this->assertTrue($board->move($correctDest, 2) != null);
        $correctDest = new Sorry\BoardIndex(2, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));
        $this->assertCount(1, $board->getActivePieces());
        $this->assertEquals(3, $board->getStarts()[Sorry\Team::YELLOW]);

        // Start opponent piece
        $this->assertTrue($board->start(Sorry\Team::GREEN));
        $correctDest = new Sorry\BoardIndex(15, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::GREEN));

        // Test landing on an opponent piece
        $this->assertCount(2, $board->getActivePieces());
        $this->assertEquals(3, $board->getStarts()[Sorry\Team::GREEN]);
        $correctDest = new Sorry\BoardIndex(15, 0);
        $this->assertTrue($board->move(new Sorry\BoardIndex(2, 0), 13) != null);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));
        $this->assertCount(1, $board->getActivePieces());
        $this->assertEquals(4, $board->getStarts()[Sorry\Team::GREEN]);

        // Reset board
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);

        // Start piece
        $this->assertTrue($board->start(Sorry\Team::YELLOW));
        $correctDest = new Sorry\BoardIndex(0, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Start opponent piece
        $this->assertTrue($board->start(Sorry\Team::GREEN));
        $correctDest = new Sorry\BoardIndex(15, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::GREEN));

        // Move opponent piece to the beginning of a slide
        $this->assertTrue($board->move($correctDest, -3) != null);
        $correctDest = new Sorry\BoardIndex(12, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::GREEN));

        // Move piece to the beginning of the slide
        $this->assertCount(2, $board->getActivePieces());
        $this->assertEquals(3, $board->getStarts()[Sorry\Team::GREEN]);
        $this->assertTrue($board->move(new Sorry\BoardIndex(0, 0), 12) != null);
        $correctDest = new Sorry\BoardIndex(15, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));
        $this->assertCount(1, $board->getActivePieces());
        $this->assertEquals(4, $board->getStarts()[Sorry\Team::GREEN]);

        // Reset board
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);

        // Start piece
        $this->assertTrue($board->start(Sorry\Team::YELLOW));
        $correctDest = new Sorry\BoardIndex(0, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Start opponent piece
        $this->assertTrue($board->start(Sorry\Team::GREEN));
        $correctDest = new Sorry\BoardIndex(15, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::GREEN));

        // Move opponent piece to the beginning of a slide
        $this->assertTrue($board->move($correctDest, -2) != null);
        $correctDest = new Sorry\BoardIndex(13, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::GREEN));

        // Move piece to the beginning of the slide
        $this->assertCount(2, $board->getActivePieces());
        $this->assertEquals(3, $board->getStarts()[Sorry\Team::GREEN]);
        $this->assertTrue($board->move(new Sorry\BoardIndex(0, 0), 12) != null);
        $correctDest = new Sorry\BoardIndex(15, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));
        $this->assertCount(1, $board->getActivePieces());
        $this->assertEquals(4, $board->getStarts()[Sorry\Team::GREEN]);

        // Reset board
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);

        // Start piece
        $this->assertTrue($board->start(Sorry\Team::YELLOW));
        $correctDest = new Sorry\BoardIndex(0, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));

        // Start opponent piece
        $this->assertTrue($board->start(Sorry\Team::GREEN));
        $correctDest = new Sorry\BoardIndex(15, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::GREEN));

        // Move piece to the beginning of the slide
        $this->assertCount(2, $board->getActivePieces());
        $this->assertEquals(3, $board->getStarts()[Sorry\Team::GREEN]);
        $this->assertTrue($board->move(new Sorry\BoardIndex(0, 0), 12) != null);
        $correctDest = new Sorry\BoardIndex(15, 0);
        $this->assertTrue($this->containsIndex($board->getActivePieces(), $correctDest, Sorry\Team::YELLOW));
        $this->assertCount(1, $board->getActivePieces());
        $this->assertEquals(4, $board->getStarts()[Sorry\Team::GREEN]);
    }

    public function test_indexDistance() {
        //
        // Not allowed moves
        //

        // Team not allowed on start space
        $this->assertCount(0, Sorry\Board::indexDistance(new Sorry\BoardIndex(13, 1), new Sorry\BoardIndex(12, 0), Sorry\Team::YELLOW));

        // Team not allowed on end space
        $this->assertCount(0, Sorry\Board::indexDistance(new Sorry\BoardIndex(12, 0), new Sorry\BoardIndex(13, 1), Sorry\Team::YELLOW));

        // Team not allowed on start and end space
        $this->assertCount(0, Sorry\Board::indexDistance(new Sorry\BoardIndex(13, 1), new Sorry\BoardIndex(13, 2), Sorry\Team::YELLOW));

        // Start is a start zone
        $this->assertCount(0, Sorry\Board::indexDistance(new Sorry\BoardIndex(0, 1), new Sorry\BoardIndex(5, 0), Sorry\Team::YELLOW));

        // End is a start zone
        $this->assertCount(0, Sorry\Board::indexDistance(new Sorry\BoardIndex(5, 0), new Sorry\BoardIndex(0, 1), Sorry\Team::YELLOW));

        // Start and end are start zones
        $this->assertCount(0, Sorry\Board::indexDistance(new Sorry\BoardIndex(0, 1), new Sorry\BoardIndex(15, 1), Sorry\Team::YELLOW));

        // Start is a home zone
        $this->assertCount(0, Sorry\Board::indexDistance(new Sorry\BoardIndex(58, 6), new Sorry\BoardIndex(55, 0), Sorry\Team::YELLOW));

        // Identical indices
        $this->assertCount(0, Sorry\Board::indexDistance(new Sorry\BoardIndex(58, 2), new Sorry\BoardIndex(58, 2), Sorry\Team::YELLOW));

        //
        // Perimeter to perimeter
        //

        // Forward wraparound
        $distances = Sorry\Board::indexDistance(new Sorry\BoardIndex(59, 0), new Sorry\BoardIndex(4, 0), Sorry\Team::YELLOW);
        $this->assertTrue(isset($distances[Sorry\Board::DIST_FORWARD_KEY]));
        $this->assertTrue(isset($distances[Sorry\Board::DIST_BACKWARDS_KEY]));
        $this->assertEquals(5, $distances[Sorry\Board::DIST_FORWARD_KEY]);
        $this->assertEquals(-55, $distances[Sorry\Board::DIST_BACKWARDS_KEY]);

        // Backward wraparound
        $distances = Sorry\Board::indexDistance(new Sorry\BoardIndex(1, 0), new Sorry\BoardIndex(57, 0), Sorry\Team::YELLOW);
        $this->assertTrue(isset($distances[Sorry\Board::DIST_FORWARD_KEY]));
        $this->assertTrue(isset($distances[Sorry\Board::DIST_BACKWARDS_KEY]));
        $this->assertEquals(56, $distances[Sorry\Board::DIST_FORWARD_KEY]);
        $this->assertEquals(-4, $distances[Sorry\Board::DIST_BACKWARDS_KEY]);

        //
        // Safe to safe
        //

        // Forwards
        $distances = Sorry\Board::indexDistance(new Sorry\BoardIndex(58, 1), new Sorry\BoardIndex(58, 3), Sorry\Team::YELLOW);
        $this->assertTrue(isset($distances[Sorry\Board::DIST_FORWARD_KEY]));
        $this->assertTrue(!isset($distances[Sorry\Board::DIST_BACKWARDS_KEY]));
        $this->assertEquals(2, $distances[Sorry\Board::DIST_FORWARD_KEY]);

        // Backwards
        $distances = Sorry\Board::indexDistance(new Sorry\BoardIndex(58, 5), new Sorry\BoardIndex(58, 2), Sorry\Team::YELLOW);
        $this->assertTrue(!isset($distances[Sorry\Board::DIST_FORWARD_KEY]));
        $this->assertTrue(isset($distances[Sorry\Board::DIST_BACKWARDS_KEY]));
        $this->assertEquals(-3, $distances[Sorry\Board::DIST_BACKWARDS_KEY]);

        // Into home
        $distances = Sorry\Board::indexDistance(new Sorry\BoardIndex(58, 2), new Sorry\BoardIndex(58, 6), Sorry\Team::YELLOW);
        $this->assertTrue(isset($distances[Sorry\Board::DIST_FORWARD_KEY]));
        $this->assertTrue(!isset($distances[Sorry\Board::DIST_BACKWARDS_KEY]));
        $this->assertEquals(4, $distances[Sorry\Board::DIST_FORWARD_KEY]);

        //
        // Perimeter to safe
        //

        // Just inside
        $distances = Sorry\Board::indexDistance(new Sorry\BoardIndex(55, 0), new Sorry\BoardIndex(58, 3), Sorry\Team::YELLOW);
        $this->assertTrue(isset($distances[Sorry\Board::DIST_FORWARD_KEY]));
        $this->assertTrue(!isset($distances[Sorry\Board::DIST_BACKWARDS_KEY]));
        $this->assertEquals(6, $distances[Sorry\Board::DIST_FORWARD_KEY]);

        // Into home
        $distances = Sorry\Board::indexDistance(new Sorry\BoardIndex(55, 0), new Sorry\BoardIndex(58, 6), Sorry\Team::YELLOW);
        $this->assertTrue(isset($distances[Sorry\Board::DIST_FORWARD_KEY]));
        $this->assertTrue(!isset($distances[Sorry\Board::DIST_BACKWARDS_KEY]));
        $this->assertEquals(9, $distances[Sorry\Board::DIST_FORWARD_KEY]);

        //
        // Safe to perimeter
        //

        // Just outside
        $distances = Sorry\Board::indexDistance(new Sorry\BoardIndex(58, 2), new Sorry\BoardIndex(58, 0), Sorry\Team::YELLOW);
        $this->assertTrue(!isset($distances[Sorry\Board::DIST_FORWARD_KEY]));
        $this->assertTrue(isset($distances[Sorry\Board::DIST_BACKWARDS_KEY]));
        $this->assertEquals(-2, $distances[Sorry\Board::DIST_BACKWARDS_KEY]);

        // Further outside
        $distances = Sorry\Board::indexDistance(new Sorry\BoardIndex(58, 2), new Sorry\BoardIndex(52, 0), Sorry\Team::YELLOW);
        $this->assertTrue(!isset($distances[Sorry\Board::DIST_FORWARD_KEY]));
        $this->assertTrue(isset($distances[Sorry\Board::DIST_BACKWARDS_KEY]));
        $this->assertEquals(-8, $distances[Sorry\Board::DIST_BACKWARDS_KEY]);

        // Near full board traverse
        $distances = Sorry\Board::indexDistance(new Sorry\BoardIndex(58, 2), new Sorry\BoardIndex(59, 0), Sorry\Team::YELLOW);
        $this->assertTrue(!isset($distances[Sorry\Board::DIST_FORWARD_KEY]));
        $this->assertTrue(isset($distances[Sorry\Board::DIST_BACKWARDS_KEY]));
        $this->assertEquals(-61, $distances[Sorry\Board::DIST_BACKWARDS_KEY]);
    }

    public function test_removeTeam() {
        $board = new Sorry\Board([Sorry\Team::YELLOW]);

        // Move piece onto the board
        $board->start(Sorry\Team::YELLOW);
        $this->assertTrue(count($board->getActivePieces()) == 1);
        $correctDest = new Sorry\BoardIndex(0, 0);

        // Move backwards to intersection (causing wraparound)
        $this->assertTrue($board->move($correctDest, -2) != null);
        $this->assertCount(1, $board->getActivePieces());
        $correctDest = new Sorry\BoardIndex(58, 0);

        // Move exact amount to get to home (should remove piece from board)
        $this->assertTrue($board->move($correctDest, 6) != null);
        $this->assertTrue(count($board->getActivePieces()) == 0);
        $this->assertEquals(1, $board->getHomes()[Sorry\Team::YELLOW]);

        $this->assertEquals(3, $board->getStarts()[Sorry\Team::YELLOW]);
        $this->assertEquals(1, $board->getHomes()[Sorry\Team::YELLOW]);

        $board->removeTeam(Sorry\Team::YELLOW);

        $this->assertEquals(0, $board->getStarts()[Sorry\Team::YELLOW]);
        $this->assertEquals(0, $board->getHomes()[Sorry\Team::YELLOW]);
        $this->assertCount(0, $board->getActivePieces());
    }

    public function test_hasWon() {
        // Reset board
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);

        // Move first piece into the home
        $board->start(Sorry\Team::YELLOW);
        $board->move(new Sorry\BoardIndex(0, 0), -4);
        $board->move(new Sorry\BoardIndex(56, 0), 8);
        $this->assertEquals(Sorry\Team::NONE, $board->hasWon());

        // Move second into the home
        $board->start(Sorry\Team::YELLOW);
        $board->move(new Sorry\BoardIndex(0, 0), -4);
        $board->move(new Sorry\BoardIndex(56, 0), 8);
        $this->assertEquals(Sorry\Team::NONE, $board->hasWon());

        // Move third piece into the home
        $board->start(Sorry\Team::YELLOW);
        $board->move(new Sorry\BoardIndex(0, 0), -4);
        $board->move(new Sorry\BoardIndex(56, 0), 8);
        $this->assertEquals(Sorry\Team::NONE, $board->hasWon());

        // Move fourth piece into the home
        $board->start(Sorry\Team::YELLOW);
        $board->move(new Sorry\BoardIndex(0, 0), -4);
        $board->move(new Sorry\BoardIndex(56, 0), 8);
        $this->assertEquals(Sorry\Team::YELLOW, $board->hasWon());
    }

    public function test_indexContent() {
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);

        // Valid spot with no piece
        $this->assertEquals(Sorry\Team::NONE, $board->indexContent(new Sorry\BoardIndex(0, 0)));

        // Valid spot with yellow piece
        $board->start(Sorry\Team::YELLOW);
        $this->assertEquals(Sorry\Team::YELLOW, $board->indexContent(new Sorry\BoardIndex(0, 0)));

        // Invalid spot (bad branch)
        $this->assertEquals(Sorry\Team::NONE, $board->indexContent(new Sorry\BoardIndex(0, 42)));

        // Invalid spot (bad perim)
        $this->assertEquals(Sorry\Team::NONE, $board->indexContent(new Sorry\BoardIndex(99, 0)));
    }

    public function test_isRealTeam() {
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);

        $this->assertTrue($board->isRealTeam(Sorry\Team::YELLOW));
        $this->assertTrue($board->isRealTeam(Sorry\Team::GREEN));
        $this->assertTrue($board->isRealTeam(Sorry\Team::RED));
        $this->assertTrue($board->isRealTeam(Sorry\Team::BLUE));
        $this->assertFalse($board->isRealTeam(Sorry\Team::NONE));
        $this->assertFalse($board->isRealTeam(42));
    }

    public function test_getActivePieces() {
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);

        $this->assertCount(0, $board->getActivePieces());

        $board->start(Sorry\Team::YELLOW);
        $this->assertCount(1, $board->getActivePieces());

        $board->start(Sorry\Team::BLUE);
        $this->assertCount(2, $board->getActivePieces());

        $board->move(new Sorry\BoardIndex(0, 0), -2);
        $board->move(new Sorry\BoardIndex(58, 0), 2);
        $this->assertCount(2, $board->getActivePieces());
        $board->move(new Sorry\BoardIndex(58, 2), 4);
        $this->assertCount(1, $board->getActivePieces());
    }

    public function test_getState() {
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);

        $yellowStartSpot = new Sorry\BoardIndex(Sorry\Board::START_INTERSECTIONS[Sorry\Team::YELLOW], Sorry\Board::MIN_SPACE);
        $greenStartSpot = new Sorry\BoardIndex(Sorry\Board::START_INTERSECTIONS[Sorry\Team::GREEN], Sorry\Board::MIN_SPACE);
        $redStartSpot = new Sorry\BoardIndex(Sorry\Board::START_INTERSECTIONS[Sorry\Team::RED], Sorry\Board::MIN_SPACE);
        $blueStartSpot = new Sorry\BoardIndex(Sorry\Board::START_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MIN_SPACE);

        // No pieces on the board
        $boardState = $board->getActivePieces();
        $this->assertCount(0, $boardState);

        // Just yellow on the board
        $board->start(Sorry\Team::YELLOW);
        $boardState = $board->getActivePieces();
        $this->assertCount(1, $boardState);
        $this->assertTrue($this->containsIndex($boardState, $yellowStartSpot, Sorry\Team::YELLOW));

        // Yellow and green on the board
        $board->start(Sorry\Team::GREEN);
        $boardState = $board->getActivePieces();
        $this->assertCount(2, $boardState);
        $this->assertTrue($this->containsIndex($boardState, $yellowStartSpot, Sorry\Team::YELLOW));
        $this->assertTrue($this->containsIndex($boardState, $greenStartSpot, Sorry\Team::GREEN));

        // Yellow, green and red on the board
        $board->start(Sorry\Team::RED);
        $boardState = $board->getActivePieces();
        $this->assertCount(3, $boardState);
        $this->assertTrue($this->containsIndex($boardState, $yellowStartSpot, Sorry\Team::YELLOW));
        $this->assertTrue($this->containsIndex($boardState, $greenStartSpot, Sorry\Team::GREEN));
        $this->assertTrue($this->containsIndex($boardState, $redStartSpot, Sorry\Team::RED));

        // All colors on the board
        $board->start(Sorry\Team::BLUE);
        $boardState = $board->getActivePieces();
        $this->assertCount(4, $boardState);
        $this->assertTrue($this->containsIndex($boardState, $yellowStartSpot, Sorry\Team::YELLOW));
        $this->assertTrue($this->containsIndex($boardState, $greenStartSpot, Sorry\Team::GREEN));
        $this->assertTrue($this->containsIndex($boardState, $redStartSpot, Sorry\Team::RED));
        $this->assertTrue($this->containsIndex($boardState, $blueStartSpot, Sorry\Team::BLUE));
    }

    /**
     * Determine if a board status contains an index and has the correct team
     * @param $boardState array Board status to test in
     * @param $index Sorry\BoardIndex The index that's supposed to exist
     * @param $team int The team that's supposed to be at that index
     * @return bool True if a board status contains an index
     */
    private function containsIndex($boardState, $index, $team) {
        foreach ($boardState as $space) {
            if ($space[Sorry\Board::STATE_INDEX_KEY]->equals($index) && $space[Sorry\Board::STATE_TEAM_KEY] == $team) {
                return true;
            }
        }
        return false;
    }

    public function test_getStarts() {
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);
        $starts = $board->getStarts();
        $this->assertTrue($starts != null);
        $this->assertTrue(isset($starts[Sorry\Team::YELLOW]));
        $this->assertTrue(isset($starts[Sorry\Team::GREEN]));
        $this->assertTrue(isset($starts[Sorry\Team::RED]));
        $this->assertTrue(isset($starts[Sorry\Team::BLUE]));
        $this->assertEquals(Sorry\Board::PIECES_PER_TEAM, $starts[Sorry\Team::YELLOW]);
        $this->assertEquals(Sorry\Board::PIECES_PER_TEAM, $starts[Sorry\Team::GREEN]);
        $this->assertEquals(Sorry\Board::PIECES_PER_TEAM, $starts[Sorry\Team::RED]);
        $this->assertEquals(Sorry\Board::PIECES_PER_TEAM, $starts[Sorry\Team::BLUE]);
    }

    public function test_getHomes() {
        $board = new Sorry\Board([Sorry\Team::YELLOW, Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]);
        $homes = $board->getHomes();
        $this->assertTrue($homes != null);
        $this->assertTrue(isset($homes[Sorry\Team::YELLOW]));
        $this->assertTrue(isset($homes[Sorry\Team::GREEN]));
        $this->assertTrue(isset($homes[Sorry\Team::RED]));
        $this->assertTrue(isset($homes[Sorry\Team::BLUE]));
        $this->assertEquals(0, $homes[Sorry\Team::YELLOW]);
        $this->assertEquals(0, $homes[Sorry\Team::GREEN]);
        $this->assertEquals(0, $homes[Sorry\Team::RED]);
        $this->assertEquals(0, $homes[Sorry\Team::BLUE]);
    }
}