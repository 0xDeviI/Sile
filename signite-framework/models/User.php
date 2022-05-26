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
    
    public function __construct($id = "", $username, $password, $db) {
        $this->db = $db;
        $this->id = $id === "" ? Identifier::uuid4() : Validity::safeMysqlInput($id, $db);
        $this->username = Validity::safeMysqlInput($username, $db);
        $this->password = Validity::safeMysqlInput($password, $db);
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