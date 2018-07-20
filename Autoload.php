<?php
define("BASE_PATH", __DIR__."/App/");
define("BASE_PATH_THIRD", __DIR__."/App/Third/");

function loaderFastApp($class) {
    $filename = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('\\', '/', $class) . '.php';
    if (file_exists($filename)) {
        require_once($filename);
    }else{
        $filename = BASE_PATH_THIRD . DIRECTORY_SEPARATOR . str_replace('\\', '/', $class) . '.php';
        if (file_exists($filename)) {
            require_once($filename);
        }
    }
}

spl_autoload_register('loaderFastApp');