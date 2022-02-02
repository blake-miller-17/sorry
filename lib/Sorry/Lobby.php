<?php

namespace Sorry;

/**
 * Class Lobby representing a lobby in the database.
 * @package Sorry
 */
class Lobby {
    const MAX_PLAYERS = 4; // Maximum number of players in a lobby

    private $gameId;       // Internal ID of the game this lobby is for
    private $hostId = -1;  // ID of the user that's the host of the lobby
    private $name;         // Name of the lobby
    private $players = []; // Array mapping user IDs to their names
    private $colors = [];  // Array mapping user IDs to the color they chose

    /**
     * Lobby constructor.
     * @param int $gameId The internal ID of the game this lobby is for
     * @param string $name The name of the lobby
     */
    public function __construct(int $gameId, string $name) {
        $this->gameId = $gameId;
        $this->name = $name;
    }

    /**
     * Add a user to the lobby.
     * @param int $userId Id of the user to add
     * @param string $name Name of the user in
     * @return bool True if the addition was successful
     */
    public function addUser(int $userId, string $name) {
        // Add the user if they're not already in the lobby and there is room
        if (!isset($this->players[$userId]) && $this->getNumPlayers() < self::MAX_PLAYERS) {
            $this->players[$userId] = $name;

            // If this is the first user, make them the host
            if (count($this->players) == 1) {
                $this->hostId = $userId;
            }
            return true;
        }
        return false;
    }

    /**
     * Set the color of a player in the lobby.
     * @param int $userId User ID to set the color of
     * @param int $color Color to set the player to
     * @return bool True if the color was set
     */
    public function setColor(int $userId, int $color) {
        if (isset($this->players[$userId]) && !in_array($color, array_values($this->colors)) && Team::isTeam($color)) {
            if ($color == Team::NONE) {
                unset($this->colors[$userId]);
            } else {
                $this->colors[$userId] = $color;
            }
            return true;
        }
        return false;
    }

    /**
     * Remove a user from the lobby.
     * @param int $userId User to remove from the lobby
     * @return bool True if the removal was successful
     */
    public function removeUser(int $userId) {
        if (isset($this->players[$userId])) {
            // Remove the user from the list of players
            unset($this->players[$userId]);

            // Unselect this user's color
            if (isset($this->colors[$userId])) {
                unset($this->colors[$userId]);
            }

            // If the user was the host, assign a random new host
            if ($userId == $this->hostId) {
                if (count($this->players) > 0) {
                    $this->hostId = array_keys($this->players)[rand(0, count($this->players) - 1)];
                } else {
                    $this->hostId = -1;
                }
            }

            return true;
        }
        return false;
    }

    /**
     * Set the host of the lobby.
     * @param int $userId ID of the user to be the host
     * @return bool True if the host was successfully set
     */
    public function setHostId(int $userId) {
        if (isset($this->players[$userId])) {
            $this->hostId = $userId;
            return true;
        }
        return false;
    }

    /**
     * Get the number of players in the lobby.
     * @return int The number of players in the lobby
     */
    public function getNumPlayers() {
        return count($this->players);
    }

    /**
     * Get the number of players who are ready.
     * @return int The number of players who are ready
     */
    public function getNumReady() {
        return count($this->colors);
    }

    /**
     * Getter for the ID of the game this lobby is for.
     * @return int The ID of the game this lobby is for
     */
    public function getGameId() {
        return $this->gameId;
    }

    /**
     * Getter for the ID of the host of this lobby.
     * @return int The ID of the host of this lobby
     */
    public function getHostId() {
        return $this->hostId;
    }

    /**
     * Getter for the name of this lobby.
     * @return string The name of this lobby
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Getter for the mapping of player IDs to their names.
     * @return array Mapping of player IDs to their names
     */
    public function getPlayers() {
        return $this->players;
    }

    /**
     * Getter for the mapping of player IDs to colors.
     * @return array Mapping of player IDs to colors
     */
    public function getColors() {
        return $this->colors;
    }
}