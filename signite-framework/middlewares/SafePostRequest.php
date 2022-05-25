<?php

require_once "signite-framework/modules/middleware-result.php";
require_once "signite-framework/modules/security.php";

use Signite\Modules\MiddlewareResult;
use Signite\Modules\Security;

class AdminAuth {

    public function __construct()
    {
        // empty constructor
    }

    public function handle(): MiddlewareResult
    {
        Security::safePost();
        return new MiddlewareResult($this::class, true, "post request is safe.", null);
    }
}