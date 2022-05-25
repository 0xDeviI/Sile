<?php

require_once "signite-framework/core/core.php";

use Signite\Core\Signite;
use Signite\Core\SigniteRouter;

// definitation
$signiteApp = new Signite("sile");
$router = new SigniteRouter($signiteApp);

// configuration
$signiteApp->setApplicationConfig("favicon", $signiteApp->getApplicationName() . "/resources/images/favicon.png");
$signiteApp->setApplicationDirectoryVisibility(false, $router);
$signiteApp->setTimeZone("Asia/Tehran");

// API Routes
$router->route("/api/v1/user/register", "UserController@store", "POST")->middleware("SafePostRequest");

// Frontend Routes
$router->route("/", "LoginController", "GET");
$router->route("/register", "RegisterController", "GET");
$router->route("/logout", "LogoutController", "GET");


$router->run();