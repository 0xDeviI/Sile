<?php

require_once "signite-framework/core/core.php";
require_once "signite-framework/core/HelperFunctions.php";

use Signite\Core\Signite;
use function Signite\Core\view;

class LoginController {
    
    private Signite $_signiteApp;

    public function __construct($signiteApp)
    {
        $this->_signiteApp = $signiteApp;
    }

    public function __invoke() {
        return view("login.php", [
            "application_name" => $this->_signiteApp->getApplicationName(),
            "favicon" => $this->_signiteApp->getApplicationConfig("favicon"),
            "page_title" => "sile - simple file upload solution"
        ], true);
    }

}

?>