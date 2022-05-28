<?php

require_once "signite-framework/core/core.php";
require_once "signite-framework/modules/FileDownloader.php";
use Signite\Core\Signite;
use function Signite\Modules\downloadSpecificFile;
use function Signite\Modules\download;

// definition
$app = new Signite("sile");

// configuration
$app->setApplicationConfig("favicon", $app->getApplicationName() . "/resources/images/favicon.png");
$app->setApplicationDirectoryVisibility(false);
$app->setTimeZone("Asia/Tehran");

// API Routes
$app->route("/api/v1/users/register", "UserController@store", "POST")->middleware("SafePostRequest");
$app->route("/api/v1/users/login", "UserController@login", "POST")->middleware("SafePostRequest");
$app->route("/api/v1/users/change_settings","UserController@update","POST")->middleware("SafePostRequest")->middleware("CanChangeSettings", true);
$app->route("/api/v1/users/delete_account","UserController@deleteAccount","POST")->middleware("SafePostRequest")->middleware("CanChangeSettings", true);
$app->route("/api/v1/files/upload", "FileController@upload", "POST")->middleware("SafeFileUpload", true);
$app->route("/api/v1/files/delete_all", "FileController@deleteAll", "POST")->middleware("SafePostRequest")->middleware("CanChangeSettings", true);
$app->route("/api/v1/files", "FileController@getAllFiles", "POST")->middleware("SafePostRequest")->middleware("CanChangeSettings", true);
$app->route("/api/v1/files/delete", "FileController@deleteFile", "POST")->middleware("SafePostRequest")->middleware("CanChangeSettings", true);
$app->route("/api/v1/files/protect", "FileController@protectFile", "POST")->middleware("SafePostRequest")->middleware("CanChangeSettings", true);
$app->route("/api/v1/files/group/protect", "FileController@protectFiles", "POST")->middleware("SafePostRequest")->middleware("CanChangeSettings", true);
$app->route("/api/v1/files/group/unprotect", "FileController@unprotectFiles", "POST")->middleware("SafePostRequest")->middleware("CanChangeSettings", true);
$app->route("/api/v1/files/group/delete", "FileController@deleteFiles", "POST")->middleware("SafePostRequest")->middleware("CanChangeSettings", true);
$app->route("/api/v1/files/unprotect", "FileController@unprotectFile", "POST")->middleware("SafePostRequest")->middleware("CanChangeSettings", true);
$app->route("/api/v1/files/unlock", "FileController@unlockFile", "POST")->middleware("SafePostRequest");
$app->route("/api/v1/files/unlock/{download_token}", function($params) use ($app) {
    downloadSpecificFile($app, $params["download_token"]);
}, "GET");
$app->route("/api/v1/files/download/{file}", function($params) use ($app) {
    return download($app, $params["file"]);
}, "GET");

// Frontend Routes
$app->route("/", "HomeController", "GET")->middleware("IsLoggedIn", true);
$app->route("/login", "LoginController", "GET")->middleware("IsNotLoggedIn", true);
$app->route("/register", "RegisterController", "GET");
$app->route("/logout", "LogoutController", "GET");

$app->run();