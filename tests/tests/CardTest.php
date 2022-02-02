<?php
use Sorry\Card as Card;
use Sorry\CardType as Type;

class CardTest extends \PHPUnit\Framework\TestCase {

    public function test_construct() {
        $card = new Card(Type::ONE);

        $this->assertInstanceOf('Sorry\Card',$card);
    }

    public function test_cardType() {
        $card = new Card(Type::NONE);
        $this->cardInfoTest($card,Type::NONE,0,0,False,False,False,False);

        $card = new Card(Type::ONE);
        $this->cardInfoTest($card,Type::ONE,1,0,False,False,True,False);

        $card = new Card(Type::TWO);
        $this->cardInfoTest($card,Type::TWO,2,0,False,False,True,True);

        $card = new Card(Type::THREE);
        $this->cardInfoTest($card,Type::THREE,3,0,False,False,False,False);

        $card = new Card(Type::FOUR);
        $this->cardInfoTest($card,Type::FOUR,0,4,False,False,False,False);

        $card = new Card(Type::FIVE);
        $this->cardInfoTest($card,Type::FIVE,5,0,False,False,False,False);

        $card = new Card(Type::SEVEN);
        $this->cardInfoTest($card,Type::SEVEN,7,0,True,False,False,False);

        $card = new Card(Type::EIGHT);
        $this->cardInfoTest($card,Type::EIGHT,8,0,False,False,False,False);

        $card = new Card(Type::TEN);
        $this->cardInfoTest($card,Type::TEN,10,1,False,False,False,False);

        $card = new Card(Type::ELEVEN);
        $this->cardInfoTest($card,Type::ELEVEN,11,0,False,True,False,False);

        $card = new Card(Type::TWELVE);
        $this->cardInfoTest($card,Type::TWELVE,12,0,False,False,False,False);

        $card = new Card(Type::SORRY);
        $this->cardInfoTest($card,Type::SORRY,0,0,False,False,False,False);
    }

    private function cardInfoTest($card,$type,$forward,$backward,$split,$swap,$start,$draw){
        $this->assertEquals($type,$card->getCardType());
        $this->assertEquals($forward,$card->getForwardSpaces());
        $this->assertEquals($backward,$card->getBackwardSpaces());
        $this->assertEquals($split,$card->isSplit());
        $this->assertEquals($swap,$card->isSwap());
        $this->assertEquals($start,$card->isStart());
        $this->assertEquals($draw,$card->isDrawAgain());
    }

}