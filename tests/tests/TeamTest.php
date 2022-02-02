<?php

class TeamTest extends \PHPUnit\Framework\TestCase {
    public function test_getString() {
        //$Team = $this->getMockForAbstractClass("\Sorry\Team");
        $this->assertNull(\Sorry\Team::getString(5));
        $this->assertEquals("Yellow", \Sorry\Team::getString(0));
        $this->assertEquals("Green", \Sorry\Team::getString(1));
        $this->assertEquals("Red", \Sorry\Team::getString(2));
        $this->assertEquals("Blue", \Sorry\Team::getString(3));
        $this->assertEquals("None", \Sorry\Team::getString(4));
    }

    public function test_isTeam() {
        // Test valid teams
        $this->assertTrue(Sorry\Team::isTeam(Sorry\Team::YELLOW));
        $this->assertTrue(Sorry\Team::isTeam(Sorry\Team::GREEN));
        $this->assertTrue(Sorry\Team::isTeam(Sorry\Team::RED));
        $this->assertTrue(Sorry\Team::isTeam(Sorry\Team::BLUE));
        $this->assertTrue(Sorry\Team::isTeam(Sorry\Team::NONE));

        // Test invalid teams
        $this->assertFalse(Sorry\Team::isTeam(-1));
        $this->assertFalse(Sorry\Team::isTeam(5));
    }
}