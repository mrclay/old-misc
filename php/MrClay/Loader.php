<?php

/**
 * A simple autoloader implementation that just cycles through the include_path looking
 * for the code file based on PSR-0.
 *
 * Side effect! Instantiating this class will prepend the parent of the "MrClay" directory to PHP's include_path.
 *
 * @link http://groups.google.com/group/php-standards/web/psr-0-final-proposal
 *
 * Usage:
 * <code>
 * require 'path/to/MrClay/Loader.php';
 * MrClay_Loader::getInstance()->register();
 * </code>
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class MrClay_Loader {

    /**
     * @return MrClay_Loader
     */
    static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new MrClay_Loader();
        }
        return $instance;
    }

    /**
     * Make sure the given $path is at the front of PHP's include_path (moves it there if already present)
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
     * @var bool
     */
    protected $loadAll = true;

    /**
     * @var bool
     */
    protected $isRegistered = false;

    protected function __construct()
    {
        self::prependIncludePath(dirname(__DIR__));
    }

    /**
     * Register $this->autoload as a class/interface loader
     *
     * @param bool $all try to find all classes, not just MrClay*
     */
    public function register($all = true)
    {
        $this->loadAll = $all;
        if (! $this->isRegistered) {
            spl_autoload_register(array($this, 'autoload'));
        }
    }

    /**
     * Load a class/interface definition file (called by SPL autoload)
     *
     * @param string $className
     *
     * @return bool
     */
    public function autoload($className) {
        $className = ltrim($className, '\\');
        if (0 !== strpos($className, 'MrClay') && ! $this->loadAll) {
            return false;
        }
        $pathToFile = str_replace(array('_', '\\'), DIRECTORY_SEPARATOR, $className) . '.php';
        foreach (explode(PATH_SEPARATOR, get_include_path()) as $includePath) {
            if (is_readable("$includePath/$pathToFile")) {
                require "$includePath/$pathToFile";
                return true;
            }
        }
        return false;
    }
}
