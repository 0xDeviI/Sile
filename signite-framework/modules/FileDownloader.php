<?php

namespace Signite\Modules;

require_once "signite-framework/controllers/FileController.php";
require_once "signite-framework/core/HelperFunctions.php";
require_once "signite-framework/modules/Identifier.php";

use Signite\Modules\Identifier;
use function Signite\Core\response;
use function Signite\Core\view;

function downloadSpecificFile($signiteApp, $downloadKey) {
    $downloadKey = base64_decode($downloadKey);
    $keySeparated = explode(":", $downloadKey);
    $uploadsDir = "uploads";
    $dir = $keySeparated[0];
    $fileName = $keySeparated[1];
    $fileExtension = $keySeparated[2];
    $filePath = $signiteApp->getApplicationName() . "/$uploadsDir/" . $dir . "/" . $fileName . "." . $fileExtension;
    header("Content-disposition: attachment;filename=$fileName.$fileExtension");
    readfile($filePath);
    unlink($filePath);
    rmdir($signiteApp->getApplicationName() . "/$uploadsDir/" . $dir);
}

function downloadProtectedFile($signiteApp, $fileId) {
    $file = new \FileController($signiteApp);
    $_file = $file->getFile($fileId);
    $filePath = $_file["realFile"];
    $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
    $randomDirName = Identifier::uniqueFileName();
    $randomFileName = Identifier::uniqueFileName() . "." . $fileExtension;
    $uploadsDir = "uploads";
    $newDir = $signiteApp->getApplicationName() . "/$uploadsDir/" . $randomDirName;
    if (mkdir($newDir, 0777, true)) {
        $newFilePath = $newDir . "/" . $randomFileName;
        if (copy($filePath, $newFilePath)) {
            $encodedKey = $randomDirName . ":" . explode(".", $randomFileName)[0] . ":" . $fileExtension;
            return response(200, [
                "status" => "success",
                "message" => "File downloaded successfully",
                "download_token" => base64_encode($encodedKey)
            ])->json();
        } else {
            return response(200, [
                "status" => "error",
                "message" => "Error downloading file"
            ])->json();
        }
    } else {
        return response(200, [
            "status" => "errsor",
            "message" => "Error downloading file"
        ])->json();
    }
}

function download($signiteApp, $fileId, $passwordVerified = null) {
    $file = new \FileController($signiteApp);
    $_file = $file->getFile($fileId);
    $filePath = $_file["realFile"];
    $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
    $randomFileName = Identifier::uniqueFileName() . "." . $fileExtension;
    $filePassword = $_file['password'];
    if (strlen($filePassword) > 0 && $passwordVerified !== true)
        return view("protected-download.php", [
            "application_name" => $signiteApp->getApplicationName(),
            "favicon" => $signiteApp->getApplicationConfig("favicon"),
            "page_title" => "Sile - Download Protected File"
        ], true);
    else {
        header("Content-disposition: attachment;filename=$randomFileName");
        readfile($filePath);
    }
}

?>