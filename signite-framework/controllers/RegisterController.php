<?php

require_once "signite-framework/core/core.php";
require_once "signite-framework/modules/validity.php";
require_once "signite-framework/models/User.php";

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

    // public static function create() {

    // }

    // public function store($data) {

    // }

    // public function show($id) {

    // }

    // public function edit($id) {

    // }

    // public function update($id, $data) {

    // }

}

?>