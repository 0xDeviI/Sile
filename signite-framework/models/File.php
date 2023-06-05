<?php

namespace Signite\Models;

require_once "signite-framework/modules/Validity.php";
require_once "signite-framework/modules/Identifier.php";
require_once "signite-framework/modules/Security.php";

use Signite\Modules\Validity;
use Signite\Modules\Identifier;
use Signite\Modules\Security;

class File {
    private $id;
    private $realFile;
    private $fileName;
    private $password;
    private $ownerId;
    
    public function __construct($id = "", $realFile, $fileName, $password, $ownerId, $db) {
        $this->db = $db;
        $this->id = $id === "" ? Identifier::uuid4() : Validity::safeMysqlInput($id, $db);
        $this->realFile = Validity::safeMysqlInput($realFile, $db);
        $this->fileName = Validity::safeMysqlInput($fileName, $db);
        $this->ownerId = $ownerId;
        $this->password = $password;
    }

    public function getFileName() {
        return $this->fileName;
    }

    public function getOwnerId() {
        return $this->ownerId;
    }

    public function getHashedPassword() {
        return strlen($this->password) > 0 ? Security::generatePasswordHash($this->password) : "";
    }

    public function reId() {
        $this->id = Identifier::uuid4();
    }
    
    public function getId() {
        return $this->id;
    }

    public function getRealFile() {
        return $this->realFile;
    }

    public function getPassword() {
        return $this->password;
    }
}