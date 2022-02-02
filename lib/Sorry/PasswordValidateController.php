<?php

namespace Sorry;

class PasswordValidateController {
    private $redirect;

    /**
     * PasswordValidateController constructor.
     * @param Site $site The Site object
     * @param array $post $_POST
     */
    public function __construct(Site $site, array $post) {
        $root = $site->getRoot();
        $this->redirect = "$root/";

        if (isset($post['ok'])) {
            //
            // 1. Ensure the validator is correct! Use it to get the user ID.
            //
            $validators = new Validators($site);
            $validator = strip_tags($post['validator']);
            $userid = $validators->get($validator);
            if($userid === null) {
                $this->redirect = "$root/password-validate.php?v=$validator&e=" . PasswordValidateView::INVALID_VALIDATOR;
                return;
            }

            //
            // 2. Ensure the email matches the user.
            //
            $user = Database::getUser($site, $userid);
            if($user === null) {
                // User does not exist!
                $this->redirect = "$root/password-validate.php?v=$validator&e=" . PasswordValidateView::INVALID_EMAIL_TO_USER;
                return;
            }
            $email = trim(strip_tags($post['email']));
            if($email !== $user->getEmail()) {
                // Email entered is invalid
                $this->redirect = "$root/password-validate.php?v=$validator&e=" . PasswordValidateView::INVALID_EMAIL_TO_VALIDATOR;
                return;
            }

            //
            // 3. Ensure the passwords match each other
            //
            $password1 = trim(strip_tags($post['password']));
            $password2 = trim(strip_tags($post['password2']));
            if($password1 !== $password2) {
                // Passwords do not match
                $this->redirect = "$root/password-validate.php?v=$validator&e=" . PasswordValidateView::PASSWORDS_DO_NOT_MATCH;
                return;
            }

            if(strlen($password1) < 8) {
                // Password too short
                $this->redirect = "$root/password-validate.php?v=$validator&e=" . PasswordValidateView::PASSWORD_TOO_SHORT;
                return;
            }

            //
            // 4. Create a salted password and save it for the user.
            //
            if(!Database::setPassword($site, $user, $password1)) {
                $this->redirect = "$root/password-validate.php?v=$validator&e=" . PasswordValidateView::PASSWORD_FAILED;
                return;
            };

            //
            // 5. Destroy the validator record so it can't be used again!
            //
            $validators->remove($userid);
        }
    }

    /**
     * Getter for the page to redirect the user to.
     * @return string The page to redirect the user to
     */
    public function getRedirect() {
        return $this->redirect;
    }
}