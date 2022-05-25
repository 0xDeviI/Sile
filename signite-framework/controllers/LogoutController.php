<?php

require_once "signite-framework/core/core.php";

use Signite\Core\Signite;

class LogoutController {
    
    private Signite $_signiteApp;

    public function __construct($signiteApp)
    {
        $this->_signiteApp = $signiteApp;
    }

    public function __invoke() {
        session_destroy();
        $this->_signiteApp->redirect("/");
    }

}

?>