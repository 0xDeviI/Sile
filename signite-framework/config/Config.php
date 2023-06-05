<?php

$_SIGNITECONFIG = [
    "timezone" => "Asia/Tehran",
    "debug" => true,
    "path" => [
        "path_access_allowed" => [
        ],
        "path_access_denied" => [
            "signite-framework/*"
        ],
    ]
];

$GLOBALS["_SIGNITECONFIG"] = $_SIGNITECONFIG;