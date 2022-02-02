<?php

namespace Sorry;

/**
 * Class RegisterView
 * @package Sorry
 */
class RegisterView extends View {

    private $session; // Session super global
    private $get;     // Get super global

    /**
     * Constructor
     * @param Site $site Site object
     * @param array $session The session super global
     * @param array $get The get super global
     */
    public function __construct(Site $site, array $session, array $get) {
        parent::__construct($site);
        $this->setTitle('Register');
        $this->addLink("instructions.php", "Instructions");
        $this->session = $session;
        $this->get = $get;
    }

    /**
     * Generate the error message for this page.
     * @return string The error message for this page
     */
    public function displayError() {
        $html = "";
        // Error set
        if(isset($this->get['e'])) {
            $error = $this->session["error"];
            $html .= <<<HTML
<p class="msg none">$error</p>
HTML;
        }
        return $html;
    }

    /**
     * Present the content for the register page.
     * @return string The content for the register page
     */
    public function presentContent() {
        $html = <<<HTML
<form method="post" action="post/register.php">
    <div class="options">
        <p class="gap-sm">
            <label class="labelFancy" for="username">Username</label><br>
            <input class="inputFancy" type="text" id="username" name="name" placeholder="Username">
        </p>
        <p class="gap-xs">
            <label class="labelFancy" for="email">Email</label><br>
            <input class="inputFancy" type="email" id="email" name="email" placeholder="Email">
        </p>
        <p class="gap-xs">
            <input class="primary primary-sm extend" type="submit" name="register" value="Register">
        </p>
    </div>
    <p class="gap-xs gap-bot-sm">Already registered? Login <a href="login.php">here</a>.</p>
</form>
HTML;
        return $html;
    }
}