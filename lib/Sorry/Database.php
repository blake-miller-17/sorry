<?php

namespace Sorry;

/**
 * Class Database. Interface for interacting with the database
 * @package Sorry
 */
abstract class Database {
    /**
     * Login a user.
     * @param Site $site Site object
     * @param string $email Email address of the user
     * @param string $password Password of the user
     * @return User User object of logged in user, null if unsuccessful
     */
    public static function login(Site $site, string $email, string $password) {
        $users = new Users($site);
        return $users->login($email, $password);
    }

    /**
     * Edit a user with a user object.
     * @param Site $site Site object
     * @param User $user User object to edit with
     * @return bool True if the user was successfully edited
     */
    public static function editUser(Site $site, User $user) {
        $users = new Users($site);
        return $users->update($user);
    }

    /**
     * Add a user to the database.
     * @param Site $site Site object
     * @param string $email Email for the new user
     * @param string $name Name for the new user
     * @return string|null Error message on failure
     */
    public static function addUser(Site $site, string $email, string $name) {
        $users = new Users($site);
        $user = new User([
            Users::ID_COL => 0,
            Users::EMAIL_COL => $email,
            Users::NAME_COL => $name,
            Users::GAME_ID_COL => -1,
        ]);
        $mailer = new Email();
        return $users->add($user, $mailer);
    }

    /**
     * Get the user from the database
     * @param Site $site Site object
     * @param int $userId The user ID
     * @return null|User The user found
     */
    public static function getUser(Site $site, int $userId) {
        $users = new Users($site);
        return $users->get($userId);
    }

    /**
     * Set the password for a user.
     * @param Site $site Site object
     * @param User $user The logged in user
     * @param string $password
     * @return bool True if the password was successfully set
     */
    public static function setPassword(Site $site, User $user, string $password) {
        $users = new Users($site);
        $users->setPassword($user->getId(), $password);
        return true;
    }

    /**
     * Create a lobby with designated user as the host.
     * @param Site $site Site object
     * @param User $user The logged in user
     * @param string $gameName The name of the game being created
     * @return int ID of game created
     */
    public static function createLobby(Site $site, User $user, string $gameName) {
        if($user->getGameId() == -1) {
            $lobbies = new Lobbies($site);
            $games = new Games($site);
            $users = new Users($site);
            $game = new Game([
                'id' => 99,
                'name' => $gameName,
                'status' => Games::STATUS_LOBBY,
                'state' => '',
                'host' => $user->getId(),
                'yellow' => -1,
                'green' => -1,
                'red' => -1,
                'blue' => -1,
                'acknowledge' => 0
            ]);

            $gameId = $games->create($game);
            if($gameId !== 0) {
                $lobby = new Lobby($gameId, $gameName);
                $lobby->addUser($user->getId(), $user->getName());
                $lobbies->createLobby($lobby);
                $user->setGameId($gameId);
                $users->update($user);
                return $gameId;
            }
        }
        return -1;
    }

    /**
     * Get a list of the lobbies that are waiting to start.
     * @param Site $site Site object
     * @return array List of the bobbies that are waiting to start
     */
    public static function getLobbies(Site $site) {
        $lobbies = new Lobbies($site);
        return $lobbies->getLobbies();
    }

    /**
     * Get the lobby the logged in user is in
     * @param Site $site Site object
     * @param User $user The logged in user
     * @return Lobby|null The lobby the logged in user is in.
     *         Null if there is no logged in user or the user isn't in a lobby.
     */
    public static function getLobby(Site $site, User $user) {
        $lobbies = new Lobbies($site);
        if ($user !== null) {
            return $lobbies->get($user->getGameId());
        }
        return null;
    }

    /**
     * Join a lobby from the game ID.
     * @param Site $site Site object
     * @param User $user The logged in user
     * @param int $gameId The game ID of the lobby to join
     * @return bool True if the lobby was successfully joined
     */
    public static function joinLobby(Site $site, User $user, int $gameId) {
        if ($user !== null) {
            // Create Games and Lobbies objects
            $lobbies = new Lobbies($site);
            $users = new Users($site);

            // get the lobby to put the player in the lobby
            $lobby = $lobbies->get($gameId);

            //if add user was false, either couldn't join or full
            if($lobby->addUser($user->getId(), $user->getName())) {
                if($lobbies->update($lobby)) {
                    //Add the gameId to the user to put it into the game
                    return $users->setGameId($user->getId(), $gameId);
                }
            }
        }
        return false;
    }

