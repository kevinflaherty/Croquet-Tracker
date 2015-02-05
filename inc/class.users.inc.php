

<?php

/**
 * Handles user interactions within the app
 *
 * PHP version 5
 *
 * @author Nick Cacace
 * @copyright 2015 Nick Cacace
 * @license
 *
 */
class CTrackerUsers
{
    // Class properties and other methods omitted to save space

    /**
     * Checks and inserts a new account email into the database
     *
     * @return string    a message indicating the action status
     */
    public function createAccount()
    {
        $u = trim($_POST['username']);
        $v = sha1(time());

        $sql = "SELECT COUNT(Username) AS theCount
                FROM users
                WHERE Username=:email";
        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":email", $u, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch();
            if($row['theCount']!=0) {
                return "<h2> Error </h2>"
                    . "<p> Sorry, that email is already in use. "
                    . "Please try again. </p>";
            }
            if(!$this->sendVerificationEmail($u, $v)) {
                return "<h2> Error </h2>"
                    . "<p> There was an error sending your"
                    . " verification email. Please "
                    . "<a href="mailto:help@coloredlists.com">contact "
                    . "us</a> for support. We apologize for the "
                    . "inconvenience. </p>";
            }
            $stmt->closeCursor();
        }

        $sql = "INSERT INTO users(Username, ver_code)
                VALUES(:email, :ver)";
        if($stmt = $this->_db->prepare($sql)) {
            $stmt->bindParam(":email", $u, PDO::PARAM_STR);
            $stmt->bindParam(":ver", $v, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();

            $userID = $this->_db->lastInsertId();
            $url = dechex($userID);

            /*
             * If the UserID was successfully
             * retrieved, create a default list.
             */
            $sql = "INSERT INTO lists (UserID, ListURL)
                    VALUES ($userID, $url)";
            if(!$this->_db->query($sql)) {
                return "<h2> Error </h2>"
                    . "<p> Your account was created, but "
                    . "creating your first list failed. </p>";
            } else {
                return "<h2> Success! </h2>"
                    . "<p> Your account was successfully "
                    . "created with the username <strong>$u</strong>."
                    . " Check your email!";
            }
        } else {
            return "<h2> Error </h2><p> Couldn't insert the "
                . "user information into the database. </p>";
        }
    }
}

?>