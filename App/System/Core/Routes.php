<?php
namespace System\Core;

use System\Request;
use System\Response;

class Routes {

    protected static $Routes = [];
    protected static $DynamicRoutes = [];

    /**
     * @param $type String Type Request
     * @param String $route route url
     * @param array $class
     * @param array $Headers Response Headers
     * @param array $RequireHeader Require headers on request
     * @param array $onCallBefore Call on Before method controller
     * @param array $onCallAfter Call on After method controller
     * @param array $onCallFinish Call on Finish controller
     */
    public static function simple($type, $route, $class, $Headers = [], $RequireHeader = [], $onCallBefore = [], $onCallAfter = [], $onCallFinish = []){
        self::setRoute($type, $route, [
            'Controller' => $class[0],
            "Method" => $class[1],
            'Headers' => $Headers,
            'RequireHeader' => $RequireHeader,
            'onCallBefore' => $onCallBefore,
            'onCallAfter' => $onCallAfter,
            'onCallFinish' => $onCallFinish
        ]);
    }

    /**
     * @param $route
     * @param $configs
     */
    public static function all($route, $configs){
        self::setRoute(Response::ALL, $route, $configs);
    }

    /**
     * @param $route
     * @param $configs
     */
    public static function get($route, $configs){
        self::setRoute(Response::GET, $route, $configs);
    }

    /**
     * @param $route
     * @param $configs
     */
    public static function post($route, $configs){
        self::setRoute(Response::POST, $route, $configs);
    }

    /**
     * @param $route
     * @param $configs
     */
    public static function put($route, $configs){
        self::setRoute(Response::PUT, $route, $configs);
    }

    /**
     * @param $route
     * @param $configs
     */
    public static function delete($route, $configs){
        self::setRoute(Response::DELETE, $route, $configs);
    }

    /**
     * @param $route
     * @param $configs
     */
    public static function patch($route, $configs){
        self::setRoute(Response::PATCH, $route, $configs);
    }

    /**
     * @param $route
     * @param $configs
     */
    public static function options($route, $configs){
        self::setRoute(Response::OPTIONS, $route, $configs);
    }

    /**
     * @param $route
     * @param $configs
     */
    public static function head($route, $configs){
        self::setRoute(Response::HEAD, $route, $configs);
    }

    /**
     * @param $type
     * @param $route
     * @param $configs
     */
    public static function other($type, $route, $configs){
        self::setRoute($type, $route, $configs);
    }

    /**
     * @param $type
     * @param $base
     * @param $controllers
     * @param $config
     */
    public static function group($type, $base, $controllers, $config){
        foreach ($controllers as $route => $controller){
            $configs = array_merge($config, $controller);
            if (!empty($route)) {
                $route = "{$base}/{$route}";
            }else{
                $route = $base;
            }

            $finalRoute = str_replace("//", "/", $route);
            self::other($type, $finalRoute, $configs);
        }
    }

    /**
     * @param $route
     * @param $method
     * @return mixed|null
     */
    public static function getRoute($route, $method){
        if (isset(self::$Routes[$method][$route])){
            return self::$Routes[$method][$route];
        }
        if (isset(self::$Routes[Response::ALL][$route])){
            return self::$Routes[Response::ALL][$route];
        }
        return null;
    }

    /**
     * @param $route
     * @param $method
     * @return bool
     */
    public static function verifyRoute($route, $method){
        if (isset(self::$Routes[$method][$route])){
            return true;
        }
        if (isset(self::$Routes[Response::ALL][$route])){
            return true;
        }

        foreach (self::$DynamicRoutes as $dRoute => $nRoute){
            foreach ($nRoute as $type => $args){
                $key = str_replace([':any', ':num'], ['[^/]+', '[0-9]+'], $type);

                if (preg_match('#^'.$key.'$#', $route, $matches)){
                    self::$Routes[$dRoute][$matches[0]] = $args;
                    return true;
                    break;
                }
            }
        }
        return false;
    }

    /**
     * @param $Route
     */
    public static function validateRoute($Route){
        if (isset($Route['Headers']) && is_array($Route['Headers'])){
            foreach ($Route['Headers'] as $header){
                Response::getInstance()->setHeader($header);
            }
        }
        if (isset($Route['RequireHeader']) && is_array($Route['RequireHeader'])){
            foreach ($Route['RequireHeader'] as $key=>$header){
                if (Request::getInstance()->getHeader($key) !== $header){
                    HooksRoutes::getInstance()->onCallError("No have \"{$key}\" in request header");
                }
            }
        }
    }

    /**
     * @param $method
     * @param $route
     * @param $configs
     */
    protected static function setRoute($method, $route, $configs){
        if (strpos($route, "(:any)") !== false || strpos($route,"(:num)") !== false){
            self::$DynamicRoutes[$method][$route] = $configs;
            return;
        }
        self::$Routes[$method][$route] = $configs;
    }

    public static function clearRoutes(){
        self::$DynamicRoutes = [];
        self::$Routes = [];
    }
}