<?php

require_once 'Fawn/Function.php';

/**
 * Fawn_Function_LatestMap
 * 
 * This supplies data only to latestmap_20071019.swf and later, replacing 
 * Fawn_Function_LatestData. Most client-side code was move moved into code
 * here.
 * 
 * Data is from Fawn_Function_Latest.
 * 
 * @author sclay
 * @copyright Copyright (c) 2007
 * @version $Id$
 * @access public
 *
 */

class Fawn_Function_LatestMap extends Fawn_Function {

    var $useDefaultCache = true;
    function setupDefaultCache()
    {
        $this->useCache(Fawn::getCache());
    }

    function valueShouldBeCached($value)
    {
        return ! empty($value);
    }

    private static function _formatTime($time, $tz_offset)
    {
        // 0 = abbr, 3 = TZ
        $tz = Fawn::getTimeZone((int)$tz_offset);
        $localTz = Fawn::factory('MrClay_TZShift', $tz[3]);
        return $localTz->date('n/j/Y g:i A ', $time) . $tz[0];
    }

    function _computeReturn($args = array())
    {
        $src = Fawn::factory('Fawn_Function_Latest')->getReturn();
        $est = Fawn::factory('MrClay_TZShift', -5);
        Fawn::loadClass('Conv');
        $data = array();
        foreach ($src['obs'] as $row) {
            $obTime = $est->strtotime($row['time_EST']);
            $data[] = array(
                'locations' => $row['display_name']
                ,'locIDs' => $row['ID']
                ,'dateTimes' => self::_formatTime($obTime, $row['tz_offset'])
                ,'isFresh' => ($row['minutes_old'] < 60 ? '1' : '') 
                ,'temps' => Fawn::round(Conv::c2f($row['temp_air_2m_C']))
                ,'xpos' => $row['xpos']
                ,'ypos' => $row['ypos']
                ,'soilTemps' => Fawn::round(Conv::c2f($row['temp_soil_10cm_C']))
                ,'rainFalls' => Fawn::round($row['rain_2m_inches'], 2)
                ,'relHums' => Fawn::round($row['rh_2m_pct'])
                ,'totalRads' => Fawn::round($row['rfd_2m_wm2'])
                ,'windSpeeds' => Fawn::round($row['wind_speed_10m_mph'])
                ,'windDirs' => Fawn::round($row['wind_direction_10m_deg'])
                ,'dewPoints' => Fawn::round(Conv::c2f($row['temp_dp_2m_C']))
            );
        }

        Fawn::loadClass('Fawn_Array');
        return "numLocations=" . count($data)
            . "&" . Fawn_Array::toFlashVars($data);
    }

    function getSwfInput()
    {
        $strDate = date('l F j, Y  g:i A ') . Fawn::registry('tz');
        $udtTime = date('g:i A', time() + (60 * 5));
        return $this->getReturn() 
            . "&fawnTime=" . urlencode($strDate)
            . "&udtTime=" . urlencode($udtTime);
    }
}

?>