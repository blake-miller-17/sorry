<?php

class GameStateTest extends \PHPUnit\Framework\TestCase {
    public function test_construct() {
        $gameState = new Sorry\GameState();
        $this->assertInstanceOf('Sorry\GameState', $gameState);

        //
        // Initial test
        //
        $this->assertEquals(Sorry\Team::NONE, $gameState->teamTurn);
        $this->assertNull($gameState->activePieces);
        $this->assertNull($gameState->starts);
        $this->assertNull($gameState->homes);
        $this->assertNull($gameState->clickableIndices);
        $this->assertNull($gameState->selectedIndex);
        $this->assertNull($gameState->topDiscard);
        $this->assertNull($gameState->canDrawCard);
        $this->assertNotNull($gameState->teamTurn);
        $this->assertNull($gameState->message);
        $this->assertNull($gameState->cardInDrawPile);

        //
        // Static test
        //
        $gameState->activePieces = [
            [Sorry\Board::STATE_INDEX_KEY => new Sorry\BoardIndex(0, 1),
                Sorry\Board::STATE_TEAM_KEY => Sorry\Team::YELLOW],
            [Sorry\Board::STATE_INDEX_KEY => new Sorry\BoardIndex(15, 1),
                Sorry\Board::STATE_TEAM_KEY => Sorry\Team::GREEN],
            [Sorry\Board::STATE_INDEX_KEY => new Sorry\BoardIndex(30, 1),
                Sorry\Board::STATE_TEAM_KEY => Sorry\Team::RED],
            [Sorry\Board::STATE_INDEX_KEY => new Sorry\BoardIndex(45, 1),
                Sorry\Board::STATE_TEAM_KEY => Sorry\Team::BLUE]
        ];
        $gameState->starts = [Sorry\Team::YELLOW => 4, Sorry\Team::GREEN => 3,
            Sorry\Team::RED => 2, Sorry\Team::BLUE => 1];
        $gameState->homes = [Sorry\Team::YELLOW => 1, Sorry\Team::GREEN => 2,
            Sorry\Team::RED => 3, Sorry\Team::BLUE => 4];
        $gameState->clickableIndices = [new Sorry\BoardIndex(0, 1)];
        $gameState->selectedIndex = new Sorry\BoardIndex(0, 1);
        $gameState->topDiscard = Sorry\CardType::ELEVEN;
        $gameState->canDrawCard = True;
        $gameState->message = "TEST";
        $gameState->teamTurn = Sorry\Team::YELLOW;
        $gameState->cardInDrawPile = True;

        $this->assertCount(4, $gameState->activePieces);
        $this->assertInstanceOf('Sorry\BoardIndex', $gameState->activePieces[0][Sorry\Board::STATE_INDEX_KEY]);
        $this->assertEquals(0, $gameState->activePieces[0][Sorry\Board::STATE_TEAM_KEY]);

        $this->assertCount(4, $gameState->starts);
        $this->assertEquals(4, $gameState->starts[Sorry\Team::YELLOW]);

        $this->assertCount(4, $gameState->homes);
        $this->assertEquals(1, $gameState->homes[Sorry\Team::YELLOW]);

        $this->assertCount(1, $gameState->clickableIndices);
        $this->assertInstanceOf('Sorry\BoardIndex', $gameState->clickableIndices[0]);

        $this->assertEquals(0, $gameState->selectedIndex->getPerim());
        $this->assertEquals(1, $gameState->selectedIndex->getBranch());
        $this->assertInstanceOf('Sorry\BoardIndex', $gameState->selectedIndex);

        $this->assertEquals(Sorry\CardType::ELEVEN, $gameState->topDiscard);
        $this->assertInternalType('int', $gameState->topDiscard);

        $this->assertTrue($gameState->canDrawCard);
        $this->assertInternalType('boolean', $gameState->canDrawCard);

        $this->assertEquals("TEST", $gameState->message);
        $this->assertInternalType('string', $gameState->message);

        $this->assertEquals(Sorry\Team::YELLOW, $gameState->teamTurn);
        $this->assertInternalType('int', $gameState->teamTurn);

        $this->assertTrue($gameState->cardInDrawPile);
        $this->assertInternalType('boolean', $gameState->cardInDrawPile);
    }

}