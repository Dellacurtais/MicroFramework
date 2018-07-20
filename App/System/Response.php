<?php
namespace System;

class Response {
    protected static $instance = null;

    protected $allHeaders;

    public static function getInstance(){
        if (is_null(self::$instance)){
            self::$instance = new Response();
        }
        return self::$instance;
    }

    public static function xssClear(){
        $_GET   = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    }

    public static function get($key, $xss = 0){
        if ($xss)
            return filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);
        return $_GET[$key];
    }

    public static function post($key, $xss = 0){
        if ($xss)
            return filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
        return $_POST[$key];
    }

    public function request($key, $xss = 0){
        if ($xss)
            return filter_input(INPUT_REQUEST, $key, FILTER_SANITIZE_STRING);
        return $_REQUEST[$key];
    }

    public function __construct(){
        $this->allHeaders = getallheaders();
    }

    public function getHeader($key){
        return $this->allHeaders[$key];
    }

    public function setHeader($key,$value = null){
        if (is_null($value))
            header($key);
        else
            header("{$key}:{$value}");
    }

    public function headerJson(){
        $this->setHeader("Content-Type", "application/json");
    }

    public function headerHtml(){
        $this->setHeader("Content-Type", "text/html; charset=utf-8");
    }

    public function headerJs(){
        $this->setHeader("Content-Type", "application/javascript");
    }

    public function headerCss(){
        $this->setHeader("Content-Type", "text/css");
    }
}