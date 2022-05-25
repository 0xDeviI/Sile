<?php

namespace Signite\Modules;

function getFilesInDirectory($dir) {
    $files = scandir($dir);
    $files = array_diff($files, array('.', '..'));
    $_files = array();
    foreach ($files as $key => $value) {
        if (!is_dir($dir . "/" . $value)){
            $_files[] = [
                "name" => $value,
                "is_dir" => is_dir($dir . "/" . $value) ? true : false,
                "size" => getFileSize($dir . "/" . $value),
                "time" => date("Y-m-d H:i:s", filemtime($dir . "/" . $value)),
                "path" => "$value"
            ];
        }
    }
    return $_files;
}

function getDirectoriesInDirectory($dir) {
    $files = scandir($dir);
    $files = array_diff($files, array('.', '..'));
    $_files = array();
    foreach ($files as $key => $value) {
        if (is_dir($dir . "/" . $value)){
            $_files[] = [
                "name" => $value,
                "is_dir" => is_dir($dir . "/" . $value) ? true : false,
                "size" => getFileSize($dir . "/" . $value),
                "time" => date("Y-m-d H:i:s", filemtime($dir . "/" . $value)),
                "path" => "$value/"
            ];
        }
    }
    return $_files;
}

function generatePathInArray($path, $arr){
    $_path = "";
    for ($i = 0; $i < count($arr); $i++) {
        if ($arr[$i] == $path) {
            $_path .= $arr[$i] . "/";
            return "/" . $_path;
        }
        else{
            $_path .= $arr[$i] . "/";
        }
    }
}

function getFileSize($file) {
    $size = filesize($file);
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}