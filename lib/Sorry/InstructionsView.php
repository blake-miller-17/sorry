<?php


namespace Sorry;


class InstructionsView extends View {
    public function __construct(Site $site) {
        parent::__construct($site);
        $this->setTitle('Instructions');
    }

    public function presentBody() {
        $html = <<<HTML
<div class="Instructions">
    <h1>Rules of Sorry:</h1>
    <ul>
        <li>Sorry is a game played between 2-4 players. Each player gets four pawns of their color (Red, Blue, Green, Yellow) that are placed in the Start area.</li>
        <li>The are 45 cards in a deck. Five 1 cards and four of the rest, all listed below.</li>
        <li>At the start your turn click on the Sorry card back to draw a card. From there follow the rules of the card.</li>
        <li>After you finish moving and your turn press the done button.</li>
        <li>When a card says forwards it means clockwise and backwards means counter clockwise.</li>
        <li>A player wins by getting all their pawns to Home.</li>
        <li>If a pawn lands on the start of a slide space (excluding its own color) it immediately moves to the end of the slide sending any pawns in its way (including yours) to Start.</li>
    </ul>

    <h1>Special Rules:</h1>
    <ul>
        <li>Pawns may jump over another pawn while moving.</li>
        <li>Only one pawn may occupy a square.</li>
        <li>If a pawn lands on an opponents pawn, it moves back to its own Start.</li>
        <li>The last five spaces before Home are the Safety Zones.</li>
        <li>Only pawns of the same color are allowed there.</li>
        <li>In the Safety Zones pawns are protected from being bumped, switched or swapped.</li>
        <li>A pawn can leave a Safety Zone if the player draws a card that make its move backwards outside the Safety Zone.</li>
    </ul>
    
    <h1>Application Features:</h1>
    <ul>
        <li>When in a lobby, only the host may start the game.</li>
        <li>There must be at least 2 players in the lobby and all players must have chosen their color.</li>
        <li>The host has the ability to kick players from the lobby with the button next to the player's name.</li>
        <li>During a game, the host has the ability to kick players from the game with the button next to the player's name.</li>
    </ul>

    <p>&nbsp;</p>
    <p>For more information go to the <a href="https://en.wikipedia.org/wiki/Sorry!_(game)">Sorry Wiki</a>!</p>

    <h1>Cards:</h1>
</div>
<div class="card-row">
    <div class="card-column">
        <p><img src="images/card_1.png" height="256" width="192" alt="Sorry 1 Card">
            <img src="images/card_8.png" height="256" width="192" alt="Sorry 8 Card"></p>
    </div>
    <div class="card-column">
        <p><img src="images/card_2.png" height="256" width="192" alt="Sorry 2 Card">
            <img src="images/card_10.png" height="256" width="192" alt="Sorry 10 Card"></p>
    </div>
    <div class="card-column">
        <p><img src="images/card_3.png" height="256" width="192" alt="Sorry 3 Card">
            <img src="images/card_11.png" height="256" width="192" alt="Sorry 11 Card"></p>
    </div>
    <div class="card-column">
        <p><img src="images/card_4.png" height="256" width="192" alt="Sorry 4 Card">
            <img src="images/card_12.png" height="256" width="192" alt="Sorry 12 Card"></p>
    </div>
    <div class="card-column">
        <p><img src="images/card_5.png" height="256" width="192" alt="Sorry 5 Card">
            <img src="images/card_sorry.png" height="256" width="192" alt="Sorry Card"></p>
    </div>
    <div class="card-column">
        <p><img src="images/card_7.png" height="256" width="192" alt="Sorry 7 Card">
            <img src="images/card_back.png" height="256" width="192" alt="Card Back"></p>
    </div>
</div>
<div class="Instructions">
<h1>Team Information</h1>
    <p>Team Name: Team 17</p>
    <ul>
        <li>Sadeem Boji</li>
        <li>Blake Miller</li>
        <li>Chris Nosowsky</li>
        <li>Reid Shinabarker</li>
        <li>Greg Szczerba</li>
    </ul>
</div>
HTML;
        return $html;
    }
}