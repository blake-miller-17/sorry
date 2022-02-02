<?php


class WebSocketsTest extends \PHPUnit\Framework\TestCase {
    private static $site;

    public static function setUpBeforeClass() {
        self::$site = new Sorry\Site();
        $localize  = require 'localize.inc.php';
        if(is_callable($localize)) {
            $localize(self::$site);
        }
    }

    public function test_listenerScript() {
        $correctScript = <<<SCRIPT
<script>
    function pushInit(key) {
        var conn = new WebSocket('wss://webdev.cse.msu.edu/ws');
        conn.onopen = function (e) {
            console.log("Connection to push established!");
            conn.send(key);
        };

        conn.onmessage = function (e) {
            try {
                var msg = JSON.parse(e.data);
                if (msg.cmd === "reload") {
                    location.reload();
                }
            } catch (e) {
            }
        };
    }

    pushInit("test key");
</script>
SCRIPT;
        $this->assertEquals($correctScript, Sorry\WebSockets::listenerScript("test key"));

        $correctScript = <<<SCRIPT
<script>
    function pushInit(key) {
        var conn = new WebSocket('wss://webdev.cse.msu.edu/ws');
        conn.onopen = function (e) {
            console.log("Connection to push established!");
            conn.send(key);
        };

        conn.onmessage = function (e) {
            try {
                var msg = JSON.parse(e.data);
                if (msg.cmd === "reload") {
                    location.reload();
                }
            } catch (e) {
            }
        };
    }

    pushInit("alternative test key");
</script>
SCRIPT;
        $this->assertEquals($correctScript, Sorry\WebSockets::listenerScript("alternative test key"));
    }

    public function test_generateKey() {
        $this->assertContains('cse477_SS21_team17', Sorry\WebSockets::generateKey(self::$site));
        $this->assertContains('cse477_SS21_team17_' . self::$site->getUser() . '_element1', Sorry\WebSockets::generateKey(self::$site, ['element1']));
        $this->assertContains('cse477_SS21_team17_' . self::$site->getUser() . '_element1_element2', Sorry\WebSockets::generateKey(self::$site, ['element1', 'element2']));
    }
}