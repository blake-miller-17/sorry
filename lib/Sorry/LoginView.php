<?php


namespace Sorry;

/**
 * Class LoginView.
 * @package Sorry
 */
class LoginView extends View {

    private $session; // Session super global
    private $get;     // Get super global

    /**
     * LoginView constructor.
     * @param Site $site Site object
     * @param array $session Session super global
     * @param array $get Get super global
     */
    public function __construct(Site $site, array $session, array $get) {
        parent::__construct($site);
        $this->session = $session;
        $this->get = $get;
        $this->setTitle('Login');
        $this->addLink("instructions.php", "Instructions");
    }

    /**
     * Generate the error message.
     * @return string The error message
     */
    public function displayError() {
        $html = "";
        // Error set?
        if(isset($this->get['e'])) {
            $error = $this->session["error"];
            $html .= "<p class='msg''>$error</p>";
        }
        return $html;
    }

    /**
     * Present the content of the login page.
     * @return string The content of the login page
     */
    public function presentContent() {
        $html = <<<HTML
<form method="post" action="post/login.php">
    <div class="options">
        <p class="gap-sm">
            <label class="labelFancy" for="email">Email</label><br>
            <input class="inputFancy" type="email" id="email" name="email" placeholder="Email">
        </p>
        <p class="gap-xs">
            <label class="labelFancy" for="password">Password</label><br>
            <input class="inputFancy" type="password" id="password" name="password" placeholder="Password">
        </p>
        <p class="gap-xs">
            <input class="primary primary-sm extend" type="submit" name="login" value="Login">
        </p>
    </div>
    <p class="gap-xs gap-bot-sm">New user? Register <a href="register.php">here</a>.</p>
</form>

HTML;

        return $html;
    }
}