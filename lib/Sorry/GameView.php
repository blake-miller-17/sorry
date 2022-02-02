<?php

namespace Sorry;

/**
 * Class GameView. View for the game page of the Sorry! game.
 * @package Sorry
 */
class GameView extends View {
    // Spaces that make up the draw pile
    const DECK_PILE = [85,86,87,101,102,103,117,118,119,133,134,135];

    private $gameState;       // State of the game from the model
    private $clickableSpaces; // Spaces that are clickable
    private $deckSpaces;      // Spaces that are on the draw pile and are clickable
    private $selectedSpaces;  // Spaces that are selected
    private $activeSpaces;    // Spaces that have pieces on them
    private $sorry;           // Current sorry object
    private $user;            // The user of this client
    private $game;            // The game the user is in
    private $isTurn;          // Is it currently the user's turn

    /**
     * GameView constructor.
     * @param Site $site The site object
     * @param User $user The user of this client
     * @param Game $game The game the user is in
     * @param Sorry $sorry Sorry object for the view to talk with
     * @param string $name Name of the person whose turn it is
     * @param int ID of the game the user is in
     */
    public function __construct(Site $site, User $user, Game $game, Sorry $sorry, string $name) {
        parent::__construct($site);
        $this->sorry = $sorry;
        $this->gameState = $this->sorry->getState();
        $this->clickableSpaces = $this->formatClickableSpaces($this->gameState->clickableIndices);
        $this->deckSpaces = $this->formatDeckSpaces($this->gameState->canDrawCard);
        $this->activeSpaces = $this->formatPieceState($this->gameState->activePieces,
            $this->gameState->starts, $this->gameState->homes);
        $this->selectedSpaces = $this->formatSelectedSpace($this->gameState->selectedIndex);
        $this->setTitle($name);
        $this->user = $user;
        $this->game = $game;
        $this->isTurn = $this->game->isTurn($this->user);
        $this->addLink("instructions.php", "Instructions");
        if ($game !== null && $game->getId() != -1) {
            $this->addWebSocketInfo('game');
            $this->addWebSocketInfo($game->getId());
        }
    }


    public function head() {
        return parent::head() . <<<SCRIPT
<script>
    setTimeout("location.href = 'game.php'", 15000);
</script>
SCRIPT;

    }

    /**
     * Gets all board indexes and converts to a format view can understand.
     * @param array $activePieces Array of indices
     * @param array $starts The number of pieces in all the start zones
     * @param array $homes The number of pieces in all the home zones
     * @return array of spaces where the index represents the space number
     * and value is the team
     */
    public function formatPieceState(array $activePieces = null, array $starts = null, array $homes = null) {
        $display = []; // Pieces that will pe displayed on the board

        // Get all active pieces on board (index is state_key, team_key)
        // Where the state_key=>index and team_key=>Team
        if($activePieces != null) {
            foreach($activePieces as $index) {
                $spaces = Conversions::indexToSpaces($index[Board::STATE_INDEX_KEY]);
                if (count($spaces) == 1) {
                    $display[$spaces[0]] = $index[Board::STATE_TEAM_KEY];
                }
            }
        }

        if($homes != null) {
            // Add pieces in the home zones
            foreach($homes as $team => $num_pieces) {
                for($i = 0; $i < $num_pieces; $i++) {
                    $display[Conversions::HOME_PIECE_SPACES[$team][$i]] = $team;
                }
            }
        }

        if($starts != null) {
            // Add pieces in the start zones
            foreach($starts as $team => $num_pieces) {
                for($i = 0; $i < $num_pieces; $i++) {
                    $display[Conversions::START_PIECE_SPACES[$team][$i]] = $team;
                }
            }
        }

        return $display;
    }

    /**
     * Convert clickable indices into clickable spaces.
     * @param array $clickableIndices Array of clickable indices
     * @return array Array of clickable spaces
     */
    public function formatClickableSpaces(array $clickableIndices) {
        $clickableSpaces = [];

        if($clickableIndices != null && count($clickableIndices) > 0) {
            foreach($clickableIndices as $index) {
                $spaces = Conversions::indexToSpaces($index);
                if(count($spaces) > 1) {
                    // Home or Start
                    $clickableSpaces =  array_merge($clickableSpaces, $spaces);
                } else if(count($spaces) == 1) {
                    // Regular
                    array_push($clickableSpaces, $spaces[0]);
                }
            }
        }
        return $clickableSpaces;
    }


