<?php

require_once 'Fawn/Function.php';

/**
 * Fawn_Function_DistanceBearing
 * 
 * This returns a list station IDs with their distances and bearings in order
 * of distance from the given ID.
 * 
 * @author sclay
 * @copyright Copyright (c) 2007
 * @version $Id$
 * @access public
 */
class Fawn_Function_DistanceBearing extends Fawn_Function {

	var $useDefaultCache = true;
	var $cacheGroup = '1yr';
	function setupDefaultCache()
	{
        $this->useCache(Fawn::getCache(array(
		    'lifeTime' => 3600 * 24 * 365 // 1 yr
		)));
	}
	
	function cacheIdFromArgs($args = array())
	{
		return "fawn_function_distancebearing_" . $args['id'];
	}

	function valueShouldBeCached($value)
    {
        return !empty($value);
    }

	function _computeReturn($args = array())
	{
		if (!isset($args['id'])) {
		    return array();
		}
        $id = (int)$args['id'];
        $db = $this->getDb('mysql');
		if (!$db->IsConnected()) {
			return array();
		}
		$data = $db->GetArray("
SELECT
    qq2.LocID AS id
    ,dist_mi AS miles
    ,CASE initBearingBoxed_deg
        WHEN 22.5  THEN 'NNE'  WHEN 45 THEN 'NE'
        WHEN 67.5  THEN 'ENE'  WHEN 90 THEN 'E'
        WHEN 112.5 THEN 'ESE'  WHEN 135 THEN 'SE'
        WHEN 157.5 THEN 'SSE'  WHEN 180 THEN 'S'
        WHEN 202.5 THEN 'SSW'  WHEN 225 THEN 'SW'
        WHEN 247.5 THEN 'WSW'  WHEN 270 THEN 'W'
        WHEN 292.5 THEN 'WNW'  WHEN 315 THEN 'NW'
        WHEN 337.5 THEN 'NNW'  ELSE 'N'
     END AS compass
    ,ROUND(initBearing_deg) AS bearing
FROM (
    SELECT 
        LocID
        ,ROUND((2 * 6378 * ASIN(d / 2 / 6378)) * 0.621371192) AS dist_mi
    FROM
        (SELECT
            SQRT(dx * dx + dy * dy + dz * dz) AS d
            ,LocID
         FROM
            (SELECT
                p1.x - p2.x AS dx
                ,p1.y - p2.y AS dy
                ,p1.z - p2.z AS dz
                ,p2.LocID
            FROM gpsGlb p1
            JOIN gpsGlb p2 ON (p1.LocID = {$id} AND p2.LocID != {$id})
           ) t1
        ) t2
    ) qq1
JOIN (
    SELECT
        LocID
        ,(360 + DEGREES(ATAN2(y, x))) % 360 AS initBearing_deg
        ,(360 + ROUND((DEGREES(ATAN2(y, x))) / 22.5) * 22.5) % 360 
         AS initBearingBoxed_deg
    FROM
        (SELECT
            SIN(RADIANS(s2.Longitude - s1.Longitude)) * COS(RADIANS(s2.Latitude)) 
             AS y
            ,COS(RADIANS(s1.Latitude)) * SIN(RADIANS(s2.Latitude))
                - SIN(RADIANS(s1.Latitude)) * COS(RADIANS(s2.Latitude))
                   * COS(RADIANS(s2.Longitude - s1.Longitude)) 
             AS x
            ,s2.LocID
        FROM station s1
        JOIN station s2 ON (s1.LocID = {$id} AND s2.LocID != {$id})
        ) q1
    ) qq2 ON (qq1.LocID = qq2.LocID)
ORDER BY dist_mi        
        ");
		return is_array($data)
            ? $data
            : array();
	}
	
	public function getWithin($id, $maxMiles = 0, $maxNum = 0, $active = true)
    {
        $data = $this->getReturn(array('id' => (int)$id));
        if (! $maxMiles && ! $maxNum) {
            return $data;
        }
        $tot = 0;
        $ret = array();
        if ($active) {
            $whiteList = array_keys(Fawn::getStations());
        }
        foreach ($data as $row) {
            if ($maxMiles && $row['miles'] > $maxMiles) {
                break;
            }
            if ($active) {
                if (in_array($row['id'], $whiteList)) {
                    $ret[] = $row;
                    ++$tot;
                }
            } else {
                $ret[] = $row;
                ++$tot;
            }
            if ($maxNum && $maxNum == $tot) {
                break;
            }
        }
        return $ret;
    } 
}

?>