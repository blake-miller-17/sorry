<?php


namespace Sorry;

/**
 * Class Game representing a game in the database
 * @package Sorry
 */
class Game {
    private $id;          // The internal ID of the game
    private $name;        // The name of the game
    private $status;      // The status of the game
    private $state;       // The state of the game
    private $host;        // The user ID of the host of this game
    private $acknowledge; // The number of players who have yet to acknowledge the end of the game
    private $colors;      // User that are playing each color

    /**
     * Game constructor.
     * @param array $row Row from the game table in the database
     */
    public function __construct(array $row) {
        $this->id = $row[Games::ID_COL];
        $this->name = $row[Games::NAME_COL];
        $this->status = $row[Games::STATUS_COL];
        $this->state = $row[Games::STATE_COL];
        $this->host = $row[Games::HOST_COL];
        $this->colors = [
            Team::YELLOW => $row[Games::YELLOW_COL],
            Team::GREEN => $row[Games::GREEN_COL],
            Team::RED => $row[Games::RED_COL],
            Team::BLUE => $row[Games::BLUE_COL]
        ];
        $this->acknowledge = $row[Games::ACKNOWLEDGE_COL];
    }

    /**
     * Determine if it is a user's turn.
     * @param User $user User the test is being done on
     * @return bool True if it is the user's turn
     */
    public function isTurn(User $user) {
        $teamTurn = unserialize($this->state)->getTeamTurn();
        return $user !== null && isset($this->colors[$teamTurn]) && $user->getId() == $this->colors[$teamTurn];
    }

    /**
     * Get the ID of the user whose turn it is.
     * @return int The ID of the user whose turn it is
     */
    public function turnId() {
        $teamTurn = unserialize($this->state)->getTeamTurn();
        return isset($this->colors[$teamTurn]) ? $this->colors[$teamTurn] : -1;
    }

    /**
     * Get the color of a user in this game.
     * @param int $userId The ID of the user to test
     * @return int The team of the user
     */
    public function getUserColor(int $userId) {
        $team = Team::NONE;
        foreach($this->colors as $color => $playerId) {
            if ($userId == $playerId) {
                $team = $color;
            }
        }
        return $team;
    }

    /**
     * Set a certain user ID to a color.
     * @param int $color Color to set
     * @param int $userId ID to set the color to
     * @return bool True on success
     */
    public function setColor(int $color, int $userId) {
        if (Team::isTeam($color) && $color != Team::NONE) {
            $this->colors[$color] = $userId;
            return true;
        }
        return false;
    }

    /**
     * Get the ID of the user with a certain color.
     * @param int $color Color to get
     * @return int The ID of the user with the color
     */
    public function getColor(int $color) {
        if (Team::isTeam($color) && $color != Team::NONE) {
            return $this->colors[$color];
        }
        return -1;
    }

    /**
     * Set a new host for the game.
     */
    public function updateHost() {
        if (!in_array($this->host, $this->colors)) {
            $this->host = -1;
            foreach($this->colors as $userId) {
                if ($userId != -1) {
                    $this->host = $userId;
                    return;
                }
            }
        }
    }

    /**
     * Getter for the internal ID of the game.
     * @return int The internal ID of the game
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Getter for the name of the game.
     * @return string The name of the game
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Getter for the status of the game.
     * @return int The status of the game
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Getter for the state of the game.
     * @return string The state of the game
     */
    public function getState() {
        return $this->state;
    }

    /**
     * Getter for the user ID of the host of the game.
     * @return int The user ID of the host of the game
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Getter for the number of players who have yet to acknowledge the end of the game.
     * @return int The number of player who have yet to acknowledge the end of the game
     */
    public function getAcknowledge() {
        return $this->acknowledge;
    }

    /**
     * Setter for the status of the game.
     * @param int $status The status of the game
     */
    public function setStatus(int $status) {
        $this->status = $status;
    }

    /**
     * Setter for the state of the game.
     * @param string $state The state of the game
     */
    public function setState(string $state) {
        $this->state = $state;
    }

    /**
     * Setter for the user Id of the host of the game.
     * @param int $host The user ID of the host of the game
     */
    public function setHost(int $host) {
        $this->host = $host;
    }

    /**
     * Setter for the number of players who have yet to acknowledge the end of the game.
     * @param int $acknowledge The number of players who have yet to acknowledge the end of the game
     */
    public function setAcknowledge(int $acknowledge) {
        $this->acknowledge = $acknowledge;
    }
}