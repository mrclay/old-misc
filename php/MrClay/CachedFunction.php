<?php

/**
 * A wrapper for caching the return values of slow functions. The class will do
 * all it can to return valid return values from cache and refresh them only
 * when stale.
 *
 * A cache lock is used so (ideally) only one PHP process calls a particular 
 * function/argument at a time. Other processes return stale values until the
 * cache is updated, or, in the worse case, sleep until the lock is removed or
 * expires.
 *
 * Function calls may also be queued to run after output is sent to the browser,
 * so no clients have to wait for the return.
 * 
 */
class MrClay_CachedFunction {
    private $_callback = null;
    private $_validator = null;
    private $_id = null;
    
    /**
     * If the cached value is older than this, getReturn() will either re-call the
     * function or sleep until another process updates the cache. To prevent 
     * processes from piling up while waiting for a value, it's best
     * to set this well above $cacheStaleAge.
     * 
     * Under certain conditions, however, you may find it preferable to have 
     * several sleeping connections rather than serve stale values
     * than give out old   
     * @var int
     */
    public $cacheUnusableAge = 3640;
    
    /**
     * If the cache value is older than this, it may still be returned, but the
     * first process to reach this will call the function to replace it.   
     * @var int
     */
    public $cacheStaleAge = 3600;
    
    /**
     * After this many seconds a lock is considered abandoned (storage of the 
     * result may have failed). Similarly, if getReturn() is waiting for a value from
     * another thread, when this number of seconds is reached, getReturn() will bail 
     * and return whatever's in the cache.
     * @var int
     */
    public $maxWait = 30;
    
    /**
     * How long in ms to sleep between checks for a change in a lock (removal
     * or time change) 
     * @var int
     */
    public $sleepMs = 500;
    
    /**
     * If true, getReturn() will return a stale value but queue the function call
     * for later in the script life. This may allow you to flush output to
     * the browser earlier and even close the connection before calling the
     * function.
     * 
     * If true, you *must* call runQueue() after all getReturn() calls to call and
     * cache the new return values.
     * 
     * @var bool
     * @see runQueue()
     */
    public $queueCalls = false;
    
    /**
     * @param object $cache object with the API of MrClay_CachedFunction_Cache_File
     * if a string is given, a MrClay_CachedFunction_Cache_File object will be
     * created with $cache as the directory.
     * 
     * @param callback $callback function to call
     * 
     * @param callback $validator (optional) function to validate return value.
     * If given, the return value is passed to the validator, and the value
     * is only cached if it returns true. The validator is also passed the 
     * callback, args, and id in case your validator would like to log errors.
     * 
     * @return null
     */
    public function __construct($cache, $callback, $validator = null)
    {
        if (is_string($cache)) {
            $cache = new MrClay_CachedFunction_Cache_File($cache);
        }
        $this->_cache = $cache;
        $this->_callback = $callback;
        $this->_validator = $validator;
    }
    
    /**
     * Get an associative array wrapping the return value of a function call. 
     * 
     * The array will at least have these keys:
     * * 'id' (string) the id under which this call is stored
     * * 'isUsable' (bool) is the value considered usable
     * * 'isFresh' (bool) is the value considered fresh
     * 
     * These keys will exist only when a value is returned:
     * * 'value' (mixed) the return value
     * * 'elapsed' (float) function execution time (s)
     * 
     * This will exist only if the value was pulled from cache:
     * * 'cacheTime' (float) timestamp that the value was stored
     * 
     * This will exist only if getReturn() was forced to wait for another process to
     * compute the value:
     * * 'wait' (float) number of seconds (float) waited
     * 
     * @param array $args (optional) arguments passed to callback function
     * 
     * @param string $id (optional) if provided, this will be used as the cache 
     * id for this call. If using the file cache, it should contain only valid 
     * filename characters. By default, the id is created from digesting the
     * serialization of the callback and arguments.
     * 
     * @return array
     */
    public function getReturn($args = array(), $id = null)
    {
        if (! is_string($id)) {
            $id = md5(serialize(array($this->_callback, $args)));
        }
        $lockId = $id . '_computing';
        
        $cache = $this->_fetchReturn($id);
        if ($cache['isFresh']) {
            return $cache;
        }

        // cache is stale
        
        // check for lock        
        $lock = $this->_fetchLock($lockId);
        
        if ($lock && $lock['isFresh']) {
            // another process working
            if ($cache['isUsable']) {
                return $cache;
            } else {
                // must wait for lock change/removal
                $start = microtime(true);
                do {
                    usleep($this->sleepMs * 1000);
                    $this->_cache->reset();
                    $newLock = $this->_fetchLock($lockId);
                    $wait = (microtime(true) - $start);
                    $lockChanged = (! $newLock || ($newLock['time'] != $lock['time']));
                } while (! $lockChanged && $wait < $this->maxWait);
                // send whatever is cached
                $cache = $this->_fetchReturn($id);
                $cache['wait'] = $wait;
                return $cache;
            }
        } else {
            // no lock or lock is too old, need to execute
            if ($this->queueCalls) {
                // we should try to queue this call for later
                if ($cache['isUsable']) {
                    // send stale cache and queue call for later
                    $this->_queue[] = array(
                        'id' => $id
                        ,'args' => $args
                    );
                    return $cache;
                }
            }
            // call could not be queued
            $ret = $this->_defaultReturn($id);
            $try = $this->executeAndStore($args, $id);
            if ($try['isValid']) {
                $ret['value'] = $try['value'];
                $ret['elapsed'] = $try['elapsed'];
                $ret['isUsable'] = true;
                $ret['isFresh'] = true;
            }
            return $ret;
        }
    }
    
