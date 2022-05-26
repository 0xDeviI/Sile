<?php

require_once "signite-framework/modules/MiddlewareResult.php";
require_once "signite-framework/modules/Security.php";
require_once "signite-framework/modules/Session.php";
require_once "signite-framework/core/HelperFunctions.php";

use Signite\Modules\MiddlewareResult;
use Signite\Modules\Security;
use function Signite\Core\response;
use function Signite\Modules\initializeSession;

class IsNotLoggedIn {

    public function __construct()
    {
        initializeSession();
    }

    public function handle(): MiddlewareResult
    {
        $isUserExist = isset($_SESSION['user']);
        $isJWTExist = isset($_SESSION['JWT']);
        if ($isUserExist && $isJWTExist) {
            $isJWTApproved = $isJWTExist ? Security::verifyJWT($_SESSION["JWT"]) : false;
            if ($isJWTApproved !== false){
                if ($isJWTApproved["data"]["username"] === $_SESSION["user"]["username"]) {
                    return new MiddlewareResult($this::class, false, "user logged in.", null, function() {
                        return response()->redirect("/");
                    });
                }
                else {
                    return new MiddlewareResult($this::class, true, "user not logged in.");
                }
            }
            else {
                return new MiddlewareResult($this::class, true, "user not logged in.");
            }
        }
        else {
            return new MiddlewareResult($this::class, true, "user not logged in.");
        }
    }
}