<?php
namespace System\Libraries;

use System\FastApp;

class Session {
    protected static $instance;

    public static function getInstance(){
        if (self::$instance ==  null){
            self::$instance = new Session();
            self::$instance->init();
        }
        return self::$instance;
    }

    /**
     * Init a session
     */
    public function init(){
        session_name(FastApp::getInstance()->getConfig("session_id"));
        session_start();
    }

    /**
     * Set a flash data
     *
     * @param $key string
     * @param $message string|mixed
     */
    public function setFlash($key, $message){
        $Flash = $this->get("flash_data");

        if (isset($Flash[$key]))
            unset($Flash[$key]);

        $data = array_merge( is_array($Flash) ? $Flash : [] , [ $key => $message ] );

        $this->set("flash_data",$data);
    }

    /**
     * Get a flash data session
     *
     * @param $key string
     * @return null|string|mixed
     */
    public function getFlash($key){
        $FlashData = $this->get("flash_data");
        $Return = isset($FlashData[$key]) ? $FlashData[$key] : null;
        if (isset($FlashData[$key])) {
            unset($FlashData[$key]);
            $this->set("flash_data", $FlashData);
        }
        return $Return;
    }

    /**
     * Get a session variable.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null){
        return $this->exists($key)
            ? $_SESSION[$key]
            : $default;
    }

    /**
     * Set a session variable.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value){
        $_SESSION[$key] = $value;
        return $this;
    }

    /**
     * Merge values recursively.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function merge($key, $value){
        if (is_array($value) && is_array($old = $this->get($key))) {
            $value = array_merge_recursive($old, $value);
        }
        return $this->set($key, $value);
    }

    /**
     * Delete a session variable.
     *
     * @param string $key
     *
     * @return $this
     */
    public function delete($key){
        if ($this->exists($key)) {
            unset($_SESSION[$key]);
        }
        return $this;
    }

    /**
     * Clear all session variables.
     *
     * @return $this
     */
    public function clear(){
        $_SESSION = [];
        return $this;
    }

    /**
     * Check if a session variable is set.
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists($key){
        return array_key_exists($key, $_SESSION);
    }

    /**
     * Get or regenerate current session ID.
     *
     * @param bool $new
     *
     * @return string
     */
    public static function id($new = false){
        if ($new && session_id()) {
            session_regenerate_id(true);
        }
        return session_id() ?: '';
    }

    /**
     * Destroy the session.
     */
    public static function destroy(){
        if (self::id()) {
            session_unset();
            session_destroy();
            session_write_close();
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 4200,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
        }
    }

    /**
     * Magic method for get.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key){
        return $this->get($key);
    }

    /**
     * Magic method for set.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value){
        $this->set($key, $value);
    }

    /**
     * Magic method for delete.
     *
     * @param string $key
     */
    public function __unset($key){
        $this->delete($key);
    }

    /**
     * Magic method for exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key){
        return $this->exists($key);
    }

    /**
     * Count elements of an object.
     *
     * @return int
     */
    public function count(){
        return count($_SESSION);
    }

    /**
     * Retrieve an external Iterator.
     *
     * @return \Traversable
     */
    public function getIterator(){
        return new \ArrayIterator($_SESSION);
    }

    /**
     * Whether an array offset exists.
     *
     * @param mixed $offset
     *
     * @return boolean
     */
    public function offsetExists($offset){
        return $this->exists($offset);
    }

    /**
     * Retrieve value by offset.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset){
        return $this->get($offset);
    }

    /**
     * Set a value by offset.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value){
        $this->set($offset, $value);
    }

    /**
     * Remove a value by offset.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset){
        $this->delete($offset);
    }
}