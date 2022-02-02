<?php

namespace Sorry;

/**
 * Class Games representing the game database in the database.
 * @package Sorry
 */
class Games extends Table {
    // The column names
    const ID_COL = 'id';
    const NAME_COL = 'name';
    const STATUS_COL = 'status';
    const HOST_COL = 'host';
    const YELLOW_COL = 'yellow';
    const GREEN_COL = 'green';
    const RED_COL = 'red';
    const BLUE_COL = 'blue';
    const STATE_COL = 'state';
    const ACKNOWLEDGE_COL = 'acknowledge';

    // Different statuses the game can be in
    const STATUS_LOBBY = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_FINISHED = 2;

    /**
     * Constructor
     * @param $site Site The Site object
     */
    public function __construct(Site $site) {
        parent::__construct($site, "game");
    }

    /**
     * Get a user based on the id
     * @param $id int ID of the user
     * @return Game|null User object if successful, null otherwise.
     */
    public function get(int $id) {
        $sql =<<<SQL
SELECT * from $this->tableName
where id=?
SQL;

        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);

        $statement->execute(array($id));
        if($statement->rowCount() === 0) {
            return null;
        }

        return new Game($statement->fetch(\PDO::FETCH_ASSOC));
    }

    /**
     * Modify a game record based on the contents of a Game object
     * @param Game $game Game object for object with modified data
     * @return bool True if successful, false if failed or game does not exist
     */
    public function update(Game $game) {
        $sql =<<<SQL
UPDATE $this->tableName
SET status=?, state=?, host=?, yellow=?, green=?, red=?, blue=?, acknowledge=?
where id=?
SQL;

        // Prepare statement
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);

        // Execute statement
        try {
            $noError = $statement->execute(array(
                $game->getstatus(),
                $game->getState(),
                $game->getHost(),
                $game->getColor(Team::YELLOW),
                $game->getColor(Team::GREEN),
                $game->getColor(Team::RED),
                $game->getColor(Team::BLUE),
                $game->getAcknowledge(),
                $game->getId()
            ));
        } catch(\PDOException $e) {
            // SQL statement failed
            $noError = false;
        }

        // Only return true if there were no problems
        return $noError && $statement->rowCount() !== 0;
    }

    /**
     * Create a game in the database.
     * @param Game $game Game object to create from
     * @return int The gameID inserted
     */
    public function create(Game $game) {
        $sql =<<<SQL
INSERT INTO $this->tableName (name, status, state, host, yellow, green, red, blue, acknowledge)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
SQL;

        // Prepare statement
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        $statement = $pdo->prepare($sql);

        // Execute statement
        try {
            $noError = $statement->execute(array(
                $game->getName(),
                $game->getstatus(),
                $game->getState(),
                $game->getHost(),
                $game->getColor(Team::YELLOW),
                $game->getColor(Team::GREEN),
                $game->getColor(Team::RED),
                $game->getColor(Team::BLUE),
                $game->getAcknowledge()
            ));
        } catch(\PDOException $e) {
            // SQL statement failed
            $noError = false;
        }

        $lastID = (int) $this->site->pdo()->lastInsertId();
        $pdo->commit();

        // Only return true if there were no problems
        return $lastID;
    }

    /**
     * Remove a game from the database as well as removing the contained players' association with it.
     * This will remove all remnants of a game regardless of which pieces are present.
     * @param int $gameId The id of the game to remove
     * @return bool
     */
    public function remove(int $gameId) {
        $lobbies = new Lobbies($this->site);
        $users = new Users($this->site);

        $sql = <<<SQL
DELETE FROM $this->tableName
WHERE id=?
SQL;

        // Remove the game record
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        try {
            $success = $statement->execute([$gameId]);
        } catch(\PDOException $e) {
            // SQL statement failed
            $success = false;
        }

        // Clear the lobby for this game
        if (!$lobbies->clearLobby($gameId)) {
            $success = false;
        }

        // Clear any player's association with this game
        if (!$users->clearGameAssociations($gameId)) {
            $success = false;
        }

        return $success;
    }

    /**
     * Determine if a game exists in the system.
     * @param int $gameId An email address.
     * @return bool True if $email is an existing email address
     */
    public function exists(int $gameId) {
        $sql =<<<SQL
SELECT * from $this->tableName
where id=?
SQL;

        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($gameId));
        return $statement->rowCount() !== 0;
    }

    /**
     * Remove a user from a game.
     * @param User $user User leaving the game
     * @return bool True if the game was successfully left
     */
    public function removeFromGame(User $user) {
        $pdo = $this->pdo();
        $pdo->exec('LOCK TABLES sorry_game WRITE;');

        $game = $this->get($user->getGameId());

        if ($game->getUserColor($user->getId()) !== Team::NONE) {

            // Remove the user from the color list
            $game->setColor($game->getUserColor($user->getId()), -1);
            $game->updateHost();

            // Acknowledge the ending of the game and remove the game if the last one to leave
            $game->setAcknowledge($game->getAcknowledge() - 1);
            if ($game->getAcknowledge() == 0) {
                $success = $this->remove($game->getId());
            } else {
                $success = $this->update($game);
            }
            $pdo->exec('UNLOCK TABLES;');
            return $success;
        }
        $pdo->exec('UNLOCK TABLES;');
        return false;
    }
}