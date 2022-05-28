<?php

require_once "signite-framework/core/core.php";
require_once "signite-framework/core/HelperFunctions.php";
require_once "signite-framework/modules/Identifier.php";
require_once "signite-framework/database/connection.php";
require_once "signite-framework/models/File.php";
require_once "signite-framework/modules/Security.php";
require_once "signite-framework/modules/FileDownloader.php";


use Signite\Core\Signite;
use Signite\Core\SigniteRequest;
use Signite\Modules\Identifier;
use Signite\Modules\Security;
use Signite\Models\File;
use function Signite\Core\response;
use function Signite\Modules\downloadProtectedFile;
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

    public function deleteFile(SigniteRequest $request) {
        $id = $request->get("id");
        $query = "SELECT * FROM files WHERE id = '$id'";
        $result = $this->db->query($query);
        if ($result->num_rows > 0) {
            $filePath = $result->fetch_assoc()["realFile"];
            $query = "DELETE FROM files WHERE id = '$id'";
            $result = $this->db->query($query);
            $fileRemoveResult = unlink($filePath);
            if ($result && $fileRemoveResult) {
                return response(200, [
                    "status" => "success",
                    "message" => "File deleted successfully"
                ])->json();
            } else {
                return response(200, [
                    "status" => "error",
                    "message" => "Error deleting file"
                ])->json();
            }
        } else {
            return response(200, [
                "status" => "error",
                "message" => "File not found"
            ])->json();
        }
    }

    public function unlockFile(SigniteRequest $request) {
        $id = $request->get("id");
        $password = $request->get("password");
        $query = "SELECT * FROM files WHERE id = '$id'";
        $result = $this->db->query($query);
        if ($result) {
            $dbPassword = $result->fetch_assoc()["password"];
            if (Security::verifyPasswordHash($password, $dbPassword)) {
                return downloadProtectedFile($this->_signiteApp, $id);
            } else {
                return response(200, [
                    "status" => "error",
                    "message" => "Invalid password"
                ])->json();
            }
        }
        else {
            return response(200, [
                "status" => "error",
                "message" => "File not found"
            ])->json();
        }
    }

    public function protectFile(SigniteRequest $request) {
        $id = $request->get("id");
        $password = $request->get("password");
        $hashedPassword = Security::generatePasswordHash($password);
        $query = "UPDATE files SET password = '$hashedPassword' WHERE id = '$id'";
        $result = $this->db->query($query);
        if ($result) {
            return response(200, [
                "status" => "success",
                "message" => "File protected successfully"
            ])->json();
        } else {
            return response(200, [
                "status" => "error",
                "message" => "Error protecting file"
            ])->json();
        }
    }

    public function unprotectFile(SigniteRequest $request) {
        $id = $request->get("id");
        $query = "UPDATE files SET password = '' WHERE id = '$id'";
        $result = $this->db->query($query);
        if ($result) {
            return response(200, [
                "status" => "success",
                "message" => "File unprotected successfully"
            ])->json();
        } else {
            return response(200, [
                "status" => "error",
                "message" => "Error unprotecting file"
            ])->json();
        }
    }

    public function getAllFiles(SigniteRequest $request) {
        $ownerId = $request->get("id");
        $query = "SELECT * FROM files WHERE ownerId = '$ownerId'";
        $result = $this->db->query($query);
        $files = [];
        while($row = $result->fetch_assoc()) {
            $files[] = $row;
        }
        return response()->json([
            "status" => "success",
            "data" => $files
        ]);
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

    public function getFile($fileId) {
        $query = 'SELECT * FROM files WHERE id = "' . $fileId . '"';
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
            $file = new File("", $fileName, "", $request->get("id"), $this->db);
            while ($this->isFileExist($file) !== null) {
                $file->reId();
            }
            $query = 'INSERT INTO files (id, realFile, password, ownerId) VALUES ("' . $file->getId() . '", "' . $file->getRealFile() . '", "' . $file->getHashedPassword() . '", "' . $file->getOwnerId() . '")';
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

    public function deleteAll(SigniteRequest $request) {
        $ownerId = $request->get("id");
        $query = 'SELECT * FROM files WHERE ownerId = "' . $ownerId . '"';
        $result = $this->db->query($query);
        if ($result) {
            $_result = true;
            // access all rows
            while ($row = $result->fetch_assoc()) {
                $query = 'DELETE FROM files WHERE id = "' . $row["id"] . '"';
                $resultx = $this->db->query($query);
                if (!$resultx)
                    $_result = false;
                else {
                    unlink($row["realFile"]);
                }
            }
            if ($_result) {
                return response(200, [
                    "status" => 'success', 
                    "message" => 'All files deleted successfully',
                ])->json();
            }
            else {
                return response(200,  [
                    "status" => 'error', 
                    "message" => 'All files deletion failed',
                ])->json();
            }
        }
        else {
            return response(400, [
                "status" => 'error', 
                "message" => "File deletion failed.",
            ])->json();
        }
    }
}

?>