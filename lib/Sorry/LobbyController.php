<?php

namespace Sorry;

class LobbyController {
    private $redirect = "lobby.php";
    private $actionDone;

    /**
     * LobbyController constructor
     * @param Site $site Site object
     * @param User $user Player
     * @param Game $game The game
     * @param array $post $_POST contents
     */
    public function __construct(Site $site, User $user, Game $game, array $post) {

        if(isset($post['kick'])) {
            $id = strip_tags($post['kick']);
            if(Database::kickFromLobby($site, $user, $id)) {
                $this->actionDone = true;
            }
        }

        if(isset($post['new'])) {
            if(Database::startGame($site, $user)) {
                $this->actionDone = true;
            }
        }

        if (isset($post['cbYellow'])) {
            if(Database::selectColor($site, $user, Team::YELLOW)){
                $this->actionDone = true;
            }
        }

        if (isset($post['cbGreen'])) {
            if(Database::selectColor($site, $user, Team::GREEN)){
                $this->actionDone = true;
            }
        }

        if (isset($post['cbRed'])) {
            if(Database::selectColor($site, $user, Team::RED)){
                $this->actionDone = true;
            }
        }

        if (isset($post['cbBlue'])) {
            if(Database::selectColor($site, $user, Team::BLUE)){
                $this->actionDone = true;
            }
        }

        if($this->actionDone) {
            WebSockets::pushToKey(WebSockets::generateKey($site, ['lobby', $game->getId()]));
            if (isset($post['kick']) or isset($post['new'])) {
                WebSockets::pushToKey(WebSockets::generateKey($site, ['lobbies']));
            }
        }
    }

    /** Get the link to redirect the user to
     * @return mixed Redirect Link
     */
    public function getRedirect() {
        return $this->redirect;
    }
}