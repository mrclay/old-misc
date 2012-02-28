<?php

/**
 * Eases handling dates/times in different timezones by allowing you to create
 * a timezone context. This can be helpful when you need to, e.g., parse date 
 * strings created in a different timezone.
 * 
 * Currently the class only provides strtotime() and date(), but this is usually
 * sufficient.
 * 
 * <code>
 * // parse a date string from Eastern Standard Time (no DST)
 * $tz = new MrClay_TimeZone(-5);
 * $time = $tz->strtotime('2007-06-01 08:00:00');
 * 
 * // display in New York time
 * $tz = new MrClay_TimeZone('America/New_York');
 * echo $tz->date('G', $time); // echoes '9'
 * </code>
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class MrClay_TimeZone {

    private $dtz = null;
    private $time = null;
    private $tz = null;

    /**
     * Create a "timezone shift" object.
     * 
     * @param $tz timezone. This can either be a string as given by
     * http://twiki.org/cgi-bin/xtra/tzdatepick.html or a GMT offset like -5.
     * -5 would be converted to the string "Etc/GMT+5" for you.
     * 
     * @param $time optional current timestamp in case you want to "set" time
     * 
     * @return null
     */
    public function __construct($tz, $time = null)
    {
        if (is_numeric($tz)) {
            $this->tz = (0 == $tz)
                ? 'Etc/GMT'
                : 'Etc/GMT' . ($tz > 0 ? '-' : '+') . abs($tz);
        } else {
            $this->tz = $tz;
        }
        $this->dtz = date_default_timezone_get();
        $this->time = ($time !== null)
            ? $time
            : (isset($_SERVER['REQUEST_TIME'])
                ? $_SERVER['REQUEST_TIME']
                : time()
            );
    }

    /**
     * Format a "local" time/date according to the timezone given in the
     * constructor
     * 
     * <code>
     * // display current New York time
     * $tz = new MrClay_TimeZone('America/New_York');
     * echo $tz->date('G:i:s');
     * </code>
     * 
     * @see http://www.php.net/manual/en/function.date.php   
     *
     */
    public function date($format, $time = null)
    {
        if (null === $time) {
            $time = $this->time;
        }
        $this->_shift();
        return $this->_unshift(date($format, $time));
    }

    /**
     * Parse about any English textual datetime description into a Unix 
     * timestamp. If given a date/time, it is parsed as if it is local to
     * the timezone given in the constructor.
     * 
     * <code>
     * // parse a date string from Eastern Standard Time (no DST)
     * $tz = new MrClay_TimeZone(-5);
     * $time = $tz->strtotime('2007-06-01 08:00:00');
     * </code>
     *  
     * @see http://www.php.net/manual/en/function.strtotime.php   
     */
    public function strtotime($strTime, $now = null)
    {
        $this->_shift();
        return $this->_unshift(strtotime($strTime, $now));
    }

    /**
     * Set the default timezone to the user's request.
     */
    private function _shift() {
        date_default_timezone_set($this->tz);
    }

    /**
     * Return the default timezone to the original value.
     * 
     * $val is passed through avoiding the need for a temp variable in the 
     * calling function.  
     */
    private function _unshift($val) {
        date_default_timezone_set($this->dtz);
        return $val;
    }
    
    /* TEST THESE
    public function getdate($time = null)
    {
        if (null === $time) {
            $time = $this->time;
        }
        $this->shift();
        return $this->_unshift(getdate($time));
    }

    public function localtime($time = null, $isAssoc = false)
    {
        if (null === $time) {
            $time = $this->time;
        }
        $this->shift();
        return $this->_unshift(localtime($time, $isAssoc));
    }

    // FIX
    public function mktime($h = null, $m = null, $s = null, $mm = null, $dd = null, $yy = null)
    {
        $this->shift();
        return $this->_unshift(mktime($h, $m, $s, $mm, $dd, $yy, idate('I')));
    }

    public function strftime($format, $time = null)
    {
        if (null === $time) {
            $time = $this->time;
        }
        $this->shift();
        return $this->_unshift(strftime($format, $time));
    }

    public function strptime($date, $format)
    {
        $this->shift();
        return $this->_unshift(strptime($date, $format));
    }

    */
}
