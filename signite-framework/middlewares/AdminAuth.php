<?php

require_once "signite-framework/modules/MiddlewareResult.php";
require_once "signite-framework/modules/Security.php";
require_once "signite-framework/modules/Session.php";

use Signite\Modules\MiddlewareResult;
use Signite\Modules\Security;
use function Signite\Modules\initializeSession;

class AdminAuth {

    public function __construct()
    {
        initializeSession();
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
            $result ? "admin logged in." : "You need to get logged in to use service.");
    }
}