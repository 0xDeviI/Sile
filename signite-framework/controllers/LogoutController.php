<?php

require_once "signite-framework/core/core.php";
require_once "signite-framework/core/HelperFunctions.php";

use Signite\Core\Signite;
use function Signite\Core\response;

class LogoutController {
    
    private Signite $_signiteApp;

    public function __construct($signiteApp)
    {
        $this->_signiteApp = $signiteApp;
    }

    public function __invoke() {
        session_destroy();
        response()->redirect("/login");
    }

}

?>