<?php


namespace Sorry;


class LobbiesView extends View {
    private $error;     // Error message
    private $get;       // Get super global

    // Codes for each error type
    const INVALID_LENGTH = 0;

    // Map from error codes to the actual message.
    const ERROR_CODES = [
        self::INVALID_LENGTH => "Lobby name must be 1 to 20 characters long"
    ];

    public function __construct(Site $site, array $get) {
        parent::__construct($site);
        $this->addWebSocketInfo('lobbies');
        $this->setTitle('Lobbies');
        $this->addLink("instructions.php", "Instructions");
        $this->addLink("post/logout.php", "Log out");
        $this->get = $get;
        if(isset($get['e'])){
            $this->error = strip_tags($get['e']);
        }
    }

    public function presentBody() {
        $errorMsg = $this->presentError();
        $html = <<<HTML
<form action="post/lobbies.php" method="post">

<!--area for making a lobby -->
<div class="newform">
    <fieldset class="create">
    <legend>Create a Lobby</legend>
       <input type="hidden" name="lobbyID" value="-1">
       <div class="create">
HTML;
        $html .= "<div class='gap-bot-xs'><p class='none'>$errorMsg</p></div>";
        $html .= <<<HTML
        <div class="text"><input type="text" name="name" id="name" placeholder="Enter the Lobby Name"></div>
        <div class="lobby-button"><p class="games-button"><input class=" primary primary-sm" type="submit" value="Create" name="create"></p></div>
       </div>
    </fieldset>  
</div>
</form>
    <fieldset>
        <legend>Open Games</legend>

HTML;


        $allLobs = new Lobbies($this->site);
        $lobbies = $allLobs->getLobbies();

        if (count($lobbies) == 0) {
            $html .= "<p class='none'>No lobbies available, create a lobby to get started.</p></fieldset>";
            return $html;
        }
        $gameType = "Open";
        $buttonType = "primary";
        $lobbyStatus = "Join";
        $disabled = "";
        foreach ($lobbies as $lobby) {
            $disabled = "";
            $gameType = "Open";
            $buttonType = "primary";
            $lobbyStatus = "Join";
            $id = $lobby->getGameId();
            $lobbyName = $lobby->getName();
            $players = $lobby->getNumPlayers();

            if($players == 4) {
                $disabled = "disabled";
                $buttonType .= "-closed";
                $gameType = "Closed";
                $lobbyStatus = "Full";
            }
           $html .= <<<HTML
<form action="post/lobbies.php" method="post">
<div class="game-cell">
            <input type="hidden" name="lobbyID" value="$id">
            <div class="button"><p class="games-button"><input class=" $buttonType primary-sm" type="submit" value="$lobbyStatus" name="join" $disabled></p></div>
            <div class="lobby"><p class="lobby">$lobbyName</p></div>
            <div class="players"><p class="left"><label for="players">Players: $players</label></p></div>
            <div class="game-type"><p class="left"><label for="type">$gameType</label></p></div>
        </div>
</form>
HTML;
        }

        $html .= <<<HTML
    </fieldset>
HTML;


        return $html;
    }

    /**
     * Generate the error message for this page.
     * @return string The error message for this page
     */
    public function presentError() {
        return isset($this->error) ? self::ERROR_CODES[$this->error] : "";
    }
}