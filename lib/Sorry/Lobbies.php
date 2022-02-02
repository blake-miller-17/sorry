<?php

namespace Sorry;

/**
 * Class Lobbies representing the Lobbies table in the database
 * @package Sorry
 */
class Lobbies extends Table {
    // Names of the columns in the table
    const GAME_ID_COL = 'gameId';
    const USER_ID_COL = 'userId';

    /**
     * Constructor
     * @param $site Site The Site object
     */
    public function __construct(Site $site) {
        parent::__construct($site, "lobby");
    }

    /**
     * Get a lobby based on the game ID
     * @param $gameId int ID of the game
     * @return Lobby|null Lobby object if successful, null otherwise.
     */
    public function get(int $gameId) {
        $users = new Users($this->site);
        $userTable = $users->getTableName();
        $games = new Games($this->site);
        $gameTable = $games->getTableName();

        $sql =<<<SQL
SELECT u.id AS userId, u.name AS userName, g.id AS gameId, g.name AS gameName, g.host, g.yellow, g.green, g.red, g.blue FROM
$userTable AS u
INNER JOIN $this->tableName AS l ON u.id = l.userid
INNER JOIN $gameTable AS g ON l.gameid = g.id
WHERE l.gameid = ?
SQL;

        // Prepare and execute the statement
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($gameId));

        // Return null if the lobby doesn't exist
        if($statement->rowCount() === 0) {
            return null;
        }

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $this->generateLobbyFromRows($rows);
    }

    /**
     * Get a list of the lobbies in the database
     * @return array List of the lobbies in the database
     */
    public function getLobbies() {
        $users = new Users($this->site);
        $userTable = $users->getTableName();
        $games = new Games($this->site);
        $gameTable = $games->getTableName();

        $sql =<<<SQL
SELECT u.id AS userId, u.name AS userName, g.id AS gameId, g.name AS gameName, g.host, g.yellow, g.green, g.red, g.blue FROM
$userTable AS u
INNER JOIN $this->tableName AS l ON u.id = l.userid
INNER JOIN $gameTable AS g ON l.gameid = g.id
ORDER BY l.gameid
SQL;

        // Prepare and execute the statement
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute();

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $lobbies = [];
        $i = 0;
        while ($i < count($rows)) {
            $currentLobby = [];
            $currentGameId = $rows[$i]['gameId'];
            while ($i < count($rows) && $rows[$i]['gameId'] == $currentGameId) {
                $currentLobby[] = $rows[$i];
                $i++;
            }
            $lobbies[] = $this->generateLobbyFromRows($currentLobby);
        }

        return $lobbies;
    }

    /**
     * Generate a lobby object from the rows of the SQL query where all rows are from the same lobby.
     * @param array $rows The rows from the SQL query
     * @return Lobby Lobby objects generated
     */
    private function generateLobbyFromRows(array $rows) {
        // Create Lobby object
        $lobby = new Lobby($rows[0]['gameId'], $rows[0]['gameName']);

        // Add all the users and set their colors
        foreach ($rows as $row) {
            $lobby->addUser($row['userId'], $row['userName']);
        }

        // Set the host
        $lobby->setHostId($rows[0]['host']);

        // Set all the colors
        $lobby->setColor($rows[0]['yellow'], Team::YELLOW);
        $lobby->setColor($rows[0]['green'], Team::GREEN);
        $lobby->setColor($rows[0]['red'], Team::RED);
        $lobby->setColor($rows[0]['blue'], Team::BLUE);

        return $lobby;
    }

    /**
     * Modify a lobby based on the contents of a Lobby object
     * @param Lobby $lobby Lobby object for object with modified data
     * @return bool True if the update was successful
     */
    public function update(Lobby $lobby) {
        // Clear the current lobby and make a new one with the new data
        if ($this->clearLobby($lobby->getGameId())) {
            return $this->createLobby($lobby);
        }
        return false;
    }

    /**
     * Clear the users from a lobby for a game (Does not remove the game).
     * @param int $gameId The ID of the game the lobby is for
     * @return bool True if the clearing was successful
     */
    public function clearLobby(int $gameId) {
        $sql = <<<SQL
DELETE FROM $this->tableName
where gameId=?
SQL;

        // Prepare statement
        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);

        // Execute statement
        try {
            $noError = $statement->execute(array($gameId));
        } catch(\PDOException $e) {
            // SQL statement failed
            $noError = false;
        }

        // Only return true if there were no problems
        return $noError && $statement->rowCount() !== 0;
    }

    /**
     * Create a new lobby for a n existing game.
     * Does nothing if the game doesn't exist, it already has no lobby, or the provided lobby has no players.
     * @param Lobby $lobby Lobby data to make the new lobby from
     * @return bool True if the creation was successful.
     *              False the game doesn't exist or it already has no lobby, the provided lobby has no players
     */
    public function createLobby(Lobby $lobby) {
        $games = new Games($this->site);
        if ($games->exists($lobby->getGameId()) && !$this->exists($lobby->getGameId())
            && $lobby->getNumPlayers() > 0 && $games->get($lobby->getGameId())->getStatus() == Games::STATUS_LOBBY) {

            $sql = <<<SQL
INSERT INTO $this->tableName (gameId, userId)
VALUES (?, ?)
SQL;

            // Prepare statement
            $pdo = $this->pdo();
            $statement = $pdo->prepare($sql);

            // Execute statement
            $noError = true;
            try {
                // Insert each user into the lobby table
                foreach($lobby->getPlayers() as $userId => $name) {
                    if (!$statement->execute(array($lobby->getGameId(), $userId))) {
                        throw new \Exception();
                    }
                }
            } catch(\Exception $e) {
                // SQL statement failed
                $noError = false;
            }

            // Only return true if there were no problems
            if ($noError) {
                $games = new Games($this->site);
                $gamesName = $games->getTableName();
                $sql = <<<SQL
UPDATE $gamesName
SET host=?, yellow=?, green=?, red=?, blue=?
WHERE id=?
SQL;
                $statement = $pdo->prepare($sql);

                $colors = [
                    Team::YELLOW => -1,
                    Team::GREEN => -1,
                    Team::RED => -1,
                    Team::BLUE => -1
                ];

                foreach ($lobby->getColors() as $id => $color) {
                    $colors[$color] = $id;
                }

                try {
                    $noError = $statement->execute([
                        $lobby->getHostId(),
                        $colors[Team::YELLOW],
                        $colors[Team::GREEN],
                        $colors[Team::RED],
                        $colors[Team::BLUE],
                        $lobby->getGameId()
                    ]);
                } catch(\Exception $e) {
                    // SQL statement failed
                    $noError = false;
                }
                return $noError;
            }
        }
        return false;
    }

    /**
     * Determine if a game exists in the system.
     * @param int $gameId An email address.
     * @return bool True if $email is an existing email address
     */
    public function exists(int $gameId) {
        $sql =<<<SQL
SELECT * from $this->tableName
where gameid=?
SQL;

        $pdo = $this->pdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array($gameId));
        return $statement->rowCount() !== 0;
    }
}