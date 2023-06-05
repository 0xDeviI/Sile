<?php

require_once "signite-framework/modules/MiddlewareResult.php";
require_once "signite-framework/modules/Security.php";

use Signite\Modules\MiddlewareResult;
use Signite\Modules\Security;

class SafePostRequest {

    public function __construct()
    {
        // empty constructor
    }

    public function handle(): MiddlewareResult
    {
        Security::safePost();
        return new MiddlewareResult($this::class, true, "post request is safe.");
    }
}