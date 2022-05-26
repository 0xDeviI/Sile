<?php

require_once "signite-framework/modules/MysqlUtill.php";

use Signite\Modules\MYSQL_Util;
use Signite\Modules\Table;
use Signite\Modules\Column;

$_HOST = "localhost";
$_USERNAME = "root";
$_PASSWORD = "";
$_DATABASE = "geekhub_dbstroe_IUjayP2GaL8WCvyD";

$connection = new MYSQL_Util($_HOST, $_USERNAME, $_PASSWORD, $_DATABASE, [
    new Table("Users", [
        new Column("id", "varchar", 36, false, true, false),
        new Column("name", "varchar", 255, false, false, false),
        new Column("username", "varchar", 16, false, false, false),
        new Column("password", "text", 255, false, false, false),
        new Column("email", "text", 255, false, false, false),
        new Column("amount", "int", 11, false, false, false),
        new Column("roles", "json", 255, false, false, false),
        new Column("profile_image", "text", 255, true, false, false),
        new Column("about", "text", 255, true, false, false),
        new Column("join_date", "datetime", 0, false, false, false),
        new Column("is_banned", "tinyint", 1, false, false, false)
    ])
]);

function secureDataInput($data) {
    global $connection;
    $data = htmlspecialchars(stripslashes(trim($data)));
    $socketConnection = $connection->connect();
    return $socketConnection != null ? mysqli_real_escape_string($socketConnection, $data) : $data;
}

$GLOBALS["connection"] = $connection;