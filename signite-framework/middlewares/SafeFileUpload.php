<?php

require_once "signite-framework/modules/MiddlewareResult.php";
require_once "signite-framework/modules/Security.php";
require_once "signite-framework/modules/Session.php";
require_once "signite-framework/core/HelperFunctions.php";

use Signite\Modules\MiddlewareResult;
use Signite\Modules\Security;
use function Signite\Core\response;
use function Signite\Modules\initializeSession;

class SafeFileUpload {

    private $_allowedFileExtensions = ["jpg", "jpeg", "png", "gif", "pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "mp3", "mp4", "avi", "mov", "wmv", "flv", "mpg", "mpeg", "zip", "rar"];
    private $_allowedFileMimeTypes = ["image/jpeg", "image/png", "image/gif", "application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.ms-powerpoint", "application/vnd.openxmlformats-officedocument.presentationml.presentation", "text/plain", "audio/mpeg", "video/mp4", "video/x-msvideo", "video/x-ms-wmv", "video/x-flv", "video/mpeg", "application/x-rar-compressed", "application/octet-stream", "application/zip", "application/octet-stream", "application/x-zip-compressed", "multipart/x-zip"];
    private $_allowedFileSize = 20971520; // 20MB
    private $_uploadPath = "sile/uploads/";

    public function __construct()
    {
        if (!file_exists($this->_uploadPath)) {
            mkdir($this->_uploadPath, 0777, true);
        }
    }

    public function handle(): MiddlewareResult
    {
        if (!isset($_FILES["file"])) {
            return new MiddlewareResult($this::class, false, "file not found.");
        }
        else {
            // check for safe file uploading
            $file = $_FILES["file"];
            $fileName = $file["name"];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $fileSize = $file["size"];
            $fileTmpName = $file["tmp_name"];

            if (!in_array($fileExtension, $this->_allowedFileExtensions)) {
                return new MiddlewareResult($this::class, false, "file extension not allowed.", null, function () {
                    return response(400, "file extension not allowed.");
                });
            }
            else if (!in_array($file["type"], $this->_allowedFileMimeTypes)) {
                return new MiddlewareResult($this::class, false, "file mime type not allowed.", null, function () {
                    return response(400, "file mime type not allowed.");
                });
            }
            else if ($fileSize > $this->_allowedFileSize) {
                return new MiddlewareResult($this::class, false, "file size is too large.", null, function () {
                    return response(400, "file size is too large.");
                });
            }
            else {
                return new MiddlewareResult($this::class, true, "file is safe to upload.");
            }
        }
    }
}