<?php


class UserTest extends \PHPUnit\Framework\TestCase {
    public function test_construct() {
        $user = new Sorry\User([
            Sorry\Users::ID_COL => 99,
            Sorry\Users::NAME_COL => 'Test Name',
            Sorry\Users::EMAIL_COL => 'Test Email',
            Sorry\Users::GAME_ID_COL => 50,
        ]);

        $this->assertInstanceOf('Sorry\User', $user);
        $this->assertEquals(99, $user->getId());
        $this->assertEquals('Test Name', $user->getName());
        $this->assertEquals('Test Email', $user->getEmail());
        $this->assertEquals(50, $user->getGameId());
    }

    public function test_setters() {
        // Create user
        $user = new Sorry\User([
            Sorry\Users::ID_COL => 0,
            Sorry\Users::NAME_COL => '',
            Sorry\Users::EMAIL_COL => '',
            Sorry\Users::GAME_ID_COL => 0,
        ]);

        $this->assertEquals('', $user->getName());
        $this->assertEquals('', $user->getEmail());
        $this->assertEquals(0, $user->getGameId());

        // Alter all settable fields
        $user->setName('Test Name');
        $user->setEmail('Test Email');
        $user->setGameId(45);

        $this->assertEquals('Test Name', $user->getName());
        $this->assertEquals('Test Email', $user->getEmail());
        $this->assertEquals(45, $user->getGameId());
    }
}