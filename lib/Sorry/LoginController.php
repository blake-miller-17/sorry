<?php

namespace Sorry;

class LoginController {
    private $redirect;	// Page we will redirect the user to.

    /**
     * LoginController constructor.
     * @param Site $site The Site object
     * @param array $session $_SESSION
     * @param array $post $_POST
     */
    public function __construct(Site $site, array &$session, array $post) {
        $email = strip_tags($post['email']);
        $password = strip_tags($post['password']);
        $user = Database::login($site, $email, $password);
        $session[SessionNames::USER] = $user;

        $root = $site->getRoot();
        if($user === null) {
            // Login failed
            $this->redirect = "$root/login.php?e";
            $session["error"] = "Invalid Login Credentials";
        } else {
            $this->redirect = "$root/lobbies.php";
        }
    }

    /**
     * Get any redirect link
     * @return mixed Redirect link
     */
    public function getRedirect() {
        return $this->redirect;
    }
}