<?php

namespace Signite\Modules;

function initializeSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}