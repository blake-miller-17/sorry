<?php

namespace Sorry;

class LobbyView extends View {
    private $user; // The user of this client

    /**
     * LobbyView constructor.
     * @param Site $site The site
     * @param int $gameId The ID of the game this lobby is for
     * @param User $user The user of this client
     */
    public function __construct(Site $site, int $gameId, User $user) {
        parent::__construct($site);
        $this->user = $user;
        $lobby = Database::getLobby($this->site, $this->user);

        $lobbyName = $lobby != Null ? $lobby->getName() : "Lobby";
        $this->setTitle($lobbyName);
        $this->addLink("instructions.php", "Instructions");
        if ($gameId != -1) {
            $this->addWebSocketInfo('lobby');
            $this->addWebSocketInfo($gameId);
        }
    }

    /**
     * Present the content of the lobby page.
     * @return string The content of the lobby page
     */
    public function presentContent() {

        $lobby = Database::getLobby($this->site, $this->user);

        $userId = $this->user->getId();

        // If this user is the host, they will have access to more buttons, so create the html attribute tag that will
        // be used for this person
        $hostAttribute = "disabled";
        if ($userId == $lobby->getHostId()) {
            $hostAttribute = "";
        }

        $colorList = array(Team::YELLOW, Team::GREEN, Team::RED, Team::BLUE);

        $bulletList = "";

        $noColor = [];
        $noColorID = [];
        // Creates two lists to store players who don't have a color
        // Also lets me index the lists in order 0 onwards
        foreach ($lobby->getPlayers() as $playerid => $player){
            if(!isset($lobby->getColors()[$playerid])){
                array_push($noColor, $player);
                array_push($noColorID, $playerid);
            }
        }

        $index = 0;

        foreach ($colorList as $color) {
            $playerColor = Team::getString($color) . "Player";

            if (in_array($color, $lobby->getColors())){
                $playerID = array_search($color, $lobby->getColors());
                $player = $lobby->getPlayers()[$playerID];
                $bulletList .= $this->displayNames($playerColor, $player, $playerID, $userId, $lobby);
            }
            elseif ($index < count($noColor)){
                $bulletList .= $this->displayNames("teamlessPlayer", $noColor[$index], $noColorID[$index], $userId, $lobby);
                $index += 1;
            }
            else{
                $bulletList.= '<p class=' . $playerColor . '>' . "&nbsp" . '</p>';
            }
        }

        $inputColor = "";
        foreach ($colorList as $color) {
            $colorStr = Team::getString($color);
            if (in_array($color, $lobby->getColors())){
                $playerID = array_search($color, $lobby->getColors());

                if($playerID == $userId){
                    $disabled = "";
                    $closed = "-invert";
                }
                else{
                    $disabled = 'disabled';
                    $closed = "-closed";
                }
                $inputColor .= '<p><input type=submit class=Lobby'.$colorStr .$closed . ' name =cb'.$colorStr .' id=cb'.$colorStr .' value='. $colorStr . ' ' . $disabled .'></p>';
            }
            else{
                $inputColor .= '<p><input type=submit class=Lobby'.$colorStr . ' name =cb'.$colorStr .' id=cb'.$colorStr .' value='. $colorStr .'></p>';
            }

        }

        $html = <<<HTML
<div class="lobbyPage">
    <div class="lobbyBox">
        <form method="post" action="post/lobby.php">
        <div class="twoboxes">
            <div class="colorOptions">
                $inputColor
                
            </div>
        <div class="showPlayers">
                $bulletList
        </div>
        </div>
            <div class="buttonBox gap">
                <input type="submit" class="primary" name="new" value="Start Game" $hostAttribute>
                <button type="submit" class="primary" name="kick" value=$userId>Leave Lobby</button>
            </div>
            <p class="selectMessage">To start the game the lobby must have at least 2 players and everyone must select their color.</p>
        </form>
    </div>
</div>

HTML;
        return $html;
    }

    /**
     * Display a users name
     * @param $color string color of a user
     * @param $user string name of a user
     * @param $id int id of a user
     * @param $userId int user id to check if the user is the host to display the kick button
     * @param $lobby lobby object
     * @return string Name to be appended
     */
    public function displayNames(string $color, string $user, int $id, int $userId, lobby $lobby){
        $name = "";
        if ($id == $lobby->getHostId()) {
            $name .= '<p class=' . $color . '>' . "Host: " . $user . '</p>';
        }
        else {
            $name .= '<p class=' . $color . '>';
            if ($userId == $lobby->getHostId()) {
                $name .= "<button type='submit' class='kickButton' name='kick' value='" . $id . "'><i class='material-icons'>person_remove</i></button>";
            }
            $name .= $user . '</p>';
        }
        return $name;
    }
}