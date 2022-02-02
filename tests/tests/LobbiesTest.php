<?php


class LobbiesTest extends \PHPUnit\Framework\TestCase{
    private static $site;
    private static $game1State;
    private static $game2State;
    private static $game3State;
    private static $game4State;
    private static $game5State;

    public static function setUpBeforeClass() {
        self::$site = new Sorry\Site();
        self::$game1State = serialize(new Sorry\Sorry([Sorry\Team::BLUE, Sorry\Team::GREEN]));
        self::$game2State = serialize(new Sorry\Sorry([Sorry\Team::BLUE, Sorry\Team::YELLOW]));
        self::$game3State = serialize(new Sorry\Sorry([Sorry\Team::BLUE, Sorry\Team::RED]));
        self::$game4State = serialize(new Sorry\Sorry([Sorry\Team::GREEN, Sorry\Team::YELLOW]));
        self::$game5State = serialize(new Sorry\Sorry([Sorry\Team::GREEN, Sorry\Team::RED]));
        $localize  = require 'localize.inc.php';
        if(is_callable($localize)) {
            $localize(self::$site);
        }
    }

    protected function setUp() {
        $games = new Sorry\Games(self::$site);
        $gamesTable = $games->getTableName();
        $users = new Sorry\Users(self::$site);
        $usersTable = $users->getTableName();
        $lobbies = new Sorry\Lobbies(self::$site);
        $lobbiesTable = $lobbies->getTableName();

        $sql = <<<SQL
delete from $gamesTable;
insert into $gamesTable(id, name, status, state, host, yellow, green, red, blue, acknowledge)
values
(7, "game1", 0, ?, 8, 9, 8, 10, 7, 4),
(8, "game2", 0, ?, 12, -1, 12, -1, -1, 1),
(9, "game3", 0, ?, 13, -1, -1, -1, -1, 1),
(10, "game4", 1, ?, 14, 14, 15, -1, -1, 1),
(11, "game5", 0, ?, 16, 16, -1, -1, -1, 1);

delete from $usersTable;
insert into $usersTable(id, email, name, password, salt, gameId)
values
(7, "user1@email.com", "User1", "90e39522a183650d2bbe61817641c683e7e7dd02f75b7ceaf5ff316a5c0a20a7", "wNU+`33iWZf%l%R%", 7),
(8, "user2@email.com", "User2", "91d9e02230ab8e741d9f3a4d825b0af8bb2e8bd2ebc09e1d69635f3e0970a8d4", "orlnUw&sL58qe4LA", 7),
(9, "user3@email.com", "User3", "400e67852c80cc38512bbe9f98a9e738a842ed8561f7372abb984a72354cb1f8", "F^janc*zH1QmgbmY", 7),
(10, "user4@email.com", "User4", "edef419d4f6bf64c467e7b41060b6f02f9e99d1efef866c7dc19aa004ca0bcca", "E4#4qey@IHITV6D8", 7),
(11, "user5@email.com", "User5", "824ee7fb9175f1698ea4451913cd9b004a096a7e8931d857c93ff2124398b9a4", "D`l0^H~@(37N(Z4G", 8),
(12, "user6@email.com", "User6", "5d13d2adec566fd2c2dcb91425fa9963a1a6af5c8a0e4126ad5806ae6fa9c648", "WqFeNqp)9X7TMawP", 8),
(13, "user7@email.com", "User7", "ea00263a55eb2514a21c989878b05cbafb646b3053ee966551507eb8f4ed9c11", "smx6CM1!jjFw`2!(", 9),
(14, "user8@email.com", "User8", "d50308f92859a76cff5db07ef2e6d268dcda63f7858693c857f3cf1de524e414", "DYZ(^VURpJ^Tr!BW", 10),
(15, "user9@email.com", "User9", "3198a016400715699857403490572f68aea478c8fe29757bcfe31d45037553e7", "5Pewky#A85TZsUw*", 10),
(16, "user10@email.com", "User10", "9ffb3fb1d11f837f47ce1cf544b528298537b5602d9718d744021346c39e92a9", "+fLFqUzV3s#J+4t4", 11),
(17, "user11@email.com", "User11", "2bb866a848914b226e5e7ccf5d94553d0116dd0f406bc218df2679a91ae94bf9", "OIhd^%xvkn7QYYC`", -1),
(18, "user12@email.com", "User12", "c2f8ac30c10a5bd18c86d1230c1cf22239e548483da7625f96dda49dc436e7c6", "J9f*DwX5VRi*Uvki", -1);

delete from $lobbiesTable;
insert into $lobbiesTable(gameid, userid)
values
(7, 7),
(7, 8),
(7, 9),
(7, 10),
(8, 11),
(8, 12),
(9, 13);
SQL;

        $stmt = self::$site->pdo()->prepare($sql);
        $stmt->execute([self::$game1State, self::$game2State, self::$game3State, self::$game4State, self::$game5State]);
    }