    /**
     * Have a user leave the lobby that they are in.
     * @param Site $site Site object
     * @param User $user The logged in user
     * @return bool True if the user successfully left their lobby
     */
    public static function leaveLobby(Site $site, User $user) {
        // Get the objects for the leave
        $lobbies = new Lobbies($site);
        $games = new Games($site);
        $users = new Users($site);
        $gameId = $user->getGameId();
        $lobby = $lobbies->get($gameId);

        $lobby->removeUser($user->getId());
        //remove from game
        $user->setGameId(-1);
        $users->update($user);

        return $lobby->getNumPlayers() == 0 ? $games->remove($gameId) : $lobbies->update($lobby);
    }

    /**
     * Have a user start the game they are in a lobby for.
     * @param Site $site Site object
     * @param User $user The logged in user
     * @return bool True if the game was started
     */
    public static function startGame(Site $site, User $user) {
        $games = new Games($site);
        $lobbies = new Lobbies($site);
        $game = $games->get($user->getGameId());
        $lobby = $lobbies->get($user->getGameId());

        if (count($lobby->getColors()) == count($lobby->getPlayers()) && count($lobby->getPlayers()) >= 2) {
            $sorry = new Sorry(array_values($lobby->getColors()), $lobby->getColors()[$lobby->getHostId()]);
            $game->setStatus(Games::STATUS_ACTIVE);
            $game->setAcknowledge($lobby->getNumReady());
            $games->update($game);
            $lobbies->clearLobby($game->getId());
            self::updateGameState($site, $user, $sorry);
            return true;
        }
        return false;
    }

    /**
     * Select a color in the lobby.
     * @param Site $site Site object
     * @param User $user The logged in user
     * @param int $color Color being chosen
     * @return bool True if color was selected
     */
    public static function selectColor(Site $site, User $user, int $color) {
        if ($user !== null) {
            $lobby = Database::getLobby($site, $user);
            $lobbies = new Lobbies($site);

            $currentColor = $lobby->getColors()[$user->getId()];

            if($currentColor !== $color) {
                $lobby->setColor($user->getId(), $color);
            } else {
                $lobby->setColor($user->getId(), Team::NONE);
            }

            return $lobbies->update($lobby);
        }
        return false;
    }

    /**
     * Kick a user from a lobby.
     * @param Site $site Site object
     * @param User $user The logged in user
     * @param int $victimId ID of the user being kicked
     * @return bool True if the victim was successfully kicked from the lobby
     */
    public static function kickFromLobby(Site $site, User $user, int $victimId) {
        $games = new Games($site);
        $users = new Users($site);
        $game = $games->get($user->getGameId());
        $victimUser = $users->get($victimId);
        if($game !== null && $game->getStatus() == Games::STATUS_LOBBY
            && ($game->getHost() == $user->getId() || $user->getId() == $victimId) && $victimUser !== null
            && $victimUser->getGameId() == $game->getId()) {
            return self::leaveLobby($site, $victimUser);
        }
        return true;
    }

    /**
     * Get the game object for a game based on its ID.
     * @param Site $site Site object
     * @param User $user The logged in user
     * @return Game|null Game object for the designated game. Null on failure.
     */
    public static function getGame(Site $site, User $user) {
        $games = new Games($site);
        return $user !== null ? $games->get($user->getGameId()) : null;
    }

    /**
     * Update the state of the game the user is in with the information from a Sorry object.
     * this function does NOT update the status, host, name or acknowledge fields.
     * @param Site $site Site object
     * @param User $user The logged in user
     * @param Sorry $sorry Sorry object to use in the update
     * @return bool True if the update was successful
     */
    public static function updateGameState(Site $site, User $user, Sorry $sorry) {
        if ($site !== null && $user !== null && $sorry !== null) {
            $games = new Games($site);
            $game = $games->get($user->getGameId());
            if ($game !== null) {
                $game->setState(serialize($sorry));
                return $games->update($game);
            }
        }
        return false;
    }

    /**
     * End the game a user is in.
     * @param Site $site Site object
     * @param User $user The logged in user
     * @param int $winner The winner of the game
     * @return bool True if the game was ended
     */
    public static function endGame(Site $site, User $user, int $winner) {
        if ($site !== null && $user !== null && Team::isTeam($winner)) {
            $games = new Games($site);
            $game = $games->get($user->getGameId());
            if ($game !== null) {
                // Designate the winner and that the status is finished
                $game->setStatus(Games::STATUS_FINISHED);
                return $games->update($game);
            }
        }
        return false;
    }

    /**
     * Have a user leave their game.
     * @param Site $site Site object
     * @param User $user User leaving their game
     * @return boolean True if the user actually left a game
     */
    public static function leaveGame(Site $site, User $user) {
        if ($site !== null && $user !== null) {
            $games = new Games($site);
            $users = new Users($site);
            $game = $games->get($user->getGameId());
            if ($game !== null) {
                $games->removeFromGame($user);
            }
            return $users->setGameId($user->getId(), -1);
        }
        return false;
    }
}