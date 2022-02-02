<?php

namespace Sorry;

class RegisterController {
    private $redirect;	// Page we will redirect the user to.
    private $registerMessage;   // Message will not be null if error occurred

    /**
     * RegisterController constructor.
     * @param Site $site Site object
     * @param User $user Current user
     * @param array $post $_POST
     */
    public function __construct(Site $site, array &$session, array $post) {
        $root = $site->getRoot();
        $this->redirect = "$root/";

        if(isset($post['register'])){
            $email = strip_tags($post['email']);
            $name = strip_tags($post['name']);
            if(strlen($email) <= 0 or strlen($name) <= 0) {
                $this->redirect = "$root/register.php?e";
                $session["error"] = "Must enter username and email";
            }
            else if(strlen($email) > 200 or strlen($name) > 100) {
                $this->redirect = "$root/register.php?e";
                $session["error"] = "Username or email are too long. Username max length: 100 characters; Email max length: 200 characters";
            } else {
                $this->registerMessage = Database::addUser($site, $email, $name);
                if($this->registerMessage !== null) {
                    $this->redirect = "$root/register.php?e";
                    $session["error"] = $this->registerMessage;
                }
            }
        }
    }

    /**
     * Get any redirect link
     * @return mixed Redirect link
     */
    public function getRedirect() {
        return $this->redirect;
    }

    /**
     * Get the register message (null if there was no error)
     * @return null|string the message
     */
    public function getRegisterMessage() {
        return $this->registerMessage;
    }
}