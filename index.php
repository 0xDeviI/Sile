<?php

require_once "signite-framework/core/core.php";
use Signite\Core\Signite;

// definition
$app = new Signite("sile");

// configuration
$app->setApplicationConfig("favicon", $app->getApplicationName() . "/resources/images/favicon.png");
$app->setApplicationDirectoryVisibility(false);
$app->setTimeZone("Asia/Tehran");

// API Routes
$app->route("/api/v1/user/register", 
    "UserController@store", 
    "POST")->middleware("SafePostRequest");

$app->route("/api/v1/user/login", 
    "UserController@login", 
    "POST")->middleware("SafePostRequest");

$app->route("/api/v1/file/upload", 
    "FileController@upload", 
    "POST")->middleware("SafeFileUpload", true);

// Frontend Routes
$app->route("/", "HomeController", "GET")->middleware("IsLoggedIn", true);
$app->route("/login", "LoginController", "GET")->middleware("IsNotLoggedIn", true);
$app->route("/register", "RegisterController", "GET");
$app->route("/logout", "LogoutController", "GET");

$app->run();