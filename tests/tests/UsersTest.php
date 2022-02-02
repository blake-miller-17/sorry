<?php


class EmailMock extends Sorry\Email {
    public function mail($to, $subject, $message, $headers)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
        $this->headers = $headers;
    }

    public $to;
    public $subject;
    public $message;
    public $headers;
}

class UsersTest extends \PHPUnit\Framework\TestCase {
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

    public function test_pdo() {
        $users = new Sorry\Users(self::$site);
        $this->assertInstanceOf('\PDO', $users->pdo());
    }

    public function test_login() {
        $users = new Sorry\Users(self::$site);

        // Test a valid login based on email address
        $user = $users->login("user1@email.com", "User1Password");
        $this->assertInstanceOf('Sorry\User', $user);
        $this->assertEquals(7, $user->getId());
        $this->assertEquals("user1@email.com", $user->getEmail());
        $this->assertEquals("User1", $user->getName());
        $this->assertEquals(7, $user->getGameId());

        // Test a valid login based on email address
        $user = $users->login("user2@email.com", "User2Password");
        $this->assertInstanceOf('Sorry\User', $user);

        // Test a failed login
        $user = $users->login("user1@email.com", "wrongpw");
        $this->assertNull($user);
    }

    public function test_get() {
        $users = new Sorry\Users(self::$site);

        // Test a valid id
        $user = $users->get(7);
        $this->assertInstanceOf('Sorry\User', $user);
        $this->assertEquals(7, $user->getId());
        $this->assertEquals("user1@email.com", $user->getEmail());
        $this->assertEquals("User1", $user->getName());
        $this->assertEquals(7, $user->getGameId());

        // Test valid id
        $user = $users->get(8);
        $this->assertInstanceOf('Sorry\User', $user);

        // Test an ID that does not exist
        $user = $users->get(99);
        $this->assertNull($user);
    }

    public function test_update() {
        $users = new Sorry\Users(self::$site);

        // ----- Test updating a valid id -----

        // Get a user and change it's fields
        $user = $users->get(7);
        $user->setEmail('test@email.com');
        $user->setName('Name, Test');
        $user->setGameId(55);

        // Update the user
        $this->assertTrue($users->update($user));

        // Get the user again and make sure the fields were updated
        $user = $users->get(7);
        $this->assertInstanceOf('Sorry\User', $user);
        $this->assertEquals(7, $user->getId());
        $this->assertEquals("test@email.com", $user->getEmail());
        $this->assertEquals("Name, Test", $user->getName());
        $this->assertEquals(55, $user->getGameId());

        // ----- Test on user that doesn't exist -----

        // Create user with an id that doesn't exist
        $user = new Sorry\User([
            'id' => 99,
            'email' => '',
            'name' => '',
            'password' => '',
            'gameId' => ''
        ]);

        // Update the user
        $this->assertFalse($users->update($user));

        // ----- Test update with violating integrity constraint -----

        // Get a user and set email to an email already in use by another user
        $user = $users->get(7);
        $user->setEmail('user2@email.com');

        // Update the user
        $this->assertFalse($users->update($user));
    }

    public function test_emailExists() {
        $users = new Sorry\Users(self::$site);

        $this->assertTrue($users->emailExists("user1@email.com"));
        $this->assertFalse($users->emailExists("user1"));
        $this->assertFalse($users->emailExists("user2"));
        $this->assertTrue($users->emailExists("user2@email.com"));
        $this->assertFalse($users->emailExists("nobody"));
        $this->assertFalse($users->emailExists("7"));
    }

    public function test_nameExists() {
        $users = new Sorry\Users(self::$site);

        // Test a name that a user has
        $this->assertTrue($users->nameExists("User1"));

        // Test a name that no user has
        $this->assertFalse($users->nameExists("Unclaimed Name"));
    }

    public function test_add() {
        $users = new Sorry\Users(self::$site);

        $mailer = new EmailMock();

        $user7 = $users->get(7);
        $this->assertContains("Email address already exists",
            $users->add($user7, $mailer));

        $row = [
            'id' => 99,
            'email' => 'new@email.com',
            'name' => 'New',
            'password' => 'NewPassword',
            'gameId' => '-1'
        ];

        $user = new Sorry\User($row);
        $users->add($user, $mailer);

        $table = $users->getTableName();
        $sql = <<<SQL
select * from $table where email='new@email.com'
SQL;

        $stmt = $users->pdo()->prepare($sql);
        $stmt->execute();
        $this->assertEquals(1, $stmt->rowCount());

        $this->assertEquals("new@email.com", $mailer->to);
        $this->assertEquals("Confirm your email", $mailer->subject);
    }

    public function test_setPassword() {
        $users = new Sorry\Users(self::$site);

        // Test a valid login based on user ID
        $user = $users->login("user1@email.com", "User1Password");
        $this->assertNotNull($user);
        $this->assertEquals("User1", $user->getName());

        // Change the password
        $users->setPassword(7, "User1PasswordNew");

        // Old password should not work
        $user = $users->login("user1@email.com", "User1Password");
        $this->assertNull($user);

        // New password does work!
        $user = $users->login("user1@email.com", "User1PasswordNew");
        $this->assertNotNull($user);
        $this->assertEquals("User1", $user->getName());
    }

    public function test_clearGameAssociations() {
        $users = new Sorry\Users(self::$site);

        // Clear and ID that no players have associations with
        $this->assertTrue($users->clearGameAssociations(5));
        $this->assertEquals(7, $users->get(7)->getGameId());
        $this->assertEquals(7, $users->get(8)->getGameId());
        $this->assertEquals(8, $users->get(11)->getGameId());
        $this->assertEquals(9, $users->get(13)->getGameId());

        // Clear an ID that a single player has an association with
        $this->assertTrue($users->clearGameAssociations(9));
        $this->assertEquals(7, $users->get(7)->getGameId());
        $this->assertEquals(7, $users->get(8)->getGameId());
        $this->assertEquals(8, $users->get(11)->getGameId());
        $this->assertEquals(-1, $users->get(13)->getGameId());

        // Clear an ID that multiple users have an association with
        $this->assertTrue($users->clearGameAssociations(7));
        $this->assertEquals(-1, $users->get(7)->getGameId());
        $this->assertEquals(-1, $users->get(8)->getGameId());
        $this->assertEquals(8, $users->get(11)->getGameId());
        $this->assertEquals(-1, $users->get(13)->getGameId());

        // Clear using the ID designating no ID (-1)
        $this->assertTrue($users->clearGameAssociations(-1));
        $this->assertEquals(-1, $users->get(7)->getGameId());
        $this->assertEquals(-1, $users->get(8)->getGameId());
        $this->assertEquals(8, $users->get(11)->getGameId());
        $this->assertEquals(-1, $users->get(13)->getGameId());
    }

    public function test_setGameId() {
        $users = new Sorry\Users(self::$site);

        // Set for a user that doesn't exist
        $this->assertTrue($users->setGameId(99, 7));

        // Set for a game that doesn't exist
        $this->assertFalse($users->setGameId(7, 99));
        $this->assertEquals(7, $users->get(7)->getGameId());

        // Set for valid values, but to a game the user is already set to
        $this->assertTrue($users->setGameId(7, 7));
        $this->assertEquals(7, $users->get(7)->getGameId());

        // Set for valid values to a new game
        $this->assertTrue($users->setGameId(7, 8));
        $this->assertEquals(8, $users->get(7)->getGameId());
    }
}