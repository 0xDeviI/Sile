<?php

require_once "signite-framework/core/core.php";
require_once "signite-framework/core/HelperFunctions.php";

use Signite\Core\Signite;
use Signite\Core\SigniteRequest;
use function Signite\Core\response;
use function Signite\Core\view;

class UserController {
    
    private Signite $_signiteApp;

    public function __construct($signiteApp)
    {
        $this->_signiteApp = $signiteApp;
    }

    public function index() {
        //
    }

    public static function create() {
        //
    }

    public function store(SigniteRequest $request) {
        return response(200, [
            "message" => "User created successfully",
            "data" => json_decode($request->__toString())
        ])->json();
    }

    public function show($id) {
        //
    }

    public function edit($id) {
        //
    }

    public function update(SigniteRequest $request, $id) {
        //
    }

    public function destroy($id) {
        //
    }

}

?>