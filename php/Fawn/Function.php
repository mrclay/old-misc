<?php

/**
 * PHP4 abstract class for creating functions whose output is automatically cached to 
 * disk. Created classes can influence their own cacheId and decide what kind of output
 * should not be cached. A stopwatch object can be injected to capture program flow and
 * timing info.
 */
class Fawn_Function {

	/*
	 * To create a default caching mechanism in the object,
	 * set this to true and override the setupDefaultCache()
	 * method to call $this->useCache($yourCache)
	 **/
	var $useDefaultCache = false; // set to true and override
	var $cacheGroup = 'default';

    /**
	 * Was the last return value of getResult() fetched from cache?  
	 */	
	var $usedCache = null;
	
	/**
	 * If the last call to getResult() involved a cache, this was the id  
	 */
	var $cacheId = null;

	// private
	var $_cache = false; // Cache_Lite object
	var $_stopwatch = false; // Stopwatch object (unimplemented)
	var $_db = false; // DB object

	/* private abstract
	 * Each subclass must override this method with calculation
	 * of your return value. For profiling, you may call $this->markTime()
	 * after large operations.
	 **/
	function _computeReturn($args = array())
	{
		// make sure to validate incoming arguments!
		return array('life', 42);
	}

	/**
	 * This function shold create a unique id for each set of argument that
	 * produces a different data set. If some args don't affect output, you
	 * may want to change this to enable more caching.
	 **/
	function cacheIdFromArgs($args = array())
	{
		return strtolower(get_class($this)) . '_' . md5(serialize($args));
	}

	/**
	 * If $this->useDefaultCache
	 * This method is called at the beginning of getReturn() UNLESS
	 * the methods dontUseCache() or useCache() have been called.
	 * This allows a unique caching mechanism per instance.
	 **/
	function setupDefaultCache()
	{
		//$this->useCache($someCache);
	}

    /**
     * The value will be cached only if this method returns true. This will help
     * avoid caching error flags as legitimate data. If your _computeReturn
     * returns a different "error" flag than null, or just want to cache
     * everything, override this function.
     */
    function valueShouldBeCached($value)
    {
        return ! is_null($value);
    }

	// final
	function useCache(&$obj)
	{
		$this->_cache =& $obj;
		$this->useDefaultCache = false;
	}
	// final
	function dontUseCache()
	{
		$this->_cache = false;
		$this->useDefaultCache = false;
	}

	// final
	function useStopwatch(&$obj)
	{
		$this->_stopwatch =& $obj;
	}

	// final
	function markTime($key = '')
	{
		if ($this->_stopwatch) {
			$this->_stopwatch->mark($key);
		}
	}

	// final
	function getTimes()
	{
		if ($this->_stopwatch) {
			return $this->_stopwatch->events;
		}
	}

	// raw data return
	// final
	function getReturn($args = array())
	{
		$this->usedCache = false;
        if ($this->useDefaultCache) {
			$this->setupDefaultCache();
		}
		if ($this->_stopwatch) {
			$this->_stopwatch->start();
		}
		if ($this->_cache === false) {
			// no cache
			$this->cacheId = null;
			$data = $this->_computeReturn($args);
			$this->markTime('computedReturn');
			return $data;
		}
		// try cache
		$this->cacheId = $this->cacheIdFromArgs($args);
		if ($data = $this->_cache->get($this->cacheId, $this->cacheGroup)) {
            $this->usedCache = true;
			$data = unserialize($data);
			$this->markTime('fetchedCache');
		} else {
			$this->markTime('cacheWasEmpty');
			// No valid cache found
			$data = $this->_computeReturn($args);
			$this->markTime('computedReturn');
			if ($this->valueShouldBeCached($data)) {
    			$this->_cache->save(serialize($data), $this->cacheId, $this->cacheGroup);
    			$this->markTime('storedCache');
			}
		}
		return $data;
	}

	// base String view
	function getString($args = array())
	{
		return var_export($this->getReturn($args), 1);
	}

	// base HTML view
	function getHtml($args = array())
	{
		return "<pre>" .htmlentities(var_export($this->getReturn($args), 1)). "</pre>";
	}

	// final (local DB ref allows cleaner unit testing)
	function &getDb($type = '') {
        if (!$type) {
            $type = FAWN_ADODB_DEFAULT_TYPE;
        }
		if (!$this->_db) {
			$this->_db =& Fawn::getDb($type);
		}
		return $this->_db;
	}

	// final
	function setDb(&$db){
		$this->_db =& $db;
	}
}

?>