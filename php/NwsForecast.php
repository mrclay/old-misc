<?php

/** 
 * Get NWS forecast data for a lat/long or zip code using webservice
 * 
 * <code>
 * NwsForecast::arrayFromZip(32609, array(
 *     NwsForecast::VAR_TEMP,
 *     NwsForecast::VAR_DEWPOINT,
 *     NwsForecast::VAR_RAIN_PROBABILITY
 * ));
 * 
 * // or just
 * NwsForecast::arrayFromZip(32609, 'temp dew pop12');
 * 
 * // condense/simplify the returned array if possible
 * NwsForecast::arrayFromZip(32609, 'temp dew pop12', NwsForecast::MODE_SIMPLIFY);
 * </code>
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class NwsForecast {
    
    const VAR_TEMP_APPARENT = 'appt';
    const VAR_TEMP = 'temp';
    const VAR_DEWPOINT = 'dew';
    const VAR_HUMIDITY = 'rh';
    const VAR_SNOW = 'snow';
    const VAR_RAIN = 'qpf';
    const VAR_RAIN_PROBABILITY = 'pop12';
    const VAR_TEMP_MIN = 'mint';
    const VAR_TEMP_MAX = 'maxt';
    const VAR_WIND = 'wspd';
    const VAR_WIND_GUST = 'wgust';
    const VAR_WIND_DIRECTION = 'wdir';
    const VAR_CLOUD_COVER = 'sky';
    const VAR_ICON = 'icon';
    
    const URL = 'http://www.weather.gov/forecasts/xml/SOAP_server/ndfdXMLclient.php';
    
    const MODE_NORMAL = 1;
    const MODE_SIMPLIFY = 2;
    
    public static function arrayFromZip($zip, $vars, $mode = self::MODE_NORMAL)
    {
        if (false === ($url = self::urlFromZip($zip, $vars))
            || false == ($xml = @file_get_contents($url))
        ) {
            return false;
        }
        return self::arrayFromXml($xml, $mode);
    }
    
    public static function arrayFromLatLon($lat, $lon, $vars, $mode = self::MODE_NORMAL)
    {
        if (false === ($url = self::urlFromLatLon($lat, $lon, $vars))
            || false == ($xml = @file_get_contents($url))
        ) {
            return false;
        }
        return self::arrayFromXml($xml, $mode);
    }
    
    public static function urlFromZip($zip, $vars)
    {
        if (! ($vars = self::_getValidVars($vars))
            || ! preg_match('/^\\d{5}$/', $zip)
        ) {
            return false;
        }
        return self::_getUrl(array("zipCodeList={$zip}"), $vars);
    }
    
    public static function urlFromLatLon($lat, $lon, $vars)
    {
        if (! ($vars = self::_getValidVars($vars))
            || ! is_numeric($lat)
            || ! is_numeric($lon)
            || $lat < -90
            || $lat > 90
            || $lon < -180
            || $lon > 180
        ) {
            return false;
        }
        return self::_getUrl(array("lat={$lat}", "lon={$lon}"), $vars);
    }
    
    public static function arrayFromXml($xml, $mode = self::MODE_NORMAL)
    {
        try {
            @$xml = new SimpleXMLElement($xml);
        } catch (Exception $e) {
            return false;
        }
        $times = array();
        $params = array();
        $startTime = null;

        if (! isset($xml->data[0]->{'time-layout'})) {
            return false;
        }

        // get array of times
        foreach ($xml->data[0]->{'time-layout'} as $tl) {
            $key = (string)$tl->{'layout-key'};
            $times[$key] = array();
            $i = 0;
            foreach ($tl->{'start-valid-time'} as $vt) {
                $times[$key][$i]['startTime'] = strtotime((string)$vt);
                if (0 == $i) {
    	            if (null == $startTime) {
    	            	$startTime = $times[$key][$i]['startTime'];
    	            } else {
    	            	$startTime = max($startTime, $times[$key][$i]['startTime']);
    	            }
                }
                $i++;
            }
            if ($tl->{'end-valid-time'}) {
                $i = 0;
                foreach ($tl->{'end-valid-time'} as $vt) {
                    $times[$key][$i]['endTime'] = strtotime((string)$vt);
                    $i++;
                }
            }
        }

        // add parameter data to times
        foreach ($xml->data[0]->parameters->children() as $el) {
            $timeLayout = (string)$el['time-layout'];
            $name = self::_getShortName((string)$el->name);
            $params[$name] = array(
                'timeLayout' => $timeLayout
                ,'units' => ('icon' === $name)
                    ? 'URL'
                    : (string)$el['units']
            );
            $i = 0;
            if ('icon' === $name) {
                foreach ($el->{'icon-link'} as $val) {
                    $times[$timeLayout][$i]['icon'] = (string)$val;
                    $i++;
                }
            } else {
                foreach ($el->value as $val) {
                    $val = (string)$val;
                    if (preg_match('/^0(?:\\.0+)$/', $val)) {
                        $val = 0;
                    } elseif (preg_match('/^(?:|null)$/i', $val)) {
                        $val = null;
                    } else {
                        $val = (false !== strpos((string)$val, '.'))
                            ? (float)$val
                            : (int)$val;
                    }
                    $times[$timeLayout][$i][$name] = $val;
                    $i++;
                }
            }
        }
        
        unset($xml);
        
        // make startTimes into keys
        foreach ($times as $timeLayout => $series) {
            $newSeries = array();
            foreach ($series as $row) {
                $startTime = $row['startTime'];
                unset($row['startTime']);
                $newSeries[$startTime] = $row;
            }
            $times[$timeLayout] = $newSeries;
        }

        if ($mode === self::MODE_SIMPLIFY && 1 === count($times)) {
            list($timeLayout) = each($times);
            $times = $times[$timeLayout];
            // remove endTimes
            foreach ($times as $time => $row) {
                unset($times[$time]['endTime']);
            }
            foreach ($params as $var => $row) {
                $varsUnits[$var] = $row['units'];
            }
            return array(
                'vars' => $varsUnits
                ,'times' => $times
            );
        } else {
            return array(
                'vars' => $params
                ,'times' => $times
                ,'startTime' => $startTime
            );
        }
    }
    
    private static $_defaultArgs = array('product=time-series');
    
    private static $_namesVars = array(
        '12 Hourly Probability of Precipitation' => 'pop12'
        ,'Apparent Temperature' => 'appt'
        ,'Cloud Cover Amount' => 'sky'
        ,'Conditions Icons' => 'icon'
        ,'Daily Maximum Temperature' => 'maxt'
        ,'Daily Minimum Temperature' => 'mint'
        ,'Dew Point Temperature' => 'dew'
        ,'Liquid Precipitation Amount' => 'qpf'
        ,'Relative Humidity' => 'rh'
        ,'Snow Amount' => 'snow'
        ,'Temperature' => 'temp'
        ,'Wind Direction' => 'wdir'
        ,'Wind Speed' => 'wspd'
        ,'Wind Speed Gust' => 'wgust'
    );
    
    private static function _getShortName($name)
    {
        return isset(self::$_namesVars[$name]) 
            ? self::$_namesVars[$name]
            : $name;
    }
    
    private static function _getValidVars($vars)
    {
        if (is_string($vars)) {
            $vars = preg_split('/[^a-z12]+/', $vars);
        }
        return array_values(array_intersect(self::$_namesVars, $vars));
    }
    
    private static function _getUrl($args, $vars)
    {
        while (list(, $var) = each($vars)) {
            $args[] = ('icon' === $var)
                ? 'icons=icons'
                : "{$var}={$var}";
        }
        return self::URL . '?' . implode('&', array_merge(self::$_defaultArgs, $args));
    }
}
