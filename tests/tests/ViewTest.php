<?php

class ViewTest extends \PHPUnit\Framework\TestCase {
    private static $site;

    public static function setUpBeforeClass() {
        self::$site = new Sorry\Site();
        $localize  = require 'localize.inc.php';
        if(is_callable($localize)) {
            $localize(self::$site);
        }
    }

    public function test_head() {
        $view = new Sorry\View(self::$site);
        $view->setTitle("Test Title");
        $this->assertContains('<meta charset="utf-8">', $view->head());
        $this->assertContains("<title>Sorry! Test Title</title>", $view->head());
        $this->assertContains('<link rel="stylesheet" href="lib/sorry.css">', $view->head());
        $this->assertContains("<link rel='icon' href='images/favicon.ico' type='image/x-icon'/>", $view->head());
    }

    public function test_header() {
        $root = self::$site->getRoot();
        $view = new Sorry\View(self::$site);
        $view->setTitle("Test Title");

        $result = $view->header('testClass');
        $this->assertContains('<header class="sorryHeader">', $result);
        $this->assertContains("<figure><a href=\"$root/\"> <img src=\"images/sorrylogo.png\" width=\"425\" height=\"125\" alt=\"sorry logo\"></a></figure>", $result);
        $this->assertContains('<h1 class="testClass">Test Title</h1>', $result);

        $this->assertNotContains("function pushInit(key) {
            var conn = new WebSocket('ws://webdev.cse.msu.edu/ws');
            conn.onopen = function (e) {
                console.log(\"Connection to push established!\");
                conn.send(key);
            };", $result);
    }

    public function test_footer() {
        $view = new Sorry\View(self::$site);

        $result = $view->footer();
        $this->assertContains('<footer class="sorryFooter">', $result);
        $this->assertContains('<p>CSE 477 SS21 Project 2</p>', $result);
        $this->assertContains('<p>Team 17</p>', $result);
        $this->assertContains('</footer>', $result);
    }
}