<?php

namespace Sorry;


class LobbiesController {
    private $redirect = "lobbies.php"; // Page to redirect to
    private $actionDone = false;              // True of there was an action done

    /**
     * LobbiesController constructor
     * @param Site $site Site object
     * @param User $user Currently logged in user
     * @param array $post $_POST contents
     */
    public function __construct(Site $site, User $user, array $post) {
        $gameId = -1;
        $this->actionDone = false;
        if(isset($post['join'])) {
            //join the correct lobby
            $gameId = strip_tags($post['lobbyID']);
            if(Database::joinLobby($site, $user, $gameId)) {
                $this->actionDone = true;
            }
        } else if(isset($post['create'])) {
            // Create a new lobby
            $lobbyName = strip_tags($post['name']);
            if(!(strlen($lobbyName) > 0 && strlen($lobbyName) <= 20)) {
                $this->redirect = "lobbies.php?e=" . LobbiesView::INVALID_LENGTH;
            } else {
                $gameId = Database::createLobby($site, $user, $lobbyName);
                if($gameId !== -1) {
                    $this->actionDone = true;
                }
            }
        }
        // Push changes to other clients if something was changed
        if($this->actionDone) {
            WebSockets::pushToKey(WebSockets::generateKey($site, ['lobbies']));
            WebSockets::pushToKey(WebSockets::generateKey($site, ['lobby', $gameId]));
        }
    }

    /**
     * Get the link to redirect the user to
     * @return mixed Redirect Link
     */
    public function getRedirect() {
        return $this->redirect;
    }
}