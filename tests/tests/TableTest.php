<?php


class TableTest extends \PHPUnit\Framework\TestCase {
    public function test_construct() {
        $site = new Sorry\Site();
        $site->setEmail("Test Email");
        $table = new Sorry\Table($site, "Test Name");
        $this->assertInstanceOf('Sorry\Table', $table);
        $this->assertEquals("Test Name", $table->getTableName());
        $this->assertEquals("Test Email", $table->getSite()->getEmail());
    }

    public function test_pdo() {
        $site = new Sorry\Site();
        $localize = require 'localize.inc.php';
        if (is_callable($localize)) {
            $localize($site);
        }
        $table = new Sorry\Table($site, "");
        $pdo = $table->pdo();
        $this->assertInstanceOf('PDO', $pdo);
    }
}