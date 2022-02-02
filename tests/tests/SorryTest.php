<?php


class SorryTest extends \PHPUnit\Framework\TestCase {

    public function test_construct() {
        // List of players
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE];
        $sorry = new Sorry\Sorry($colorsList);

        $this->assertInstanceOf('Sorry\Sorry', $sorry);

        $this->assertTrue(in_array(Sorry\Team::BLUE, $sorry->getTurnOrder()));
        $this->assertTrue(in_array(Sorry\Team::YELLOW, $sorry->getTurnOrder()));
        $this->assertTrue(!in_array(Sorry\Team::RED, $sorry->getTurnOrder()));
        $this->assertTrue(!in_array(Sorry\Team::GREEN, $sorry->getTurnOrder()));
    }

    public function test_pressSpace() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE];
        $sorry = new Sorry\Sorry($colorsList);

        //
        // See if you can press same space twice to select then deselect
        //

        $card = new Sorry\Card(Sorry\CardType::FIVE);
        $sorry->setDrawnCard($card);
        $sorry->setForwardSpaces(5);
        $sorry->setUsedUpCard(false);
        // Press same space (2,0 index == 6 sorry space)
        $sorry->setSelectedIndex(new Sorry\BoardIndex(2, 0));
        $this->assertTrue($sorry->pressSpace(6));
        $this->assertFalse($sorry->isUsedUpCard());
        // Should be false since space isn't a clickable space
        $this->assertFalse($sorry->pressSpace(27));

        //
        // See if you can press start space
        // and start a new piece
        //

        $sorry->setSelectedIndex(null);
        $card = new Sorry\Card(Sorry\CardType::TWO);
        $sorry->setDrawnCard($card);
        $sorry->setUsedUpCard(false);
        // Set to YELLOW turn
        $sorry->setTeamTurn(0);
        $sorry->setForwardSpaces(2);

        // Valid space gets converted to yellow start
        $this->assertTrue($sorry->pressSpace(36));
        $this->assertEquals(0, $sorry->getSelectedIndex()->getPerim());
        $this->assertEquals(1, $sorry->getSelectedIndex()->getBranch());

        // Cannot press blue start on yellow turn
        $this->assertFalse($sorry->pressSpace(178));
        // Valid press here (can press 4 since we already pressed yellow start zone)
        $this->assertTrue($sorry->pressSpace(4));
        $this->assertTrue($sorry->isUsedUpCard());

        //
        // Action press normal perimeter moving
        //

        $sorry->getBoard()->start(Sorry\Team::YELLOW);
        $sorry->getBoard()->move(new Sorry\BoardIndex(0, 0), 10);
        $sorry->setSelectedIndex(new Sorry\BoardIndex(10, 0));
        $card = new Sorry\Card(Sorry\CardType::FIVE);
        $sorry->setDrawnCard($card);
        $sorry->setUsedUpCard(false);
        // Set to YELLOW turn
        $sorry->setTeamTurn(0);
        $sorry->setForwardSpaces(5);

        $this->assertTrue($sorry->pressSpace(79));

        //
        // Action press swapping
        //
        $sorry->getBoard()->start(Sorry\Team::YELLOW);
        $sorry->getBoard()->start(Sorry\Team::BLUE);
        $sorry->getBoard()->move(new Sorry\BoardIndex(0, 0), 10);
        $sorry->setSelectedIndex(new Sorry\BoardIndex(10, 0));
        $card = new Sorry\Card(Sorry\CardType::ELEVEN);
        $sorry->setDrawnCard($card);
        $sorry->setUsedUpCard(false);
        // Set to YELLOW turn
        $sorry->setTeamTurn(0);
        $sorry->setForwardSpaces(11);
        // Should be able to swap!
        $this->assertTrue($sorry->pressSpace(176));

        //
        // Action press sorry
        //

        $sorry->getBoard()->start(Sorry\Team::BLUE);
        $card = new Sorry\Card(Sorry\CardType::SORRY);
        $sorry->setDrawnCard($card);
        $sorry->setUsedUpCard(false);
        $sorry->setSelectedIndex(new Sorry\BoardIndex(0, 1));
        // Set to YELLOW turn
        $sorry->setTeamTurn(0);
        // Should be able to send them back to start!
        $this->assertTrue($sorry->pressSpace(176));

        //
        // Test pressing on a space the full distance of movement on a swap card
        //
        $sorry = new Sorry\Sorry($colorsList);
        $sorry->getBoard()->start(Sorry\Team::YELLOW);
        $sorry->getBoard()->move(new Sorry\BoardIndex(0, 0), -4);
        $sorry->getBoard()->start(Sorry\Team::BLUE);
        $sorry->setDrawnCard(new Sorry\Card((Sorry\CardType::ELEVEN)));
        $this->assertCount(2, $sorry->getBoard()->getActivePieces());
        $sorry->pressSpace(176);
        $sorry->pressSpace(0);
        $this->assertCount(1, $sorry->getBoard()->getActivePieces());
    }

    public function test_nextTurn() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE];
        $sorry = new Sorry\Sorry($colorsList);

        //
        // Card 2 should not advance to next turn
        //

        $card = new Sorry\Card(Sorry\CardType::TWO);

        $sorry->setDrawnCard($card);
        $this->assertFalse($sorry->nextTurn());


        //
        // Card 7 should not advance to next turn if split
        //

        $card = new Sorry\Card(Sorry\CardType::SEVEN);

        $sorry->setDrawnCard($card);

        // Simulating a move
        $sorry->setForwardSpaces($card->getForwardSpaces() - 4);
        $sorry->setBackwardSpaces(0);
        $sorry->setUsedUpCard(false);
        $sorry->setDrawLeft(false);
        $this->assertFalse($sorry->nextTurn());


        //
        // Card 5 should advance to next turn
        //

        $card = new Sorry\Card(Sorry\CardType::FIVE);

        $sorry->setDrawnCard($card);
        $sorry->setUsedUpCard(true);
        $sorry->setDrawLeft(false);
        $this->assertTrue($sorry->nextTurn());

        //
        // Card 10 should advance to next turn
        //

        $card = new Sorry\Card(Sorry\CardType::TEN);

        $sorry->setDrawnCard($card);
        $sorry->setUsedUpCard(true);
        $sorry->setDrawLeft(false);
        $this->assertTrue($sorry->nextTurn());

    }

    public function test_drawCard() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE];
        $sorry = new Sorry\Sorry($colorsList);

        //
        // Basic card draw
        //

        $this->assertTrue($sorry->drawCard());
        $this->assertInstanceOf('Sorry\Card', $sorry->getDrawnCard());

        //
        // Draw again check
        //

        if($sorry->getDrawnCard()->getCardType() != 2) {
            $this->assertFalse($sorry->drawCard());
        } else {
            $this->assertTrue($sorry->drawCard());
        }

    }

    public function test_getState() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE];
        $sorry = new Sorry\Sorry($colorsList);

        $sorry->setSelectedIndex(new Sorry\BoardIndex(1, 0));
        $card = new Sorry\Card(Sorry\CardType::TWO);
        $sorry->setDrawnCard($card);
        $sorry->setTeamTurn(1);
        $sorry->setUsedUpCard(true);
        $sorry->setDrawLeft(true);

        $formattedState = $sorry->getState();

        $this->assertInstanceOf('Sorry\GameState', $formattedState);
        $this->assertTrue($formattedState->canDrawCard);
        $this->assertEquals(null, $formattedState->message);
        $this->assertCount(0, $formattedState->activePieces);
        $this->assertCount(4, $formattedState->starts);
        $this->assertCount(4, $formattedState->homes);
        $this->assertEquals(1, $formattedState->selectedIndex->getPerim());
        $this->assertEquals(0, $formattedState->selectedIndex->getBranch());
        $this->assertEquals(Sorry\Team::BLUE, $formattedState->teamTurn);

        $sorry->drawCard();
        $formattedState = $sorry->getState();
        // Make sure the card top discard matches the card we just drew
        $this->assertEquals($sorry->getDrawnCard()->getCardType(), $formattedState->topDiscard);
        // Haven't drawn all cards in deck. Shouldn't be false.
        $this->assertTrue($formattedState->cardInDrawPile);


        $card = new Sorry\Card(Sorry\CardType::FOUR);
        $sorry->setDrawnCard($card);
        $formattedState = $sorry->getState();
        $this->assertFalse($formattedState->canDrawCard);

    }

    public function test_moveSpaces() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE];
        $sorry = new Sorry\Sorry($colorsList);
        $sorry->setUsedUpCard(false);
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE, Sorry\Board::MIN_SPACE);
        $sorry->setSelectedIndex($boardIndex);

        //
        // Normal forward check
        //

        $card = new Sorry\Card(Sorry\CardType::FIVE);
        $sorry->setDrawnCard($card);
        $sorry->setForwardSpaces($card->getForwardSpaces());
        $sorry->setBackwardSpaces($card->getBackwardSpaces());

        // 5 card doesn't have backwards. Should be false.
        $this->assertFalse($sorry->moveSpaces(-5));
        $sorry->setUsedUpCard(false);
        $this->assertFalse($sorry->isUsedUpCard());

        $this->assertTrue($sorry->moveSpaces(5));
        $this->assertTrue($sorry->isUsedUpCard());


        $this->assertEquals(0, $sorry->getForwardSpaces());
        $this->assertEquals(0, $sorry->getBackwardSpaces());

        //
        // Same card, over amount possible. Fail.
        //
        $sorry->setUsedUpCard(false);
        $sorry->setForwardSpaces($card->getForwardSpaces());
        $sorry->setBackwardSpaces($card->getBackwardSpaces());

        $this->assertFalse($sorry->moveSpaces(6));
        $this->assertFalse($sorry->isUsedUpCard());
        $this->assertEquals(5, $sorry->getForwardSpaces());
        $this->assertEquals(0, $sorry->getBackwardSpaces());

        //
        // Same card, under amount possible. Not a split. Fail.
        //
        $sorry->setUsedUpCard(false);
        $sorry->setForwardSpaces($card->getForwardSpaces());
        $sorry->setBackwardSpaces($card->getBackwardSpaces());

        $this->assertFalse($sorry->moveSpaces(4));
        $this->assertFalse($sorry->isUsedUpCard());
        $this->assertEquals(5, $sorry->getForwardSpaces());
        $this->assertEquals(0, $sorry->getBackwardSpaces());

        //
        // All in one move on 7 card check
        //
        $sorry->setUsedUpCard(false);
        $card = new Sorry\Card(Sorry\CardType::SEVEN);
        $sorry->setDrawnCard($card);
        $sorry->setForwardSpaces($card->getForwardSpaces());
        $sorry->setBackwardSpaces($card->getBackwardSpaces());

        $this->assertTrue($sorry->moveSpaces(7));

        //
        // Split move on 7 card check
        //
        $sorry->setUsedUpCard(false);
        $sorry->setForwardSpaces($card->getForwardSpaces());
        $sorry->setBackwardSpaces($card->getBackwardSpaces());

        // This would have 4 forward spaces remaining after move.
        $this->assertTrue($sorry->moveSpaces(3));
        $this->assertFalse($sorry->isUsedUpCard());
        $this->assertEquals(4, $sorry->getForwardSpaces());
        $this->assertEquals(0, $sorry->getBackwardSpaces());

        $this->assertTrue($sorry->moveSpaces(4));
        $this->assertTrue($sorry->isUsedUpCard());
        $this->assertEquals(0, $sorry->getForwardSpaces());
        //
        // Split move on 7 card check over amount
        //
        $sorry->setUsedUpCard(false);
        $sorry->setForwardSpaces($card->getForwardSpaces());
        $sorry->setBackwardSpaces($card->getBackwardSpaces());

        $this->assertTrue($sorry->moveSpaces(3));
        $this->assertFalse($sorry->isUsedUpCard());
        $this->assertEquals(4, $sorry->getForwardSpaces());

        // 3 + 5 is over 7. This will be false.
        $this->assertFalse($sorry->moveSpaces(5));
        $this->assertFalse($sorry->isUsedUpCard());
        $this->assertEquals(4, $sorry->getForwardSpaces());


        //
        // BACKWARDS CHECK
        //
        $sorry->setUsedUpCard(false);
        $card = new Sorry\Card(Sorry\CardType::FOUR);
        $sorry->setDrawnCard($card);
        $sorry->setForwardSpaces($card->getForwardSpaces());
        $sorry->setBackwardSpaces($card->getBackwardSpaces());

        // Doesn't move forward 4. Card 4 only moves backwards!
        $this->assertFalse($sorry->moveSpaces(4));
        $sorry->setUsedUpCard(false);
        $this->assertFalse($sorry->isUsedUpCard());
        $this->assertEquals(0, $sorry->getForwardSpaces());
        $this->assertEquals(4, $sorry->getBackwardSpaces());

        $this->assertTrue($sorry->moveSpaces(-4));
        $this->assertTrue($sorry->isUsedUpCard());
        $this->assertEquals(0, $sorry->getForwardSpaces());
        $this->assertEquals(0, $sorry->getBackwardSpaces());

        //
        // 10 card can be either or
        //
        $sorry->setUsedUpCard(false);
        $card = new Sorry\Card(Sorry\CardType::TEN);
        $sorry->setDrawnCard($card);
        $sorry->setForwardSpaces($card->getForwardSpaces());
        $sorry->setBackwardSpaces($card->getBackwardSpaces());

        $this->assertTrue($sorry->moveSpaces(10));
        $this->assertTrue($sorry->isUsedUpCard());
        $this->assertEquals(0, $sorry->getForwardSpaces());
        $this->assertEquals(0, $sorry->getBackwardSpaces());

        // Already moved forward 10. Can't move again on 10 card!
        $this->assertFalse($sorry->moveSpaces(-1));
        $this->assertEquals(0, $sorry->getForwardSpaces());
        $this->assertEquals(0, $sorry->getBackwardSpaces());

        //
        // Reverse attempt below
        //
        $sorry->setUsedUpCard(false);
        $sorry->setForwardSpaces($card->getForwardSpaces());
        $sorry->setBackwardSpaces($card->getBackwardSpaces());
        $this->assertEquals(10, $sorry->getForwardSpaces());
        $this->assertEquals(1, $sorry->getBackwardSpaces());

        $this->assertTrue($sorry->moveSpaces(-1));
        $this->assertTrue($sorry->isUsedUpCard());
        $this->assertEquals(0, $sorry->getForwardSpaces());
        $this->assertEquals(0, $sorry->getBackwardSpaces());

        // Already moved backward -1. Can't move again on 10 card!
        $this->assertFalse($sorry->moveSpaces(10));
        $this->assertEquals(0, $sorry->getForwardSpaces());
        $this->assertEquals(0, $sorry->getBackwardSpaces());

    }

    public function test_forfeit() {
        //
        // Test one team trying to forfeit
        //
        $colorsList = [Sorry\Team::YELLOW];
        $sorry = new Sorry\Sorry($colorsList);
        $sorry->forfeit($sorry->getTeamTurn());

        $this->assertCount(1, $sorry->getTurnOrder());

        //
        // Test two teams and one forfeits
        //
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::RED];
        $sorry = new Sorry\Sorry($colorsList);
        $sorry->setTeamTurn(1);
        $sorry->forfeit($sorry->getTeamTurn());

        $this->assertCount(1, $sorry->getTurnOrder());

        // Shouldn't forfeit, only one team left
        $sorry->forfeit($sorry->getTeamTurn());
        $this->assertCount(1, $sorry->getTurnOrder());

        //
        // Test four teams
        //
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::RED, Sorry\Team::BLUE, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);
        $sorry->setTeamTurn(3);
        $sorry->forfeit($sorry->getTeamTurn());

        $this->assertCount(3, $sorry->getTurnOrder());
        $this->assertEquals(0, $sorry->getTeamTurn());

        $sorry->forfeit($sorry->getTeamTurn());
        $this->assertCount(2, $sorry->getTurnOrder());
        $this->assertEquals(1, $sorry->getTeamTurn());
    }

    public function test_undo() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE];
        $sorry = new Sorry\Sorry($colorsList);

        //
        // The initial state
        //
        $this->assertFalse($sorry->undo());
        $this->assertNull($sorry->getSelectedIndex());
        $this->assertEquals("Cannot undo actions with the current card because no card has been drawn this turn.",
            $sorry->getMessage());

        //
        // Drawing a card
        //
        $card = new Sorry\Card(Sorry\CardType::FIVE);
        $sorry->setDrawnCard($card);
        $this->assertTrue($sorry->undo());
        $this->assertEquals("Actions done with the current card have been undone.",
            $sorry->getMessage());

    }

    public function test_skipCard() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE];
        $sorry = new Sorry\Sorry($colorsList);

        //
        // Cannot skip card
        //
        $sorry->setUsedUpCard(false);
        $this->assertFalse($sorry->skipCard());
        $this->assertEquals("Can only skip to opt out of swaps on 11 cards when no regular movement is possible.",
            $sorry->getMessage());
        $this->assertFalse($sorry->isUsedUpCard());

        //
        // Can skip card
        //
        $sorry->setCanSkip(true);
        $this->assertTrue($sorry->skipCard());
        $this->assertNull($sorry->getMessage());
        $this->assertTrue($sorry->isUsedUpCard());
    }

    public function test_hasWon() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE];
        $sorry = new Sorry\Sorry($colorsList);

        //
        // Initial state
        //

        $this->assertEquals(Sorry\Team::NONE, $sorry->hasWon());

        //
        // One person forfeits out of the two total
        //

        $sorry->setTeamTurn(Sorry\Team::YELLOW);
        $sorry->forfeit($sorry->getTeamTurn());
        $this->assertEquals(Sorry\Team::BLUE, $sorry->hasWon());

        //
        // Multiple people forfeit until one remains
        //

        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $sorry->setTeamTurn(0);
        $sorry->forfeit($sorry->getTeamTurn());
        $this->assertEquals(Sorry\Team::NONE, $sorry->hasWon());

        $sorry->setTeamTurn(1);
        $sorry->forfeit($sorry->getTeamTurn());
        $this->assertEquals(Sorry\Team::NONE, $sorry->hasWon());

        $sorry->setTeamTurn(1);
        $sorry->forfeit($sorry->getTeamTurn());
        $this->assertEquals(Sorry\Team::GREEN, $sorry->hasWon());


        //
        // Test team actually winning
        //
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);
        $board = $sorry->getBoard();

        // Move first piece into the home
        $board->start(Sorry\Team::YELLOW);
        $board->move(new Sorry\BoardIndex(0, 0), -4);
        $board->move(new Sorry\BoardIndex(56, 0), 8);

        // Move second into the home
        $board->start(Sorry\Team::YELLOW);
        $board->move(new Sorry\BoardIndex(0, 0), -4);
        $board->move(new Sorry\BoardIndex(56, 0), 8);

        // Move third piece into the home
        $board->start(Sorry\Team::YELLOW);
        $board->move(new Sorry\BoardIndex(0, 0), -4);
        $board->move(new Sorry\BoardIndex(56, 0), 8);

        // Move fourth piece into the home
        $board->start(Sorry\Team::YELLOW);
        $board->move(new Sorry\BoardIndex(0, 0), -4);
        $board->move(new Sorry\BoardIndex(56, 0), 8);

        $this->assertEquals(Sorry\Team::YELLOW, $sorry->hasWon());

    }

    //
    //  Accessors and Mutators tests below
    //

    public function test_isDrawn () {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $this->assertTrue($sorry->isDrawn());

        $sorry->setDrawLeft(false);
        $this->assertFalse($sorry->isDrawn());

        $sorry->setDrawLeft(true);
        $this->assertTrue($sorry->isDrawn());
    }

    public function test_setDrawLeft() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $sorry->setDrawLeft(false);
        $this->assertFalse($sorry->isDrawn());

        $sorry->setDrawLeft(true);
        $this->assertTrue($sorry->isDrawn());
    }

    public function test_getTurnOrder() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $this->assertCount(4, $sorry->getTurnOrder());

        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE];
        $sorry = new Sorry\Sorry($colorsList);

        $this->assertCount(2, $sorry->getTurnOrder());
    }

    public function test_getSelectedIndex() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $this->assertNull($sorry->getSelectedIndex());

        $sorry->setSelectedIndex(new Sorry\BoardIndex(0, 1));
        $this->assertInstanceOf('Sorry\BoardIndex', $sorry->getSelectedIndex());
        $this->assertEquals(0, $sorry->getSelectedIndex()->getPerim());
        $this->assertEquals(1, $sorry->getSelectedIndex()->getBranch());
    }

    public function test_setSelectedIndex() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $sorry->setSelectedIndex(new Sorry\BoardIndex(0, 1));
        $this->assertInstanceOf('Sorry\BoardIndex', $sorry->getSelectedIndex());
    }

    public function test_getDrawnCard() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $this->assertNull($sorry->getDrawnCard());

        $sorry->drawCard();
        $this->assertInstanceOf('Sorry\Card', $sorry->getDrawnCard());

        $card = new Sorry\Card(Sorry\CardType::FIVE);
        $sorry->setDrawnCard($card);
        $this->assertInstanceOf('Sorry\Card', $sorry->getDrawnCard());
    }

    public function test_setDrawnCard() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $card = new Sorry\Card(Sorry\CardType::FIVE);
        $sorry->setDrawnCard($card);
        $this->assertInstanceOf('Sorry\Card', $sorry->getDrawnCard());
    }

    public function test_getForwardSpaces() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $this->assertEquals(0, $sorry->getForwardSpaces());

        $sorry->setForwardSpaces(10);
        $this->assertEquals(10, $sorry->getForwardSpaces());
    }

    public function test_getBackwardSpaces() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $this->assertEquals(0, $sorry->getBackwardSpaces());

        $sorry->setBackwardSpaces(4);
        $this->assertEquals(4, $sorry->getBackwardSpaces());
    }

    public function test_setForwardSpaces() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $sorry->setForwardSpaces(1);
        $this->assertEquals(1, $sorry->getForwardSpaces());
    }

    public function test_setBackwardSpaces() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $sorry->setBackwardSpaces(1);
        $this->assertEquals(1, $sorry->getBackwardSpaces());
    }

    public function test_isUsedUpCard() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        // True by default
        $this->assertTrue($sorry->isUsedUpCard());

        $sorry->setUsedUpCard(false);
        $this->assertFalse($sorry->isUsedUpCard());

        $sorry->setUsedUpCard(true);
        $this->assertTrue($sorry->isUsedUpCard());
    }

    public function test_setUsedUpCard() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $sorry->setUsedUpCard(false);
        $this->assertFalse($sorry->isUsedUpCard());

        $sorry->setUsedUpCard(true);
        $this->assertTrue($sorry->isUsedUpCard());
    }

    public function test_getTeamTurn() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $this->assertInternalType('int', $sorry->getTeamTurn());

        $sorry->setTeamTurn(0);
        $this->assertEquals(Sorry\Team::YELLOW, $sorry->getTeamTurn());

        $sorry->setTeamTurn(3);
        $this->assertEquals(3, $sorry->getTeamTurn());

    }

    public function test_setTeamTurn() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $sorry->setTeamTurn(0);
        $this->assertEquals(Sorry\Team::YELLOW, $sorry->getTeamTurn());

        $sorry->setTeamTurn(3);
        $this->assertEquals(3, $sorry->getTeamTurn());
    }

    public function test_getBoard() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $this->assertInstanceOf('Sorry\Board', $sorry->getBoard());
    }

    public function test_getMessage() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $this->assertNull($sorry->getMessage());

        $sorry->undo();
        $this->assertEquals("Cannot undo actions with the current card because no card has been drawn this turn.",
            $sorry->getMessage());
    }

    public function test_setCanSkip() {
        $colorsList = [Sorry\Team::YELLOW, Sorry\Team::BLUE, Sorry\Team::RED, Sorry\Team::GREEN];
        $sorry = new Sorry\Sorry($colorsList);

        $sorry->setCanSkip(false);
        $this->assertFalse($sorry->skipCard());

        $sorry->setCanSkip(true);
        $this->assertTrue($sorry->skipCard());
    }
}