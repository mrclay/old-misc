<?php

/**
 * Used to wrap static method calls in a dynamic class for DI/OO purposes
 *
 * Say you'd like to use a class "Worker" whose API is all static methods, but
 * you'd like to be able to mock its methods for testing, or type hint in
 * arguments:
 *
 * <code>
 * class Worker {
 *     static function perform() { echo "(grunts)"; }
 * }
 *
 * class DynamicWorker extends MrClay_StaticWrapper {
 *     protected $_staticClassName = 'Worker';
 * }
 * $worker = new DynamicWorker();
 *
 * function doWork(DynamicWorker $worker) {
 *     $worker->perform();
 * }
 *
 * doWork($worker); // Worker::perform() is called
 *
 * $worker->__setMethodCallback('perform', function () {
 *     echo "Hello";
 * });
 *
 * doWork($worker); // says Hello
 *
 * </code>
 */
abstract class MrClay_StaticWrapper {
    
    /**
     * Name of static class being proxied
     * @var string
     */
    protected $_staticClassName = '';

    /**
     * Overridden methods
     * @var array
     */
    protected $_callbacks = array();

    public function __construct()
    {
        if (empty($this->_staticClassName)) {
            throw new Exception('You must set $_staticClassName in your subclass.');
        }
    }
    
    /**
     * Set a callback to be used as a method
     * @param string $name
     * @param callback $callback 
     */
    public function __setMethodCallback($name, $callback)
    {
        $this->_callbacks[$name] = $callback;
    }

    public function __call($name, $args)
    {
        $func = isset($this->_callbacks[$name])
            ? $this->_callbacks[$name]
            : array($this->_staticClassName, $name);
        return call_user_func_array($func, $args);
    }
}
