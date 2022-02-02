<?php

class DeckTest extends \PHPUnit\Framework\TestCase {

    public function test_construct() {
        $deck = new Sorry\Deck();
        $this->assertInstanceOf('Sorry\Deck',$deck);

        $this->assertEquals(0, $deck->getNextIndex());

        $this->assertEquals(Sorry\CardType::ONE,$deck->getDeck()[0]);
        $this->assertEquals(Sorry\CardType::TWO,$deck->getDeck()[5]);
        $this->assertEquals(Sorry\CardType::THREE,$deck->getDeck()[9]);
        $this->assertEquals(Sorry\CardType::FOUR,$deck->getDeck()[13]);
        $this->assertEquals(Sorry\CardType::FIVE,$deck->getDeck()[17]);
        $this->assertEquals(Sorry\CardType::SEVEN,$deck->getDeck()[21]);
        $this->assertEquals(Sorry\CardType::EIGHT,$deck->getDeck()[25]);
        $this->assertEquals(Sorry\CardType::TEN,$deck->getDeck()[29]);
        $this->assertEquals(Sorry\CardType::ELEVEN,$deck->getDeck()[33]);
        $this->assertEquals(Sorry\CardType::TWELVE,$deck->getDeck()[37]);
        $this->assertEquals(Sorry\CardType::SORRY,$deck->getDeck()[41]);
    }

    public function test_shuffle(){
        $deck = new Sorry\Deck();

        // Move index
        $deck->drawCard();
        $this->assertEquals(1, $deck->getNextIndex());

        // Shuffle
        $deck->shuffle();
        $this->assertTrue(is_array($deck->getDeck()));
        $this->assertEquals(0, $deck->getNextIndex());
    }

    public function test_drawCard(){
        $deck = new Sorry\Deck();

        //
        // Test that the cards are being drawn in order
        //

        $this->assertEquals(0, $deck->getNextIndex());
        $type = $deck->drawCard()->getCardType();
        $this->assertEquals($deck->getDeck()[0], $type);
        $this->assertEquals(1, $deck->getNextIndex());
        $type = $deck->drawCard()->getCardType();
        $this->assertEquals(2, $deck->getNextIndex());
        $this->assertEquals($deck->getDeck()[1], $type);

        //
        // Test that The deck is reshuffled when no cards are left
        //

        // Move to the end of the deck
        for ($i = 2; $i < count($deck->getDeck()); $i++) {
            $deck->drawCard();
        }
        $this->assertEquals(count($deck->getDeck()), $deck->getNextIndex());

        // Draw when there are no cards left
        $type = $deck->drawCard()->getCardType();
        $this->assertEquals(1, $deck->getNextIndex());
        $this->assertEquals($deck->getDeck()[0], $type);
    }

    public function test_getTopDiscard() {
        $deck = new Sorry\Deck();

        $this->assertEquals(Sorry\CardType::NONE, $deck->getTopDiscard());
        $deck->drawCard();
        $this->assertEquals($deck->getTopDiscard(), $deck->getDeck()[0]);
    }

    public function test_isDeckEmpty() {
        $deck = new Sorry\Deck();

        //
        // Initial test
        //

        $this->assertFalse($deck->isDeckEmpty());
        $this->assertCount(45, $deck->getDeck());
        $this->assertEquals(0, $deck->getNextIndex());

        //
        // Middle test
        //
        $deck->setNextIndex(44);
        $this->assertFalse($deck->isDeckEmpty());

        //
        // Test empty deck
        //
        $deck->setNextIndex(45);
        $this->assertTrue($deck->isDeckEmpty());

        $deck->setNextIndex(46);
        $this->assertTrue($deck->isDeckEmpty());
    }
}