    /**
     * Convert selected index into selected spaces.
     * @param BoardIndex $index Index to convert
     * @return array Array of selected spaces
     */
    public function formatSelectedSpace(BoardIndex $index = null) {
        return $index != null ? Conversions::indexToSpaces($index) : [];
    }

    /**
     * Generate the highlighted spaces on the draw pile
     * @param bool $canDrawCard bool True if player can draw card from deck
     * @return array Array of deck spaces
     */
    public function formatDeckSpaces(bool $canDrawCard) {
        return $canDrawCard ? self::DECK_PILE : [];
    }

    /**
     * Present the content of the game page.
     * @return string HTML for the content of the game page
     */
    public function presentContent() {
        return $this->presentPlayerList()
            . $this->presentBoard()
            . $this->presentButtons()
            . $this->presentMessage();
    }

    /** Will read a formatted state and tell the game where pieces and buttons should be presented
     * @return string an HTML string displaying all the valid information
     */
    public function presentBoard() {
        $html = "<div class = 'game'><div class='board'>";

        for ($rows = 0; $rows < 16; $rows++) {
            $html .= "<div class='row'>";
            for ($columns = 0; $columns < 16; $columns++) {
                $value = ($rows*16) + $columns;
                //Display the correct content in each cell
                $html .= $this->display_cell($value);
            }
            $html .= "</div>";
        }
        $html .= "</div></div>";
        return $html;
    }

    /**
     * Present the buttons below the board.
     * @return string HTML for the buttons below the board
     */
    public function presentButtons() {
        $disabled = $this->game->isTurn($this->user) ? '' : 'disabled';
        $buttonClass = $this->isTurn ? '' : '-closed';

        return <<<HTML
<div class='buttonBox'>
    <input type='submit' class='primary$buttonClass' name='done' value='Next Turn' $disabled>
    <input type='submit' class='primary$buttonClass' name='skip' value='Skip Card' $disabled>
    <input type='submit' class='primary$buttonClass' name='undo' value='Undo Card' $disabled>
    <input type='submit' class='primary' name='forfeit' value='Forfeit'>
</div>
HTML;
    }

    /**
     * Display a cell on the board grid
     * @param $space int the space to be formatted.
     * @return string html string representing the cell
     */
    public function display_cell(int$space) {
        $clickable = $this->isClickable($space); //clickable spaces on the board
        $clickableDeck = $this->isClickableDeck($space); //deck spaces that are clickable
        $selected = $this->isSelected($space); // pieces on the board that become selected
        $pieceTeam = $this->pieceTeam($space); // team of the piece either selected, or clickable
        $draw = $this->gameState->canDrawCard; // can the person draw a card, if so it becomes highlighted
        $discard = $this->gameState->topDiscard; // card last drawn, will be shown in the discard pile
        $canDraw = $this->gameState->cardInDrawPile; // is there a card left in the draw pile

        $html = "";
        if ($clickable || $clickableDeck) {
            $highlightType = $selected ? 'selected' : 'highlighted';
            $name = $clickableDeck ? 'draw' : 'press';
            if ($name == 'draw' && $space == 85 && $draw && $canDraw) {
                $html .= "<div class ='cell'><img class='deckImage' src='images/card_back.png' alt='back of sorry card'><button type ='submit' value='$space' name='$name' class='$highlightType cardSource'>
                            </button></div>";
            } else if (!$canDraw && $space == 85) {
                $html .= "<div class='cell $highlightType $pieceTeam'></div>";
            } else {
                $html .= "<div class ='cell'><button type = 'submit' value='$space' name='$name' class='$highlightType $pieceTeam'></button></div>";
            }
        } else if ($space == 85 && !$draw) {
            if (!$canDraw) {
                $html .= "<div class='cell notClickable $pieceTeam'></div>";
            } else {
                $html .= "<div class ='cell'><img class='deckImage' src='images/card_back.png ' alt='Deck image'></div>";
            }
            //if there are no cards drawn, the discard should be empty
        } else if ($discard == CardType::NONE && $space == 88) {
            $html .= "<div class='cell notClickable $pieceTeam'></div>";
        } else if ($discard != CardType::NONE && $space == 88) {
            $html .= "<div class ='cell'><img class='deckImage' src='images/card_" . CardType::getString($discard) . ".png' alt='Card " . CardType::getString($discard) . "'></div>";
        } else if ($pieceTeam != TEAM::NONE) {
            $html .= "<div class='cell notClickable $pieceTeam'></div>";
        }

        return $html;
    }

