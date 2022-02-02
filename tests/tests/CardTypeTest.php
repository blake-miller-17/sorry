<?php


class CardTypeTest extends \PHPUnit\Framework\TestCase
{

    public function test_getString() {
       //$card = CardType::NONE;
       //$CardType = $this->getMockForAbstractClass("\Sorry\CardType");
       $this->assertNull(\Sorry\CardType::getString(12));
       $this->assertEquals("", \Sorry\CardType::getString(0));
       $this->assertEquals("1", \Sorry\CardType::getString(1));
       $this->assertEquals("2", \Sorry\CardType::getString(2));
       $this->assertEquals("3", \Sorry\CardType::getString(3));
       $this->assertEquals("4", \Sorry\CardType::getString(4));
       $this->assertEquals("5", \Sorry\CardType::getString(5));
       $this->assertEquals("7", \Sorry\CardType::getString(6));
       $this->assertEquals("8", \Sorry\CardType::getString(7));
       $this->assertEquals("10", \Sorry\CardType::getString(8));
       $this->assertEquals("11", \Sorry\CardType::getString(9));
       $this->assertEquals("12", \Sorry\CardType::getString(10));
       $this->assertEquals("sorry", \Sorry\CardType::getString(11));
    }
}