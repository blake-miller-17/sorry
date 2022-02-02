<?php
namespace Sorry;

/**
 * Class Card representing a Sorry! card.
 * @package Sorry
 */
class Card {
    private $cardType = CardType::NONE; // The type of card
    private $forwardSpaces = 0; // How many spaces forward a player could move
    private $backwardSpaces = 0; // How many spaces backward a player could move
    private $split = false; // If a card's movement can be split between two pieces
    private $swap = false; // If a card allows two pieces to swap
    private $start = false; // If a card allows a player to leave from start
    private $drawAgain = false; // If a card allows a player to draw again

    /**
     * Card constructor.
     * Every card needs each of its field filled out and made per card type.
     * @param int $type The type of the card to be created
     */
    public function __construct(int $type) {
        switch($type){
            case CardType::NONE:
                $this->MakeCard($type,0,0,false,false,false,false);
                break;

            case CardType::ONE:
                $this->MakeCard($type,1,0,false,false,true,false);
                break;

            case CardType::TWO:
                $this->MakeCard($type,2,0,false,false,true,true);
                break;

            case CardType::THREE:
                $this->MakeCard($type,3,0,false,false,false,false);
                break;

            case CardType::FOUR:
                $this->MakeCard($type,0,4,false,false,false,false);
                break;

            case CardType::FIVE:
                $this->MakeCard($type,5,0,false,false,false,false);
                break;

            case CardType::SEVEN:
                $this->MakeCard($type,7,0,true,false,false,false);
                break;

            case CardType::EIGHT:
                $this->MakeCard($type,8,0,false,false,false,false);
                break;

            case CardType::TEN:
                $this->MakeCard($type,10,1,false,false,false,false);
                break;

            case CardType::ELEVEN:
                $this->MakeCard($type,11,0,false,true,false,false);
                break;

            case CardType::TWELVE:
                $this->MakeCard($type,12,0,false,false,false,false);
                break;

            case CardType::SORRY:
                $this->MakeCard($type,0,0,false,false,false,false);
        }
    }

    /**
     * Makes a card by filling out the required fields.
     * @param int $type The type of the card to make
     * @param int $forward The number of forward spaces the card will allow
     * @param int $backward The number of backward spaces the card will allow
     * @param bool $split True if this card will allow splitting movement between two pieces
     * @param bool $swap True if this card will allow swapping with an opponent's piece
     * @param bool $start True if this card will allow starting a new piece
     * @param bool $draw True if this card will allow drawing another card after the this one is used
     */
    private function MakeCard(int $type, int $forward, int $backward, bool $split, bool $swap, bool $start, bool $draw){
        $this->cardType = $type;
        $this->forwardSpaces = $forward;
        $this->backwardSpaces = $backward;
        $this->split = $split;
        $this->swap = $swap;
        $this->start = $start;
        $this->drawAgain = $draw;
    }

    /**
     * Getter for the type of the card.
     * @return int the type of the card
     */
    public function getCardType() {
        return $this->cardType;
    }

    /**
     * Getter for the number of spaces this card allows moving forwards.
     * @return int The number of spaces this card allows moving forwards
     */
    public function getForwardSpaces() {
        return $this->forwardSpaces;
    }

    /**
     * Getter for the number of spaces this card allows moving backwards.
     * @return int The number of spaces this card allows moving backwards
     */
    public function getBackwardSpaces() {
        return $this->backwardSpaces;
    }

    /**
     * Getter for if this cards allows splitting movement between two pieces.
     * @return bool True if this cards allows splitting movement between two pieces
     */
    public function isSplit() {
        return $this->split;
    }

    /**
     * Getter for if this card allows for drawing another card after using this one.
     * @return bool True if this card allows for drawing another card after using this one.
     */
    public function isDrawAgain() {
        return $this->drawAgain;
    }

    /**
     * Getter for if this card allows starting a new piece.
     * @return bool True if this card allows starting a new piece
     */
    public function isStart() {
        return $this->start;
    }

    /**
     * Getter for if this card allows swapping with an opponent's piece.
     * @return bool True if this card allows swapping with an opponent's piece.
     */
    public function isSwap() {
        return $this->swap;
    }
}

