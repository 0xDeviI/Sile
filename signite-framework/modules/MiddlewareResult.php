<?php

namespace Signite\Modules;

class MiddlewareResult {
    private $_middlewareName;
    private $_success;
    private $_message;
    private $_data;
    private $_onMiddlewareFailed;

    public function __construct($middlewareName, $success, $message, $data, $onMiddlewareFailed = null) {
        $this->_success = $success;
        $this->_message = $message;
        $this->_data = $data;
        $this->_middlewareName = $middlewareName;
        $this->_onMiddlewareFailed = $onMiddlewareFailed;
    }
    
    public function getOnMiddlewareFailed() {
        return $this->_onMiddlewareFailed;
    }

    public function getResult(){
        return json_encode(array(
            "middleware" => $this->_middlewareName,
            "success" => $this->_success,
            "message" => $this->_message,
            "data" => $this->_data
        ));
    }
}