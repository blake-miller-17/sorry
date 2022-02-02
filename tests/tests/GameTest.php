<?php

class GameTest extends \PHPUnit\Framework\TestCase{
    public function test_construct() {
        $game = new Sorry\Game([
            Sorry\Games::ID_COL => 99,
            Sorry\Games::NAME_COL => 'Test Name',
            Sorry\Games::STATUS_COL => Sorry\Games::STATUS_ACTIVE,
            Sorry\Games::STATE_COL => 'Test State',
            Sorry\Games::HOST_COL => 1,
            Sorry\Games::YELLOW_COL => 1,
            Sorry\Games::GREEN_COL => 2,
            Sorry\Games::RED_COL => 3,
            Sorry\Games::BLUE_COL => 4,
            Sorry\Games::ACKNOWLEDGE_COL => 4
        ]);

        $this->assertInstanceOf('Sorry\Game', $game);
        $this->assertEquals(99, $game->getId());
        $this->assertEquals('Test Name', $game->getName());
        $this->assertEquals(Sorry\Games::STATUS_ACTIVE, $game->getStatus());
        $this->assertEquals('Test State', $game->getState());
        $this->assertEquals(1, $game->getHost());
        $this->assertEquals(1, $game->getColor(Sorry\Team::YELLOW));
        $this->assertEquals(2, $game->getColor(Sorry\Team::GREEN));
        $this->assertEquals(3, $game->getColor(Sorry\Team::RED));
        $this->assertEquals(4, $game->getColor(Sorry\Team::BLUE));
        $this->assertEquals(4, $game->getAcknowledge());
    }

    public function test_setters() {
        // Create game
        $game = new Sorry\Game([
            Sorry\Games::ID_COL => 0,
            Sorry\Games::NAME_COL => '',
            Sorry\Games::STATUS_COL => 0,
            Sorry\Games::STATE_COL => '',
            Sorry\Games::HOST_COL => 0,
            Sorry\Games::YELLOW_COL => 0,
            Sorry\Games::GREEN_COL => 0,
            Sorry\Games::RED_COL => 0,
            Sorry\Games::BLUE_COL => 0,
            Sorry\Games::ACKNOWLEDGE_COL => 0
        ]);

        $this->assertEquals(0, $game->getStatus());
        $this->assertEquals('', $game->getState());
        $this->assertEquals(0, $game->getHost());
        $this->assertEquals(0, $game->getColor(Sorry\Team::YELLOW));
        $this->assertEquals(0, $game->getColor(Sorry\Team::GREEN));
        $this->assertEquals(0, $game->getColor(Sorry\Team::RED));
        $this->assertEquals(0, $game->getColor(Sorry\Team::BLUE));
        $this->assertEquals(0, $game->getAcknowledge());

        // Alter all settable fields
        $game->setStatus(Sorry\Games::STATUS_ACTIVE);
        $game->setState('Test State');
        $game->setHost(1);
        $game->setColor(Sorry\Team::YELLOW, 1);
        $game->setColor(Sorry\Team::GREEN, 2);
        $game->setColor(Sorry\Team::RED, 3);
        $game->setColor(Sorry\Team::BLUE, 4);
        $game->setAcknowledge(4);

        $this->assertEquals(Sorry\Games::STATUS_ACTIVE, $game->getStatus());
        $this->assertEquals('Test State', $game->getState());
        $this->assertEquals(1, $game->getHost());
        $this->assertEquals(1, $game->getColor(Sorry\Team::YELLOW));
        $this->assertEquals(2, $game->getColor(Sorry\Team::GREEN));
        $this->assertEquals(3, $game->getColor(Sorry\Team::RED));
        $this->assertEquals(4, $game->getColor(Sorry\Team::BLUE));
        $this->assertEquals(4, $game->getAcknowledge());
    }
}