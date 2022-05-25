<?php

require_once "signite-framework/modules/middleware-result.php";
require_once "signite-framework/modules/security.php";

use Signite\Modules\MiddlewareResult;
use Signite\Modules\Security;

class AdminAuth {

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function handle(): MiddlewareResult
    {
        $isJWTExist = isset($_SESSION['JWT']);
        $isJWTApproved = $isJWTExist ? Security::verifyJWT($_SESSION["JWT"]) : false;
        if ($isJWTApproved !== false){
            $isJWTApproved["data"]["roles"] = json_decode($isJWTApproved["data"]["roles"]); // cause roles is string in default
        }
        $isAdmin = $isJWTExist && $isJWTApproved !== false ? in_array('admin', $isJWTApproved["data"]["roles"]) : false;
        $result = isset($_SESSION["admin"]) && $_SESSION["admin"] === true
        && $isJWTExist && $isAdmin;

        return new MiddlewareResult($this::class, $result, 
            $result ? "admin logged in." : "You need to get logged in to use service.", null);
    }
}