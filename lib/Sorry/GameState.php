<?php
namespace Sorry;

/**
 * Class GameState. Data class to hold the formatted state of the Sorry! game.
 * @package Sorry
 */
class GameState {
    public $activePieces; // State of the pieces on the board. Elements in format: [self::STATE_INDEX_KEY => index, self::STATE_TEAM_KEY => team]
    public $starts; // Number of pieces in each team's start. Elements in format: [Team => numPieces]
    public $homes; // Number of pieces in each team's home. Elements in format: [Team => numPieces]
    public $clickableIndices; // All spaces that are highlighted. Elements in format: index
    public $selectedIndex; // The space selected by the player. Format: spaceIndex
    public $topDiscard; // The type of card that is visible in the discard pile
    public $canDrawCard; // Can the player draw a card?
    public $teamTurn = Team::NONE; // Team of current turn
    public $message; // Message to be displayed to the player
    public $cardInDrawPile; // True if there is a card left in the draw pile
}