    /**
     * Call and and cache the return of functions enqueued by the $queueCalls
     * option.
     * 
     * Generally this should be done after setting the Content-Length header,
     * echoing output and flushing buffers. This way the browser can close the
     * connection early while the function calls continue. E.g.:
     * <code>
     * header('Content-Length: ' . strlen($out));
     * header('Connection: close');
     * echo $out;
     * ob_end_flush(); flush();
     * // browser "completes" request here
     * $func->runQueue();
     * <code>
     * 
     * @return null
     */
    public function runQueue()
    {
        foreach ($this->_queue as $call) {
            $this->executeAndStore($call['args'], $call['id']);
        }
        $this->_queue = array();
    }
    
    /**
     * Call the callback function, call the validator function (if set), and 
     * cache the result if valid. You may want to use this in a cron script to
     * update your caches.
     *
     * @param array $args (optional) arguments passed to callback function
     * 
     * @param string $id (optional) cache id. (see getReturn())
     * 
     * @return array
     */
    public function executeAndStore($args = array(), $id = null)
    {
        ignore_user_abort(true);
        if (! is_string($id)) {
            $id = md5(serialize(array($this->_callback, $args)));
        }
        $lockId = $id . '_computing';
        $this->_cache->set($lockId, microtime(true));
        $before = microtime(true);
        $value = call_user_func_array($this->_callback, $args);
        $after = microtime(true);
        $isValid = $this->_validator
            ? (bool)call_user_func($this->_validator, $value, $this->_callback, $args, $id)
            : true;
        if ($isValid) {
            $this->_set($id, $value, ($after - $before), $after);
        }
        $this->_cache->delete($lockId);
        return array(
            'value' => $value
            ,'isValid' => $isValid
            ,'elapsed' => ($after - $before)
        );
    }
        
    
    protected $_queue = array();
    protected $_cache = null;
    
    protected function _defaultReturn($id) {
        return array(
            'id' => $id
            ,'isUsable' => false
            ,'isFresh' => false
        );
    }
    
    protected function _fetchReturn($id) {
        $ret = $this->_defaultReturn($id);
        $get = $this->_cache->get($id);
        if ($get) {
            $age = microtime(true) - $get[2];
            $ret['value'] = $get[0];
            $ret['elapsed'] = $get[1];
            $ret['cacheTime'] = $get[2];
            $ret['isFresh'] = $age < $this->cacheStaleAge;
            $ret['isUsable'] = $age < $this->cacheUnusableAge;
        }
        return $ret;
    }
    
    protected function _fetchLock($id)
    {
        $get = $this->_cache->get($id);
        return is_numeric($get)
            ? array(
                'time' => $get
                ,'isFresh' => (microtime(true) - $get) < $this->maxWait  
            )
            : false;
    }
    
    protected function _set($id, $value, $elapsed, $time)
    {
        $this->_cache->set($id, array($value, $elapsed, $time));
    }
}
