<?php
namespace Sorry;

/**
 * Class GameController. Controller for the Sorry! game.
 * @package Sorry
 */
class GameController {
    private $page = 'game.php';  // The next page we will go to
    private $actionDone = false;  // True of there was an action done
    private $leave = null;

    /**
     * Constructor.
     * @param Site $site Site object
     * @param User $user The user of this client
     * @param Game $game The game the user is in
     * @param Sorry $sorry Sorry object representing the current game state
     * @param array $post Values posted in request
     */
    public function __construct(Site $site, User $user, Game $game, Sorry $sorry, array $post) {
        $turnAllowed = $user !== null && $game !== null && $game->getId() != -1 && $game->isTurn($user);
        $hostAllowed = $user !== null && $game !== null && $game->getId() != -1 && $user->getId() == $game->getHost();

        if (isset($post['done']) && $turnAllowed) {
            $sorry->nextTurn();
            $this->actionDone = true;
        } else if (isset($post['skip']) && $turnAllowed) {
            $sorry->skipCard();
            $this->actionDone = true;
        } else if (isset($post['undo']) && $turnAllowed) {
            $sorry->undo();
            $this->actionDone = true;
        } else if (isset($post['forfeit'])) {
            $sorry->forfeit($game->getUserColor($user->getId()));
            $this->leave = $user;
            $this->actionDone = true;
        } else if (isset($post['draw']) && $turnAllowed) {
            $sorry->drawCard();
            $this->actionDone = true;
        } else if (isset($post['press']) && $turnAllowed) {
            $sorry->pressSpace($post['press']);
            $this->actionDone = true;
        } else if (isset($post['kick']) && $hostAllowed) {
            $sorry->forfeit($game->getUserColor($post['kick']));
            $this->leave = Database::getUser($site, $post['kick']);
            $this->actionDone = true;
        }

        if ($this->actionDone) {
            Database::updateGameState($site, $user, $sorry);
            if ($sorry->hasWon() !== Team::NONE) {
                Database::endGame($site, $user, $sorry->hasWon());
            } else if ($this->leave !== null) {
                Database::leaveGame($site, $this->leave);
            }
            WebSockets::pushToKey(WebSockets::generateKey($site, ['game', $game->getId()]));
        }
    }

    /**
     * Get the page that should be redirected to.
     * @return string The page that should be redirected to
     */
    public function getPage() {
        return $this->page;
    }
}