<?php

require_once "signite-framework/core/core.php";
require_once "signite-framework/core/HelperFunctions.php";
require_once "signite-framework/modules/Identifier.php";
require_once "signite-framework/database/connection.php";
require_once "signite-framework/models/File.php";


use Signite\Core\Signite;
use Signite\Core\SigniteRequest;
use function Signite\Core\response;
use Signite\Modules\Identifier;
use Signite\Models\File;
use function Signite\Core\view;

class FileController {
    
    private Signite $_signiteApp;
    private $_uploadPath = "sile/uploads/";
    private $db;

    public function __construct($signiteApp)
    {
        $this->_signiteApp = $signiteApp;
        $this->db = $GLOBALS['connection']->connect();
    }

    public function isFileExist(File $file) {
        $query = 'SELECT * FROM users WHERE username = "' . $file->getId() . '"';
        $result = $this->db->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row;
        } else {
            return null;
        }
    }

    public function upload(SigniteRequest $request) {
        $file = $request->getFile("file");
        $fileName = $file["name"];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileTmpName = $file["tmp_name"];
        $fileName = $this->_uploadPath . Identifier::uniqueFileName() . "." . $fileExtension;
        if (move_uploaded_file($fileTmpName, $fileName)) {
            $file = new File("", $fileName, "", $this->db);
            while ($this->isFileExist($file) !== null) {
                $file->reId();
            }
            $query = 'INSERT INTO files (id, realFile, password) VALUES ("' . $file->getId() . '", "' . $file->getRealFile() . '", "' . $file->getHashedPassword() . '")';
            $result = $this->db->query($query);
            if ($result) {
                return response(200, [
                    "status" => 'success', 
                    "message" => 'File uploaded successfully',
                ])->json();
            }
            else {
                // unlink($fileName);
                return response(400,  'File upload failed')->json();
            }
        }
        else {
            return response(400, "File upload failed.")->json();
        }
    }
}

?>