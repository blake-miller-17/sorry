<?php
namespace Sorry;

/**
 * Class GameOverView handling generation of HTML for gameover.php
 * @package Sorry
 */
class GameOverView extends View {
    public function __construct(Site $site) {
        parent::__construct($site);
        $this->addLink("instructions.php", "Instructions");
        $this->addLink("post/logout.php", "Log out");
        $winner = $_SESSION[SessionNames::WINNER];
        $user = $winner["user"];
        $this->setTitle($user . ' Won!');
    }

    /**
     * Generate HTML for the image and text for the winner of the game.
     * @return string HTML for the image and text for the winner of the game
     */
    public function presentImage() {
        $winner = $_SESSION[SessionNames::WINNER];
        $image = "gameover_tie.png";
        $text = "It is a tie!";
        $color = $winner["color"];
        switch($color){
            case Team::YELLOW:
                $image = "gameover_yellow.png";
                $text = "Yellow Won!";
                break;

            case Team::GREEN:
                $image = "gameover_green.png";
                $text = "Green Won!";
                break;

            case Team::RED:
                $image = "gameover_red.png";
                $text = "Red Won!";
                break;

            case Team::BLUE:
                $image = "gameover_blue.png";
                $text = "Blue Won!";
        }

        return <<<HTML
        <div class="gameover"><p><img src="images/$image" height="768" width="1024" alt="$text"></p></div>
HTML;
    }

    public function presentBody() {
        $root = $this->site->getRoot();
        $html = <<<HTML
<form method='post' action='$root/'>
      <div class='buttonBox'>
          <input type='submit' class='primary' value='New Game'>
      </div>
</form>
HTML;
        return $html;
    }
}