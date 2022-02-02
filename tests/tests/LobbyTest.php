<?php


class LobbyTest extends \PHPUnit\Framework\TestCase {
    public function test_construct() {
        $lobby = new Sorry\Lobby(1, 'Lobby Name');
        $this->assertInstanceOf('Sorry\Lobby', $lobby);
        $this->assertEquals(1, $lobby->getGameId());
        $this->assertEquals(-1, $lobby->getHostId());
        $this->assertEquals('Lobby Name', $lobby->getName());
        $this->assertEquals(0, $lobby->getNumPlayers());
        $this->assertEquals(0, $lobby->getNumReady());
        $this->assertEquals([], $lobby->getPlayers());
        $this->assertEquals([], $lobby->getColors());
    }

    public function test_addUser() {
        // Lobby should be initially empty
        $lobby = new Sorry\Lobby(1, 'Lobby Name');
        $this->assertEquals(-1, $lobby->getHostId());
        $this->assertEquals([], $lobby->getPlayers());

        // Add first user
        $this->assertTrue($lobby->addUser(1, 'Player1'));
        $this->assertEquals(1, $lobby->getNumPlayers());
        $this->assertEquals('Player1', $lobby->getPlayers()[1]);
        $this->assertEquals(1, $lobby->getHostId());

        // Add second user
        $this->assertTrue($lobby->addUser(2, 'Player2'));
        $this->assertEquals(2, $lobby->getNumPlayers());
        $this->assertEquals('Player1', $lobby->getPlayers()[1]);
        $this->assertEquals('Player2', $lobby->getPlayers()[2]);
        $this->assertEquals(1, $lobby->getHostId());

        // Fill lobby
        $this->assertTrue($lobby->addUser(3, 'Player3'));
        $this->assertTrue($lobby->addUser(4, 'Player4'));
        $this->assertEquals(4, $lobby->getNumPlayers());
        $this->assertEquals('Player1', $lobby->getPlayers()[1]);
        $this->assertEquals('Player2', $lobby->getPlayers()[2]);
        $this->assertEquals('Player3', $lobby->getPlayers()[3]);
        $this->assertEquals('Player4', $lobby->getPlayers()[4]);
        $this->assertEquals(1, $lobby->getHostId());

        // Attempt adding to a full lobby
        $this->assertFalse($lobby->addUser(5, 'Player5'));
        $this->assertEquals('Player1', $lobby->getPlayers()[1]);
        $this->assertEquals('Player2', $lobby->getPlayers()[2]);
        $this->assertEquals('Player3', $lobby->getPlayers()[3]);
        $this->assertEquals('Player4', $lobby->getPlayers()[4]);
        $this->assertEquals(1, $lobby->getHostId());
    }

    public function test_setColor() {
        // Setup a lobby
        $lobby = new Sorry\Lobby(1, 'Lobby Name');
        $lobby->addUser(1, 'Player1');
        $lobby->addUser(2, 'Player2');
        $this->assertEquals(0, $lobby->getNumReady());

        // Set a color for a user not in the lobby
        $this->assertFalse($lobby->setColor(0, Sorry\Team::YELLOW));
        $this->assertEquals(0, $lobby->getNumReady());

        // Set an invalid color
        $this->assertFalse($lobby->setColor(0, -1));
        $this->assertEquals(0, $lobby->getNumReady());

        // Set a valid color to a valid user
        $this->assertTrue($lobby->setColor(1, Sorry\Team::YELLOW));
        $this->assertEquals(1, $lobby->getNumReady());
        $this->assertEquals(Sorry\Team::YELLOW, $lobby->getColors()[1]);

        // Set to a color already selected by this user
        $this->assertFalse($lobby->setColor(1, Sorry\Team::YELLOW));
        $this->assertEquals(1, $lobby->getNumReady());
        $this->assertEquals(Sorry\Team::YELLOW, $lobby->getColors()[1]);

        // Set to a color already selected by another user
        $this->assertFalse($lobby->setColor(2, Sorry\Team::YELLOW));
        $this->assertEquals(1, $lobby->getNumReady());
        $this->assertEquals(Sorry\Team::YELLOW, $lobby->getColors()[1]);

        // Unset a color from a user that doesn't have a color
        $this->assertTrue($lobby->setColor(2, Sorry\Team::NONE));
        $this->assertEquals(1, $lobby->getNumReady());
        $this->assertEquals(Sorry\Team::YELLOW, $lobby->getColors()[1]);

        // Unset a color from a user that has a color
        $this->assertTrue($lobby->setColor(1, Sorry\Team::NONE));
        $this->assertEquals(0, $lobby->getNumReady());

        // Set a color that used to belong to another user
        $this->assertTrue($lobby->setColor(2, Sorry\Team::YELLOW));
        $this->assertEquals(1, $lobby->getNumReady());
        $this->assertEquals(Sorry\Team::YELLOW, $lobby->getColors()[2]);
    }

    public function test_removeUser() {
        // Setup a lobby
        $lobby = new Sorry\Lobby(1, 'Lobby Name');
        $lobby->addUser(1, 'Player1');
        $lobby->addUser(2, 'Player2');
        $lobby->addUser(3, 'Player3');
        $lobby->addUser(4, 'Player4');
        $lobby->setColor(2, Sorry\Team::BLUE);
        $lobby->setColor(4, Sorry\Team::RED);
        $this->assertEquals(4, $lobby->getNumPlayers());
        $this->assertEquals(2, $lobby->getNumReady());
        $this->assertEquals(1, $lobby->getHostId());

        // Remove a user that isn't in the lobby
        $this->assertFalse($lobby->removeUser(5));
        $this->assertEquals(4, $lobby->getNumPlayers());
        $this->assertEquals(2, $lobby->getNumReady());
        $this->assertEquals(1, $lobby->getHostId());

        // Remove a user that is not ready
        $this->assertTrue($lobby->removeUser(3));
        $this->assertEquals(3, $lobby->getNumPlayers());
        $this->assertEquals(2, $lobby->getNumReady());
        $this->assertEquals(1, $lobby->getHostId());

        // Remove a user that is ready
        $this->assertTrue($lobby->removeUser(4));
        $this->assertEquals(2, $lobby->getNumPlayers());
        $this->assertEquals(1, $lobby->getNumReady());
        $this->assertEquals(1, $lobby->getHostId());

        // Remove the host when there is another player
        $this->assertTrue($lobby->removeUser(1));
        $this->assertEquals(1, $lobby->getNumPlayers());
        $this->assertEquals(1, $lobby->getNumReady());
        $this->assertEquals(2, $lobby->getHostId());

        // Remove the host when they are the last player
        $this->assertTrue($lobby->removeUser(2));
        $this->assertEquals(0, $lobby->getNumPlayers());
        $this->assertEquals(0, $lobby->getNumReady());
        $this->assertEquals(-1, $lobby->getHostId());
    }

    public function test_setHostId() {
        // Set up a lobby
        $lobby = new Sorry\Lobby(1, 'Test Name');
        $lobby->addUser(2, 'Test User 1');
        $lobby->addUser(3, 'Test USer 2');
        $this->assertEquals(2, $lobby->getHostId());

        // Set to the user that is already the host
        $this->assertTrue($lobby->setHostId(2));
        $this->assertEquals(2, $lobby->getHostId());

        // Set to another user in the lobby
        $this->assertTrue($lobby->setHostId(3));
        $this->assertEquals(3, $lobby->getHostId());

        // Set to a user not in the lobby
        $this->assertFalse($lobby->setHostId(99));
        $this->assertEquals(3, $lobby->getHostId());
    }
}