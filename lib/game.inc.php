<?php
$game = Sorry\Database::getGame($site, $user); // Game the user is in
$lobby = null;
$sorry = null;                                 // State of the game the user is in
$turnName = null;                              // Name of the user whose turn it is
$redirect = null;                              // Page to redirect to

// Determine if redirect is necessary and unserialize the state in no redirect is necessary
if ($game === null && (!isset($noGame) || !$noGame)) {
    // User is not in a game
    $redirect = '';
} else if ($game !== null) {
    // Redirect based on the state the game is in
    switch($game->getStatus()) {
        case Sorry\Games::STATUS_LOBBY:
            $lobby = Sorry\Database::getLobby($site, $user);
            $redirect = 'lobby.php';
            break;
        case Sorry\Games::STATUS_ACTIVE:
            $sorry = unserialize($game->getState());
            $turnUser = Sorry\Database::getUser($site, $game->turnId());
            $turnName = $turnUser !== null ? $turnUser->getName() : 'Undefined';
            $redirect = 'game.php';
            break;
        case Sorry\Games::STATUS_FINISHED:
            $winner = Sorry\Team::getString(unserialize($game->getState())->hasWon());
            $_SESSION[Sorry\SessionNames::WINNER] = array("color" => unserialize($game->getState())->hasWon(), "user" => $winner);
            Sorry\Database::leaveGame($site, $user);
            $redirect = 'gameover.php';
            break;
        default:
            $redirect = '';
    }
}

// Redirect if necessary
if ((!isset($noRedirect) || !$noRedirect) && $redirect !== null
    && $redirect !== $currentPage) {
    $root = $site->getRoot();
    header("location: $root/$redirect");
}