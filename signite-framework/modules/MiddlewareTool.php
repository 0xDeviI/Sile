<?php

namespace Signite\Modules;
use Exception;

class SigniteMiddlewareTool {
    function isMiddlewareExist($middlewareName) {
        $middlewareFile = "signite-framework/middlewares/" . $middlewareName . ".php";
        if (file_exists($middlewareFile)) {
            return true;
        }
        return false;
    }

    function loadMiddleware($middleware): Object {
        $middlewareFile = "signite-framework/middlewares/" . $middleware . ".php";
        if (file_exists($middlewareFile)) {
            require_once $middlewareFile;
            eval('$loadedMiddleware = new ' . $middleware . '();');
            return $loadedMiddleware;
        }
        else{
            throw new Exception("Middleware '$middleware' not found.");
        }
    }
}