    public function test_construct() {
        $lobbies = new Sorry\Lobbies(self::$site);
        $this->assertInstanceOf('Sorry\Lobbies', $lobbies);
    }

    public function test_get() {
        $lobbies = new Sorry\Lobbies(self::$site);

        // Test a valid, fully ready lobby
        $lobby = $lobbies->get(7);
        $this->assertEquals(4, $lobby->getNumPlayers());
        $this->assertEquals(4, $lobby->getNumReady());
        $this->assertEquals(8, $lobby->getHostId());
        $this->assertEquals(7, $lobby->getGameId());
        $this->assertEquals('game1', $lobby->getName());
        $this->assertEquals('User1', $lobby->getPlayers()[7]);
        $this->assertEquals('User2', $lobby->getPlayers()[8]);
        $this->assertEquals('User3', $lobby->getPlayers()[9]);
        $this->assertEquals('User4', $lobby->getPlayers()[10]);
        $this->assertEquals(Sorry\Team::BLUE, $lobby->getColors()[7]);
        $this->assertEquals(Sorry\Team::GREEN, $lobby->getColors()[8]);
        $this->assertEquals(Sorry\Team::YELLOW, $lobby->getColors()[9]);
        $this->assertEquals(Sorry\Team::RED, $lobby->getColors()[10]);

        // Test a valid, not ready lobby
        $lobby = $lobbies->get(8);
        $this->assertEquals(2, $lobby->getNumPlayers());
        $this->assertEquals(1, $lobby->getNumReady());
        $this->assertEquals(12, $lobby->getHostId());
        $this->assertEquals(8, $lobby->getGameId());
        $this->assertEquals('game2', $lobby->getName());
        $this->assertEquals('User5', $lobby->getPlayers()[11]);
        $this->assertEquals('User6', $lobby->getPlayers()[12]);
        $this->assertEquals(Sorry\Team::GREEN, $lobby->getColors()[12]);

        // Test a valid lobby with no one ready
        $lobby = $lobbies->get(9);
        $this->assertEquals(1, $lobby->getNumPlayers());
        $this->assertEquals(0, $lobby->getNumReady());
        $this->assertEquals(13, $lobby->getHostId());
        $this->assertEquals(9, $lobby->getGameId());
        $this->assertEquals('game3', $lobby->getName());
        $this->assertEquals('User7', $lobby->getPlayers()[13]);

        // Test an invalid lobby
        $this->assertNull($lobbies->get(10));
    }

    public function test_getLobbies() {
        $lobbies = new Sorry\Lobbies(self::$site);
        $lobbyList = $lobbies->getLobbies();
        $this->assertCount(3, $lobbyList);

        $this->assertEquals(4, $lobbyList[0]->getNumPlayers());
        $this->assertEquals(4, $lobbyList[0]->getNumReady());
        $this->assertEquals(8, $lobbyList[0]->getHostId());
        $this->assertEquals(7, $lobbyList[0]->getGameId());
        $this->assertEquals('game1', $lobbyList[0]->getName());
        $this->assertEquals('User1', $lobbyList[0]->getPlayers()[7]);
        $this->assertEquals('User2', $lobbyList[0]->getPlayers()[8]);
        $this->assertEquals('User3', $lobbyList[0]->getPlayers()[9]);
        $this->assertEquals('User4', $lobbyList[0]->getPlayers()[10]);
        $this->assertEquals(Sorry\Team::BLUE, $lobbyList[0]->getColors()[7]);
        $this->assertEquals(Sorry\Team::GREEN, $lobbyList[0]->getColors()[8]);
        $this->assertEquals(Sorry\Team::YELLOW, $lobbyList[0]->getColors()[9]);
        $this->assertEquals(Sorry\Team::RED, $lobbyList[0]->getColors()[10]);

        $this->assertEquals(2, $lobbyList[1]->getNumPlayers());
        $this->assertEquals(1, $lobbyList[1]->getNumReady());
        $this->assertEquals(12, $lobbyList[1]->getHostId());
        $this->assertEquals(8, $lobbyList[1]->getGameId());
        $this->assertEquals('game2', $lobbyList[1]->getName());
        $this->assertEquals('User5', $lobbyList[1]->getPlayers()[11]);
        $this->assertEquals('User6', $lobbyList[1]->getPlayers()[12]);
        $this->assertEquals(Sorry\Team::GREEN, $lobbyList[1]->getColors()[12]);

        $this->assertEquals(1, $lobbyList[2]->getNumPlayers());
        $this->assertEquals(0, $lobbyList[2]->getNumReady());
        $this->assertEquals(13, $lobbyList[2]->getHostId());
        $this->assertEquals(9, $lobbyList[2]->getGameId());
        $this->assertEquals('game3', $lobbyList[2]->getName());
        $this->assertEquals('User7', $lobbyList[2]->getPlayers()[13]);
    }

