<?php

/**
 * A simple autoloader implementation that just cycles through the include_path looking
 * for the code file based on PSR-0.
 *
 * @see http://groups.google.com/group/php-standards/web/psr-0-final-proposal
 *
 * Usage:
 * <code>
 * require 'path/to/MrClay/Loader.php';
 * MrClay_Loader::register();
 * </code>
 */
class MrClay_Loader {

    /**
     * Register MrClay_Loader::load as a class loader and prepend the directory containing "MrClay" to the include_path
     *
     * @param bool $autoPrepend prepend to the include_path the path two directories
     * above this file (the path containing "MrClay")
     */
    static function register($autoPrepend = true)
    {
        if ($autoPrepend) {
            self::prependIncludePath(dirname(dirname(__FILE__)));
        }
        spl_autoload_register(array('MrClay_Loader', 'load'));
    }

    /**
     * Make sure the given $path is at the front of PHP's include_path
     *
     * @param $path
     */
    static function prependIncludePath($path) {
        $path = rtrim($path, '/\\');
        $ps = PATH_SEPARATOR;
        $ip = $ps . get_include_path() . $ps;
        $ip = str_replace("$ps$path$ps", $ps, $ip); // remove if there
        $ip = "$ps$path$ip";
        set_include_path(substr($ip, 1, -1));
    }

    /**
     * Load a class/interface definition file (called by SPL autoload)
     *
     * @param string $className
     *
     * @return bool
     */
    static function load($className) {
        $className = ltrim($className, '\\');
        $path = str_replace(array('_', '\\'), DIRECTORY_SEPARATOR, $className) . '.php';
        foreach (explode(PATH_SEPARATOR, get_include_path()) as $includePath) {
            if (is_readable("$includePath/$path")) {
                require "$includePath/$path";
                return true;
            }
        }
        return false;
    }
}