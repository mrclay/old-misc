<?php

class NoCms {
    private $_config = null;
    public $user = null;
    public $requestPath = null;
    
    public function __construct($config)
    {
        $this->_config = $config;
    }
    
    public function getConfig($key, $default = null)
    {
        return isset($this->_config[$key])
            ? $this->_config[$key]
            : $default;
    }
    
    public function redirect($path = '/')
    {
        if ($path === '') {
            $path = $this->requestPath();
        }
        header("Location: " . $this->getUrl($path));
    }
    
    public function getUrl($path)
    {
        if ($path !== '' && $path[0] === '/') {
            $path = $this->_config['htmlRoot'] . '/index.php' . $path;
        }
        return $path;
    }
    
    public function init()
    {
        if (! isset($_SERVER['PATH_INFO'])) {
            $this->redirect();
            return;
        }
        $this->requestPath = $_SERVER['PATH_INFO'];
        
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session('nocms'));
        if (! $auth->hasIdentity()) {
            if ($this->requestPath !== '/login') {
                $this->redirect('/login');
            }
            $this->_handleRequest('/login');
        } else {
            $this->user = $auth->getIdentity();
            $this->_handleRequest();
        }
    }
    
    private function _handleRequest($path = false) 
    {   
        if ($path === false) {
            $path = $_SERVER['PATH_INFO'];    
        }
        $urls = self::_getUrlFormats();
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        foreach ($urls as $regex => $class) {
            $class = 'NoCms_Action_' . $class;
            if (preg_match($regex, $path, $matches)) {
                if (class_exists($class)) {
                    $obj = new $class($this);
                    if (method_exists($obj, $method)) {
                        $obj->$method($matches);
                    } else {
                        throw new BadMethodCallException("Method, $method, not supported.");
                    }
                } else {
                    throw new Exception("Class, $class, not found.");
                }
                return;
            }
        }
        throw new Exception("URL, $path, not found.");
    }
    
    private static function _getUrlFormats()
    {
        return array(
            // regex => NoCms_Action_*
            '@^/$@' => 'index'
            ,'@^/login$@' => 'login'
            ,'@^/logout$@' => 'logout'
            ,'@^/show/(.+)$@' => 'show'
        );
    }
}