    public function test_update() {
        $lobbies = new Sorry\Lobbies(self::$site);

        $lobbies->get(7);
        $lobby = $lobbies->get(7);
        $this->assertEquals(4, $lobby->getNumPlayers());
        $this->assertEquals(4, $lobby->getNumReady());
        $this->assertEquals(8, $lobby->getHostId());
        $this->assertEquals(7, $lobby->getGameId());
        $this->assertEquals('game1', $lobby->getName());
        $this->assertEquals('User1', $lobby->getPlayers()[7]);
        $this->assertEquals('User2', $lobby->getPlayers()[8]);
        $this->assertEquals('User3', $lobby->getPlayers()[9]);
        $this->assertEquals('User4', $lobby->getPlayers()[10]);
        $this->assertEquals(Sorry\Team::BLUE, $lobby->getColors()[7]);
        $this->assertEquals(Sorry\Team::GREEN, $lobby->getColors()[8]);
        $this->assertEquals(Sorry\Team::YELLOW, $lobby->getColors()[9]);
        $this->assertEquals(Sorry\Team::RED, $lobby->getColors()[10]);

        $lobby->removeUser(7);
        $lobbies->update($lobby);

        $lobby = $lobbies->get(7);
        $this->assertEquals(3, $lobby->getNumPlayers());
        $this->assertEquals(3, $lobby->getNumReady());
        $this->assertEquals(8, $lobby->getHostId());
        $this->assertEquals(7, $lobby->getGameId());
        $this->assertEquals('game1', $lobby->getName());
        $this->assertEquals('User2', $lobby->getPlayers()[8]);
        $this->assertEquals('User3', $lobby->getPlayers()[9]);
        $this->assertEquals('User4', $lobby->getPlayers()[10]);
        $this->assertEquals(Sorry\Team::GREEN, $lobby->getColors()[8]);
        $this->assertEquals(Sorry\Team::YELLOW, $lobby->getColors()[9]);
        $this->assertEquals(Sorry\Team::RED, $lobby->getColors()[10]);

    }

    public function test_clearLobby() {
        $lobbies = new Sorry\Lobbies(self::$site);
        $tableName = $lobbies->getTableName();

        $sql = <<<SQL
SELECT * FROM $tableName
SQL;

        // Ensure number or records to start
        $stmt = $lobbies->pdo()->prepare($sql);
        $stmt->execute();
        $this->assertEquals(7, $stmt->rowCount());

        // Clear a valid lobby
        $lobbies->clearLobby(7);
        $stmt = $lobbies->pdo()->prepare($sql);
        $stmt->execute();
        $this->assertEquals(3, $stmt->rowCount());

        // Clear a lobby that doesn't exist
        $lobbies->clearLobby(5);
        $stmt = $lobbies->pdo()->prepare($sql);
        $stmt->execute();
        $this->assertEquals(3, $stmt->rowCount());
    }

    public function test_createLobby() {
        $lobbies = new Sorry\Lobbies(self::$site);
        $tableName = $lobbies->getTableName();

        $sql = <<<SQL
SELECT * FROM $tableName
WHERE gameId=?
SQL;

        // Test with a lobby that has no players
        $lobby = new Sorry\Lobby(0, '');
        $this->assertFalse($lobbies->createLobby($lobby));

        // Test with a game that doesn't exist
        $lobby = new Sorry\Lobby(99, '');
        $lobby->addUser(17, 'User11');
        $this->assertFalse($lobbies->createLobby($lobby));

        // Test with an existing game that already has a lobby
        $lobby = new Sorry\Lobby(7, '');
        $lobby->addUser(17, 'User11');
        $this->assertFalse($lobbies->createLobby($lobby));

        // Test with an existing game that doesn't have a lobby but isn't in the lobby state
        $lobby = new Sorry\Lobby(10, '');
        $lobby->addUser(17, 'User11');
        $this->assertFalse($lobbies->createLobby($lobby));

        // Test with an existing game in the lobby state that doesn't have a lobby
        $lobby = new Sorry\Lobby(11, '');
        $lobby->addUser(17, 'User11');
        $lobby->addUser(18, 'User12');
        $this->assertTrue($lobbies->createLobby($lobby));
        $stmt = $lobbies->pdo()->prepare($sql);
        $stmt->execute([$lobby->getGameId()]);
        $this->assertEquals(2, $stmt->rowCount());
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function test_exists() {
        $games = new Sorry\Games(self::$site);

        // Test an ID that exists
        $this->assertTrue($games->exists(8));

        // Test and ID that does not exist
        $this->assertFalse($games->exists(2));
    }
}