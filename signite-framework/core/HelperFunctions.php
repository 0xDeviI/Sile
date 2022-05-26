<?php

namespace Signite\Core;

use Signite\Core\SigniteRender;
use Signite\Core\SigniteResponse;

function view($path, $data = array(), $useApplicationPath = false) {
    $signiteRender = new SigniteRender();
    return $signiteRender->render($path, $data, $useApplicationPath);
}

function response($status, $data, $headers = array()) {
    $signiteResponse = new SigniteResponse($status, $data, $headers);
    return $signiteResponse;
}

?>