    /**
     * Generate the player list.
     * @return string HTML for the player list
     */
    public function presentPlayerList() {
        $kickButton = $this->user->getId() == $this->game->getHost();
        $users = [
            Team::getString(Team::YELLOW) => Database::getUser($this->site, $this->game->getColor(Team::YELLOW)),
            Team::getString(Team::GREEN) => Database::getUser($this->site, $this->game->getColor(Team::GREEN)),
            Team::getString(Team::RED) => Database::getUser($this->site, $this->game->getColor(Team::RED)),
            Team::getString(Team::BLUE) => Database::getUser($this->site, $this->game->getColor(Team::BLUE))
        ];

        $html = <<<HTML
<div class="playerList">
    <p class="title">Players:</p>
HTML;
        $userInstances = [];
        foreach ($users as $team => $instanceUser) {
            if ($instanceUser !== null) {
                $name = $instanceUser->getName();
                $id = $instanceUser->getId();
                $userInstance = "<p class='limit playerListEntry $team'>";
                if ($kickButton) {
                    $disabled = $instanceUser->getId() === $this->user->getId() ? 'disabled' : '';
                    $class = $disabled ? 'kickButton-closed' : 'kickButton';
                    $userInstance .= "<button type='submit' name='kick' value='$id' class='$class' $disabled><i class='material-icons'>person_remove</i></button>";
                }
                $userInstance .= "$name</p>";
                $userInstances[] = $userInstance;
            }
        }

        $html .= "<div class='row'>";
        foreach ($userInstances as $index => $instance) {
            if ($index == 2) {
                $html .= "</div><div class='row'>";
            }
            $html .= $instance;
        }
        if (count($userInstances) % 2 != 0) {
            $html .= "<p class='playerListEntry'></p>";
        }
        $html .= "</div>";

        $html .= <<<HTML
</div>
HTML;
        return $html;
    }

    /**
     * Determine if a space is clickable.
     * @param int $space checking for if the space is clickable
     * @return bool true if clickable, false if not
     */
    private function isClickable(int $space) {
       return in_array($space, $this->clickableSpaces);
    }

    /**
     * Determine if a space is a clickable space on the draw pile.
     * @param $space int checking for if the space is on a deck space and that its clickable
     * @return bool true if clickable in the deck, false if not
     */
    private function isClickableDeck(int $space) {
        return in_array($space, $this->deckSpaces);
    }

    /**
     * Determine if a space is selected.
     * @param $space int checking for if the space is selected
     * @return bool true if selected, false if not
     */
    private function isSelected(int $space) {
        return in_array($space, $this->selectedSpaces);
    }

    /**
     * Determine the team of the piece that is on a space.
     * @param int $space space on the board
     * @return string The team which is on this space
     */
    private function pieceTeam(int $space) {
        // Which teams turn it is
        foreach ($this->activeSpaces as $key => $value) {
            if ($space == $key) {
                $team = $value;
                $teamTurn = Team::getString($team);
                return $teamTurn .= "Piece";
            }
        }
        return "";
    }

    /**
     * Present the message to the player.
     * @return string The message to the player
     */
    public function presentMessage() {
        $message = $this->gameState->message;
        return $message != null ? "<p class='message'>$message</p>" : "<p class='message'>&nbsp;</p>";
    }
}