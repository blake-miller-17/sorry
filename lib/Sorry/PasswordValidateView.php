<?php


namespace Sorry;

/**
 * Class PasswordValidateView.
 * @package Sorry
 */
class PasswordValidateView extends View {
    // Codes for each error type
    const INVALID_VALIDATOR = 0;
    const INVALID_EMAIL_TO_USER = 1;
    const INVALID_EMAIL_TO_VALIDATOR = 2;
    const PASSWORDS_DO_NOT_MATCH = 3;
    const PASSWORD_TOO_SHORT = 4;
    const PASSWORD_FAILED = 5;

    // Map from error codes to the actual message.
    const ERROR_CODES = [
        self::INVALID_VALIDATOR => "Invalid or unavailable validator",
        self::INVALID_EMAIL_TO_USER => "Email address is not for a valid user",
        self::INVALID_EMAIL_TO_VALIDATOR => "Email address does not match validator",
        self::PASSWORDS_DO_NOT_MATCH => "Passwords did not match",
        self::PASSWORD_TOO_SHORT => "Password too short",
        self::PASSWORD_FAILED => "Password failed to set"
    ];

    private $get;       // Get super global
    private $validator; // Validator string
    private $error;     // Error message

    /**
     * Constructor
     * Sets the page title and any other settings.
     * @param Site $site The site object
     * @param array $get The get super global
     */
    public function __construct(Site $site, array $get) {
        parent::__construct($site);
        $this->setTitle("Password Entry");
        $this->addLink("instructions.php", "Instructions");
        $this->get = $get;
        if(isset($get['v'])) {
            $this->validator = strip_tags($get['v']);
        }
        if(isset($get['e'])){
            $this->error = strip_tags($get['e']);
        }
    }

    /**
     * Present the content of the password validator page.
     * @return string The content of the password validator page
     */
    public function presentContent() {
        $html = <<<HTML
    <form action="post/password-validate.php" method="post">
    <input type="hidden" name="validator" value="$this->validator">
        <div class="options">
            <p class="gap-sm">
                <label class="labelFancy" for="email">Email</label><br>
                <input class="inputFancy" type="email" id="email" name="email" placeholder="Email">
            </p>
            <p class="gap-xs">
                <label class="labelFancy" for="password">Password:</label><br>
                <input class="inputFancy" type="password" id="password" name="password" placeholder="Password">
            </p>
            <p class="gap-xs">
                <label class="labelFancy" for="password2">Confirm Password</label><br>
                <input class="inputFancy" type="password" id="password2" name="password2" placeholder="Password">
            </p>
            <p class="gap-xs">
                <input class="primary primary-sm" type="submit" value="Submit" name="ok"> <input class="primary primary-sm" type="submit" value="Cancel" name="cancel">
            </p>
HTML;
        $errorMsg = $this->presentError();
        $html .= <<<HTML
        </div>
        <p class="gap-xs gap-bot-sm warningMsg">$errorMsg</p>
    </form>
HTML;
        return $html;
    }

    /**
     * Generate the error message for this page.
     * @return string The error message for this page
     */
    public function presentError() {
        return isset($this->error) ? self::ERROR_CODES[$this->error] : "";
    }
}