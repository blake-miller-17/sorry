<?php


class ValidatorsTest extends \PHPUnit\Framework\TestCase{
    private static $site;

    public static function setUpBeforeClass() {
        self::$site = new Sorry\Site();
        $localize  = require 'localize.inc.php';
        if(is_callable($localize)) {
            $localize(self::$site);
        }
    }

    protected function setUp() {
        $validators = new Sorry\Validators(self::$site);
        $tableName = $validators->getTableName();

        $sql = "delete from $tableName";

        self::$site->pdo()->query($sql);
    }

    public function test_construct() {
        $validators = new Sorry\Validators(ValidatorsTest::$site);
        $this->assertInstanceOf('Sorry\Validators', $validators);
    }

    public function test_newValidator() {
        $validators = new Sorry\Validators(self::$site);

        $validator = $validators->newValidator(27);
        $this->assertEquals(32, strlen($validator));

        $table = $validators->getTableName();
        $sql = <<<SQL
select * from $table
where userid=? and validator=?
SQL;

        $stmt = $validators->pdo()->prepare($sql);
        $stmt->execute([27, $validator]);
        $this->assertEquals(1, $stmt->rowCount());
    }

    public function test_get() {
        $validators = new Sorry\Validators(self::$site);

        // Test a not-found condition
        $this->assertNull($validators->get(""));

        // Create a validator
        $validator = $validators->newValidator(27);

        $this->assertEquals(27, $validators->get($validator));

        // Remove the validator for this user
        $validators->remove(27);
        $this->assertNull($validators->get($validator));

        // Create two validators
        // Either can work.
        $validator1 = $validators->newValidator(33);
        $validator2 = $validators->newValidator(33);

        $this->assertEquals(33, $validators->get($validator1));
        $this->assertEquals(33, $validators->get($validator2));

        // Remove the validator for this user
        $validators->remove(33);

        $this->assertNull($validators->get($validator1));
        $this->assertNull($validators->get($validator2));
    }
}