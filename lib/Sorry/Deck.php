<?php
namespace Sorry;

/**
 * Class Deck representing a Sorry! deck.
 * @package Sorry
 */
class Deck {
    // Sorted deck of all the cards
    private $deck = [
        CardType::ONE, CardType::ONE, CardType::ONE, CardType::ONE, CardType::ONE,
        CardType::TWO, CardType::TWO, CardType::TWO, CardType::TWO,
        CardType::THREE, CardType::THREE, CardType::THREE, CardType::THREE,
        CardType::FOUR, CardType::FOUR, CardType::FOUR, CardType::FOUR,
        CardType::FIVE, CardType::FIVE, CardType::FIVE, CardType::FIVE,
        CardType::SEVEN, CardType::SEVEN, CardType::SEVEN, CardType::SEVEN,
        CardType::EIGHT, CardType::EIGHT, CardType::EIGHT, CardType::EIGHT,
        CardType::TEN, CardType::TEN, CardType::TEN, CardType::TEN,
        CardType::ELEVEN, CardType::ELEVEN, CardType::ELEVEN, CardType::ELEVEN,
        CardType::TWELVE, CardType::TWELVE, CardType::TWELVE, CardType::TWELVE,
        CardType::SORRY, CardType::SORRY, CardType::SORRY, CardType::SORRY
    ];

    private $nextIndex = 0; // The index of the next card to be drawn

    /**
     * Draw a card from the deck.
     * @return Card The card that was drawn
     */
    public function drawCard() {
        // Shuffle the deck if there are no cards left to be drawn
        if($this->nextIndex >= count($this->deck)) {
            $this->shuffle();
        }

        // Draw the next card
        $card = new Card($this->deck[$this->nextIndex]); // Create card object from the type
        $this->nextIndex++; // Move to the next index in the deck
        return $card;
    }

    /**
     * Getter for the type of the top card in the discard pile.
     * @return int The type of the top card on the discard pile
     */
    public function getTopDiscard() {
        return $this->nextIndex > 0 ? $this->deck[$this->nextIndex-1] : CardType::NONE;
    }

    /**
     * Shuffles the deck and resets the index.
     */
    public function shuffle() {
        shuffle($this->deck);
        $this->nextIndex = 0;
    }

    /**
     * Getter for the array containing the card order.
     * @return array The array containing the card order
     */
    public function getDeck() {
        return $this->deck;
    }

    /**
     * Getter for the index in the card order of the next card.
     * @return int The index in the card order of the next card
     */
    public function getNextIndex() {
        return $this->nextIndex;
    }

    /**
     * Setter for the next index to grab in the deck pile
     * @param int The next index
     */
    public function setNextIndex($nextIndex) {
        $this->nextIndex = $nextIndex;
    }

    /**
     * Determines if the deck is empty
     * @return boolean True if the deck is empty
     */
    public function isDeckEmpty() {
        return $this->nextIndex >= count($this->deck);
    }
}