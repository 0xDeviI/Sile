<?php

namespace Signite\Models;

require_once "signite-framework/modules/Validity.php";
require_once "signite-framework/modules/Security.php";
require_once "signite-framework/modules/Session.php";
require_once "signite-framework/modules/Identifier.php";

use Signite\Modules\Validity;
use Signite\Modules\Security;
use Signite\Modules\Identifier;
use function Signite\Modules\initializeSession;

class User {
    private $id;
    private $username;
    private $password;

    
    public function __construct($id, $username, $password, $db) {
        $this->db = $db;
        $this->id = $id === "" ? Identifier::uuid4() : Validity::safeMysqlInput($id, $db);
        $this->username = Validity::safeMysqlInput($username, $db);
        $this->password = Validity::safeMysqlInput($password, $db);
    }

    public function isUserExist(): bool {
        $query = 'SELECT * FROM users WHERE username = "' . $this->username . '"';
        $result = $this->db->query($query);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getUser($username) {
        $query = 'SELECT * FROM users WHERE username = "' . $username . '"';
        $result = $this->db->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row;
        } else {
            return null;
        }
    }

    public function register(): bool {
        if ($this->isUserExist()) {
            return false;
        } else {
            $password = Security::generatePasswordHash($this->password);
            $query = 'INSERT INTO users (id, username, password) VALUES ("' . $this->id . '", "' . $this->username . '", "' . $password . '"';
            return $this->db->query($query);
        }
    }

    public function login() {
        if ($this->isUserExist()) {
            $user = $this->getUser($this->username);
            // check password using JWT
            if (Security::verifyPasswordHash($this->password, $user["password"])) {
                // set session
                initializeSession();
                $_SESSION["user"] = $user;
                $_SESSION["JWT"] = Security::generateJWT($user);
                return $_SESSION["JWT"];
            } else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function getId() {
        return $this->id;
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    public function getPassword() {
        return $this->password;
    }
}