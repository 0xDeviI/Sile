<?php

require_once "signite-framework/modules/MysqlUtill.php";

use Signite\Modules\MYSQL_Util;
use Signite\Modules\Table;
use Signite\Modules\Column;

$_HOST = "localhost";
$_USERNAME = "root";
$_PASSWORD = "";
$_DATABASE = "sile_dbstroe_IUjayP2GaL8WCvyD";

$connection = new MYSQL_Util($_HOST, $_USERNAME, $_PASSWORD, $_DATABASE, [
    new Table("Users", [
        new Column("id", "varchar", 36, false, true, false),
        new Column("username", "varchar", 16, false, false, false),
        new Column("password", "text", 255, false, false, false)
    ]),
    new Table("Files", [
        new Column("id", "varchar", 36, false, true, false),
        new Column("realFile", "text", 255, false, false, false),
        new Column("password", "text", 255, false, false, false),
        new Column("ownerId", "varchar", 36, false, false, false)
    ])
]);

function secureDataInput($data) {
    global $connection;
    $data = htmlspecialchars(stripslashes(trim($data)));
    $socketConnection = $connection->connect();
    return $socketConnection != null ? mysqli_real_escape_string($socketConnection, $data) : $data;
}

$GLOBALS["connection"] = $connection;