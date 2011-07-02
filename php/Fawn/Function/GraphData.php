<?php

require_once 'Fawn/Function.php';

/**
 * Data for the graphing swf
 *
 * @author sclay
 * @copyright Copyright (c) 2007
 * @version $Id$
 * @access public
 */
class Fawn_Function_GraphData extends Fawn_Function {

    var $useDefaultCache = true;
    function setupDefaultCache()
    {
        $this->useCache(Fawn::getCache());
    }

    function valueShouldBeCached($value)
    {
        // dont cache if using a "custom" time
        return ($value !== '' && strpos($value, 'customTime=1&') !== 0);
    }

    function _computeReturn($args = array())
    {
        $stations = Fawn::getStations();

        // context for local standard time
        $localTz = Fawn::Factory(
            'MrClay_TZShift', $stations[$args['id']]['tz_offset']);

        if (isset($args['customTime'])) {
            $latestLocalTime = $args['customTime'];
            $data = $this->_getCustomTimeData($args['id'], $latestLocalTime); 
        } else {
            $latest = Fawn::factory('Fawn_Function_Latest')->GetReturn();
            $latestLocalTime = $latest['obs'][$args['id']]['time_local_standard'];
            unset($latest);
            $data = $this->_getDataMysql($args['id'], $latestLocalTime);
        }

        //Fawn::trace($data, __FILE__, __LINE__);

        if (!is_array($data)) {
            return '';
        }

        $latestTime = $localTz->strtotime($latestLocalTime);

        Fawn::loadClass('VBScript');
        $latestTime = VBScript::server_urlencode(date(
            'Y,n,j,G,i', $latestTime
        ));

        // calc wetbulb
        Fawn::loadClass('Fawn_Calc');

        $dataByTime = array();
        foreach ($data as $key => $row) {
            if ($row['temp2fts'] == 0
                && $row['temp6fts'] == 0
                && $row['temp30fts'] == 0
                && $row['soilTemp'] == 0
            ) {
                // data logger bug
                $row['temp2fts'] = 
                $row['temp6fts'] = 
                $row['temp30fts'] = 
                $row['soilTemp'] = '';
            }
            // calc wetbulb
            if (is_numeric($row['temp6fts']) 
                && is_numeric($row['dewPoint'])
            ) {
                $row['wetBulbTemp'] = round(
                    Fawn_Calc::wetBulb($row['temp6fts'], $row['dewPoint'])
                , 3); 
                if (false === $row['wetBulbTemp']) {
                    $row['wetBulbTemp'] = '';
                }
            } else {
                $row['wetBulbTemp'] = '';
            }
            // save row
            $dataByTime[$localTz->strtotime($row['UTC'])] = $row;
            $data[$key] = null;
        }

        //Fawn::trace($dataByTime, __FILE__, __LINE__);

        // fill in missing
        $t = $localTz->strtotime($latestLocalTime);
        $totalObs = 0;
        while ($totalObs <= 672) {
            if (!isset($dataByTime[$t])) {
                $dataByTime[$t] = array (
                    'UTC' => '',
                    'temp2fts' => '',
                    'temp6fts' => '',
                    'temp30fts' => '',
                    'soilTemp' => '',
                    'dewPoint' => '',
                    'windMax' => '',
                    'windAvg' => '',
                    'windMin' => '',
                    'relHumid' => '',
                    'rainFall' => '',
                    'wetBulbTemp' => '',
                );
            }

            $totalObs++;
            $t -= 900;
        }
        unset($data);
        krsort($dataByTime);

        Fawn::trace($dataByTime, __FILE__, __LINE__);

        foreach ($dataByTime as $time => $row) {
            unset($dataByTime[$time]['UTC']);
        }		

        /* as this is a large amount of data, we'll form encode it here instead
        of a view function so that it will all be cached */
        Fawn::loadClass('Fawn_Array');
        $fv = Fawn_Array::toFlashVars($dataByTime);

        return isset($args['customTime'])
            ? "customTime=1&{$fv}&latestTime={$latestTime}"
            : "{$fv}&latestTime={$latestTime}";
    }

    // turns off cache if customTime is given
    function getSwfInput($args)
    {
        if (isset($args['customTime'])) {
            $this->dontUseCache();
        }
        return $this->getReturn($args);
    }

    private function _getDataMysql($id, $latestLocalTime)
    {
        $db = $this->getDb('mysql');
        if (!$db->IsConnected()) {
            return false;
        }
        $sql = "
SELECT
    UTC
    ,ROUND(temp_air_60cm_C, 2) AS temp2fts
    ,ROUND(temp_air_2m_C, 2) AS temp6fts
    ,ROUND(temp_air_10m_C, 2) AS temp30fts
    ,ROUND(temp_soil_10cm_C, 2) AS soilTemp
    ,ROUND(temp_dp_2m_C, 2) AS dewPoint
    ,ROUND(wind_speed_max_10m_mph / 0.62137119, 2) AS windMax
    ,ROUND(wind_speed_10m_mph / 0.62137119, 2) AS windAvg
    ,ROUND(wind_speed_min_10m_mph / 0.62137119, 2) AS windMin
    ,rh_2m_pct AS relHumid
    ,rain_2m_inches * 2.54 AS rainFall
FROM wx
WHERE ID = " .(int)$id. "
  AND UTC > ('{$latestLocalTime}' - INTERVAL 169 HOUR)
ORDER BY UTC DESC
LIMIT 672
        ";
        return $db->GetArray($sql);
    }

    private function _getCustomTimeData($id, $latestLocalTime)
    {
        $db = $this->getDb('mysql');
        if (!$db->IsConnected()) {
            return false;
        }
        $sql = "
SELECT
    UTC
    ,ROUND(temp_air_60cm_C, 2) AS temp2fts
    ,ROUND(temp_air_2m_C, 2) AS temp6fts
    ,ROUND(temp_air_10m_C, 2) AS temp30fts
    ,ROUND(temp_soil_10cm_C, 2) AS soilTemp
    ,ROUND(temp_dp_2m_C, 2) AS dewPoint
    ,ROUND(wind_speed_max_10m_mph / 0.62137119, 2) AS windMax
    ,ROUND(wind_speed_10m_mph / 0.62137119, 2) AS windAvg
    ,ROUND(wind_speed_min_10m_mph / 0.62137119, 2) AS windMin
    ,rh_2m_pct AS relHumid
    ,rain_2m_inches * 2.54 AS rainFall
FROM wx
WHERE ID = " .(int)$id. "
  AND UTC > ('{$latestLocalTime}' - INTERVAL 169 HOUR)
  AND UTC <= '{$latestLocalTime}'
ORDER BY UTC DESC
LIMIT 672
        ";
        return $db->GetArray($sql);
    }
}

?>