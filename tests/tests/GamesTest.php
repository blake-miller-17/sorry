<?php


class GamesTest extends \PHPUnit\Framework\TestCase {
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

    public function test_get() {
        $games = new Sorry\Games(self::$site);

        // Test a valid id
        $game = $games->get(7);
        $this->assertInstanceOf('Sorry\Game', $game);
        $this->assertEquals(7, $game->getId());
        $this->assertEquals("game1", $game->getName());
        $this->assertEquals(0, $game->getstatus());
        $this->assertEquals(self::$game1State, $game->getState());
        $this->assertEquals(8, $game->getHost());
        $this->assertEquals(9, $game->getColor(Sorry\Team::YELLOW));
        $this->assertEquals(8, $game->getColor(Sorry\Team::GREEN));
        $this->assertEquals(10, $game->getColor(Sorry\Team::RED));
        $this->assertEquals(7, $game->getColor(Sorry\Team::BLUE));
        $this->assertEquals(4, $game->getAcknowledge());

        // Test valid id
        $game = $games->get(8);
        $this->assertInstanceOf('Sorry\Game', $game);

        // Test an ID that does not exist
        $game = $games->get(99);
        $this->assertNull($game);
    }

    public function test_update() {
        $games = new Sorry\Games(self::$site);

        // ----- Test updating a valid id -----

        $newState = serialize(new Sorry\Sorry([Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::BLUE]));

        // Get a game and change it's fields
        $game = $games->get(7);
        $game->setStatus(2);
        $game->setState($newState);
        $game->setHost(99);
        $game->setColor(Sorry\Team::YELLOW, 98);
        $game->setColor(Sorry\Team::GREEN, 97);
        $game->setColor(Sorry\Team::RED, 96);
        $game->setColor(Sorry\Team::BLUE, 95);
        $game->setAcknowledge(4);

        // Update the game
        $this->assertTrue($games->update($game));

        // Get the game again and make sure the fields were updated
        $game = $games->get(7);
        $this->assertInstanceOf('Sorry\Game', $game);
        $this->assertEquals(7, $game->getId());
        $this->assertEquals(2, $game->getStatus());
        $this->assertEquals($newState, $game->getState());
        $this->assertEquals(99, $game->getHost());
        $this->assertEquals(98, $game->getColor(Sorry\Team::YELLOW));
        $this->assertEquals(97, $game->getColor(Sorry\Team::GREEN));
        $this->assertEquals(96, $game->getColor(Sorry\Team::RED));
        $this->assertEquals(95, $game->getColor(Sorry\Team::BLUE));
        $this->assertEquals(4, $game->getAcknowledge());

        // ----- Test on game that doesn't exist -----

        // Create user with an id that doesn't exist
        $game = new Sorry\Game([
            'id' => 99,
            'name' => '',
            'status' => '',
            'state' => '',
            'host' => '',
            'yellow' => '',
            'green' => '',
            'red' => '',
            'blue' => '',
            'acknowledge' => ''
        ]);

        // Update the game
        $this->assertFalse($games->update($game));
    }

    public function test_create() {
        $games = new Sorry\Games(self::$site);

        $newState = new Sorry\Sorry([Sorry\Team::GREEN, Sorry\Team::RED, Sorry\Team::YELLOW]);

        $game = new Sorry\Game([
            'id' => 100,
            'name' => "Unique Name",
            'status' => 1,
            'state' => serialize($newState),
            'host' => 109,
            'yellow' => 108,
            'green' => 107,
            'red' => 106,
            'blue' => 105,
            'acknowledge' => 3
        ]);

        // Create the game
        $this->assertTrue($games->create($game) > 0);

        // Get the game again and make sure the fields were updated
        $table = $games->getTableName();
        $sql = <<<SQL
select * from $table where name='Unique Name'
SQL;

        $stmt = $games->pdo()->prepare($sql);
        $stmt->execute();
        $this->assertEquals(1, $stmt->rowCount());
        $game = new Sorry\Game($stmt->fetch(\PDO::FETCH_ASSOC));

        $this->assertInstanceOf('Sorry\Game', $game);
        $this->assertEquals(1, $game->getStatus());
        $this->assertEquals(serialize($newState), $game->getState());
        $this->assertEquals(109, $game->getHost());
        $this->assertEquals(108, $game->getColor(Sorry\Team::YELLOW));
        $this->assertEquals(107, $game->getColor(Sorry\Team::GREEN));
        $this->assertEquals(106, $game->getColor(Sorry\Team::RED));
        $this->assertEquals(105, $game->getColor(Sorry\Team::BLUE));
        $this->assertEquals(3, $game->getAcknowledge());
    }

    public function test_remove() {
        $games = new Sorry\Games(self::$site);
        $lobbies = new Sorry\Lobbies(self::$site);
        $users = new sorry\Users(self::$site);
        $usersTable = $users->getTableName();

        $sql = <<<SQL
SELECT * FROM $usersTable
WHERE gameId=?
SQL;

        $stmt = $games->pdo()->prepare($sql);
        $stmt->execute([7]);
        $this->assertEquals(4, $stmt->rowCount());
        $this->assertTrue($games->exists(7));
        $this->assertTrue($lobbies->exists(7));

        $this->assertTrue($games->remove(7));

        $this->assertFalse($games->exists(7));
        $this->assertFalse($lobbies->exists(7));
        $stmt = $games->pdo()->prepare($sql);
        $stmt->execute([7]);
        $this->assertEquals(0, $stmt->rowCount());
    }

    public function test_exists() {
        $games = new Sorry\Games(self::$site);

        // Test an ID that exists
        $this->assertTrue($games->exists(8));

        // Test and ID that does not exist
        $this->assertFalse($games->exists(2));
    }
}