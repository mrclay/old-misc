<?php

/**
 * Unit conversions, particular those dealing with weather calculations
 * 
 * All functions return null if given a non-numeric value
 *
 * Note: By default, conversions between SI and U.S. customary units (e.g.
 * mi2km) are done using the international yard, defined to be 0.9144 m.
 * Setting the 2nd argument to true causes the function to use the
 * <a href="http://en.wikipedia.org/wiki/Mile#Statute_miles">U.S. Survey
 * standard</a> for a mile's length.
 * <code>
 * Conv::mi2km(1.0); // 1.609344
 * Conv::mi2km(1.0, true); // 1.609347219
 * </code>
 * @static
 * @link http://en.wikipedia.org/wiki/Conversion_of_units
 **/
class Conv {

    const NON_NUMERIC = null; 

	// Rankine to Fahrenheit
	function r2f($r) {
        if (! is_numeric($r)) {
            return self::NON_NUMERIC;
        }
		return $r + 459.69;
	}

	// Fahrenheit to Rankine
	function f2r($f) {
		if (! is_numeric($f)) {
            return self::NON_NUMERIC;
        }
        return $f - 459.69;
	}

	// Fahrenheit to Celcius
	function f2c($f)
	{
		if (! is_numeric($f)) {
            return self::NON_NUMERIC;
        }
        return ($f - 32) / 1.8;
	}

	// Celcius to Fahrenheit
	function c2f($c)
	{
		if (! is_numeric($c)) {
            return self::NON_NUMERIC;
        }
        return (1.8 * $c) + 32;
	}

	// Celcuis to Kelvin
	function c2k($c) {
		if (! is_numeric($c)) {
            return self::NON_NUMERIC;
        }
        return $c + 273.15;
	}

	// Kelvin to Celcius
	function k2c($k) {
		if (! is_numeric($k)) {
            return self::NON_NUMERIC;
        }
        return $k - 273.15;
	}

	// Fahrenheit to Kelvin
	function f2k($f) {
		if (! is_numeric($f)) {
            return self::NON_NUMERIC;
        }
        return (($f - 32) / 1.8) + 273.15;
	}

	// Kelvin to Fahrenheit
	function k2f($k) {
		if (! is_numeric($k)) {
            return self::NON_NUMERIC;
        }
        return ($k - 273.15) * 1.8 + 32;
	}

	// miles to km
	function mi2km($mi, $USSurvey = false)
	{
		if (! is_numeric($mi)) {
            return self::NON_NUMERIC;
        }
        return $mi * ($USSurvey ? 1.609347219 : 1.609344);
	}

	// km to miles
	function km2mi($km, $USSurvey = false)
	{
		if (! is_numeric($km)) {
            return self::NON_NUMERIC;
        }
        return $km / ($USSurvey ? 1.609347219 : 1.609344);
	}

	// inches to centimeters
	function in2cm($in, $USSurvey = false)
	{
		if (! is_numeric($in)) {
            return self::NON_NUMERIC;
        }
        return $in * ($USSurvey ? 2.540005 : 2.54);
	}

	// centimeters to inches
	function cm2in($cm, $USSurvey = false)
	{
		if (! is_numeric($cm)) {
            return self::NON_NUMERIC;
        }
        return $cm / ($USSurvey ? 2.540005 : 2.54);
	}

	// inches to millimeters
	function in2mm($in, $USSurvey = false)
	{
		if (! is_numeric($in)) {
            return self::NON_NUMERIC;
        }
        return $in * ($USSurvey ? 25.40005 : 25.4);
	}

    // millimeters to inches
	function mm2in($mm, $USSurvey = false)
	{
		if (! is_numeric($mm)) {
            return self::NON_NUMERIC;
        }
        return $mm / ($USSurvey ? 25.40005 : 25.4);
	}

	// feet to meters
	function ft2m($ft, $USSurvey = false) {
		if (! is_numeric($ft)) {
            return self::NON_NUMERIC;
        }
        return $ft * ($USSurvey ? 0.30480061 : 0.3048);
	}

	// meters to feet
	function m2ft($m, $USSurvey = false) {
		if (! is_numeric($m)) {
            return self::NON_NUMERIC;
        }
        return $m / ($USSurvey ? 0.30480061 : 0.3048);
	}

	// meters/sec to km/hour
	function mps2kmph($mps)
	{
		if (! is_numeric($mps)) {
            return self::NON_NUMERIC;
        }
        return $mps * 3.6;
	}

	// km/hour to meters/sec
	function kmph2mps($kph)
	{
		if (! is_numeric($kph)) {
            return self::NON_NUMERIC;
        }
        return $kph / 3.6;
	}

    // km/hour to miles/hour
	function kmph2mph($kmhp, $USSurvey = false) {
		if (! is_numeric($kmhp)) {
            return self::NON_NUMERIC;
        }
        return $kmhp / ($USSurvey ? 1.609347219 : 1.609344);
	}

    // miles/hour to km/hour
	function mph2kmph($mph, $USSurvey = false) {
		if (! is_numeric($mph)) {
            return self::NON_NUMERIC;
        }
        return $mph * ($USSurvey ? 1.609347219 : 1.609344);
	}
	
	// miles/hour to meters/sec
	function mph2mps($mph) {
        if (! is_numeric($mph)) {
            return self::NON_NUMERIC;
        }
        return $mph * 0.44704;
	}
	
	// knots to miles/hour
	function knots2mph($knots) {
        if (! is_numeric($knots)) {
            return self::NON_NUMERIC;
        }
        return $knots * 1.15077945;
    }
}
