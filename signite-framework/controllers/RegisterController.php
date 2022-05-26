<?php

require_once "signite-framework/core/core.php";
require_once "signite-framework/modules/Validity.php";
require_once "signite-framework/models/User.php";
require_once "signite-framework/core/HelperFunctions.php";

use Signite\Core\Signite;
use Signite\Modules\Validity;
use Signite\Models\User;
use function Signite\Core\view;

class RegisterController {
    
    private Signite $_signiteApp;

    public function __construct($signiteApp)
    {
        $this->_signiteApp = $signiteApp;
    }

    public function __invoke() {
        return view("register.php", [
                "application_name" => $this->_signiteApp->getApplicationName(),
                "favicon" => $this->_signiteApp->getApplicationConfig("favicon"),
                "page_title" => "Sile - register an account"
        ], true);
    }

}

?>