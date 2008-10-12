<?php

/* 2008-10-18 : (sigh) Like every other jerk in 2006 I made my own MySQL class. Please use 
                Zend_DB or MDB2 or ADOdb or anything else.

USAGE

1) create .ini file:
host = my.mysql.server
username = myuser
password = mypass
db = mydb

2) PHP;
require_once 'MySQL.class.php';
$db = new MySQL('/path/to/ini');
$count = $db->value("SELECT COUNT(*) FROM myTable");

TODO: SQL_inserts() method

CHANGELOG:
2006-08-21 v 1.4
	Added $lastTime
2006-08-24 v 1.5
	Added $wasError, affected_rows().
	row() and numeric_array() with no results now return empty array.
2006-08-31 v 1.6
	Added column_arrays()
2006-09-08 v 1.7
	Added column_in_array()
2006-09-22 v 1.8
	Added assoc_array_join()
*/

class MySQL {
	var $version = '1.8';
	var $errors = array(); // array of error messages
	var $executedQueries = array(); // array of executed queries
	var $timeQueries = false; // add comment with time onto each query
	var $lastTime = -1; // elapsed time of last query
	var $wasError = false; // did last query cause an error?

	// private
	var $_dbLink = false;
	var $_triedConnect = false;
	var $_connectVars;

	// constructor
	function MySQL($iniPath = '', $host = false, $username = false, $password = false, $db = false) {
		if (!empty($iniPath)) {
			$ini = parse_ini_file($iniPath);
			if (!is_array($ini)) {
				return $this->_error('Could not process ini file.');
			}
			$this->_connectVars = $ini;
		} else {
			$this->_connectVars = array(
				'host' => $host
				,'username' => $username
				,'password' => $password
				,'db' => $db
			);
		}
	}

	// connect to DB
	// by default, waits until query is executed
	function connect($forceRetry = false) {
		if ($this->_dbLink !== false) {return true;} // connected

		if ($this->_triedConnect && !$forceRetry) {return false;} // sorry, already tried

		$this->_triedConnect = true;
		$this->_dbLink = mysql_connect(
			$this->_connectVars['host']
			,$this->_connectVars['username']
			,$this->_connectVars['password']
		);
		if (!$this->_dbLink) {
			return $this->_error('Could not connect to server.');
		}
		if (!mysql_select_db($this->_connectVars['db'], $this->_dbLink)) {
			return $this->_error('Could not select database.');
		}
		$this->_connectVars = array(); // for security
		return true;
	}


	// returns a safe string for use in SQL
	// Give it an array of strings, it returns an array of safe strings.
	// To avoid handling Magic Quote stripping, set $fromGPC = false.
	function safe_string($str, $trim = false, $fromGPC = true) {
		// handle arrays!
		if (is_array($str)) {
			$safeArray = array();
			foreach($str as $key => $string) {
				$safeArray[$key] = $this->safe_string($string, $trim, $fromGPC);
			}
			return $safeArray;
		}
		if ($fromGPC && get_magic_quotes_gpc()) {$str = stripslashes($str);}
		if ($trim) {$str = trim($str);}
		return ($this->_dbLink !== false)?
			mysql_real_escape_string($str, $this->_dbLink)
		  : mysql_escape_string($str);
	}

	// insert an array of fields=>values
	function insert_array($table, $fields2values, $fromGPC = true) {
		return $this->insert(
			$this->_insert_array_SQL($table, $fields2values, $fromGPC)
		);
	}
	// private method! (SQL performed by above)
	function _insert_array_SQL($table, $fields2values, $fromGPC = true) {
		$fields2values = $this->safe_string($fields2values, false, $fromGPC);
		$fields = array_keys($fields2values);
		$values = array_values($fields2values);
		return sprintf("INSERT INTO %s (`%s`) VALUES ('%s')"
			, $table, join('`,`', $fields), join("','", $values)
		);
	}

	// update a table with an array of fields=>values
	function update_array($table, $keyEqualsValue, $fields2values, $fromGPC = true) {
		return $this->run(
			$this->_update_array_SQL($table, $keyEqualsValue, $fields2values, $fromGPC)
		);
	}
	// private method! (SQL performed by above)
	function _update_array_SQL($table, $keyEqualsValue, $fields2values, $fromGPC = true) {
		$updates = array();
		list($keyColumn, $keyValue) = explode('=', $keyEqualsValue, 2);
		foreach ($fields2values as $col => $value) {
			$safeValue = $this->safe_string($value, false, $fromGPC);
			array_push($updates, "`{$col}` = '{$safeValue}'");
		}
		return "UPDATE `{$table}` SET\n\t".join("\n\t,",$updates)
			."\nWHERE `{$keyColumn}` = '{$keyValue}' LIMIT 1";
	}

	// returns 1 row array
	function row($SQL, $fetchType = MYSQL_BOTH) {
		$result = $this->result($SQL);
		if ($result === false) return false;
		if (0 == mysql_num_rows($result)) return array();
		return mysql_fetch_array($result, $fetchType);
	}


	// returns 1 value
	function value($SQL) {
		$row = $this->row($SQL, MYSQL_NUM);
		if (false === $row || empty($row)) return false;
		return $row[0];
	}


	// returns 1 row from table where keyColumn = keyValue
	function matching_row($table, $keyColumn, $keyValue, $fetchType = MYSQL_BOTH) {
		$result = $this->result(
			$this->_matching_row_SQL($table, $keyColumn, $keyValue, $fetchType)
		);
		if ($result===false) return false;
		if (mysql_num_rows($result)==0) {
			return $this->_error("No record in {$table} ({$keyColumn}='{$keyValue}')");
		}
		return mysql_fetch_array($result, $fetchType);
	}
	// private method! (SQL performed by above)
	function _matching_row_SQL($table, $keyColumn, $keyValue, $fetchType) {
		$keyValue = $this->safe_string($keyValue);
		return "SELECT * FROM `{$table}` WHERE `{$keyColumn}` = '{$keyValue}' LIMIT 1";
	}


	// returns numeric array with contents of single column query
	function numeric_array($SQL) {
		$result = $this->result($SQL);
		if ($result === false) return false;
		$array = array();
		while ($row = mysql_fetch_row($result)) {
			array_push($array, $row[0]);
		}
		return $array;
	}

	// returns a numeric array for each column
	function column_arrays($SQL) {
		$result = $this->result($SQL);
		if ($result === false) return false;
		$arrays = array();
		foreach (range(1, mysql_num_fields($result)) as $i) {
			array_push($arrays, array());
		}
		while ($row = mysql_fetch_row($result)) {
			foreach ($row as $key => $value) {
				array_push($arrays[$key], $value);
			}
		}
		return $arrays;
	}


	// returns associative array of all values
	// eg. $return[0]['id'] = id column of the first row in result
	function assoc_array($SQL) {
		$result = $this->result($SQL);
		if ($result === false) return false;
		if (0 == mysql_num_rows($result)) return array();
		$rows = array();
		while ($rows[] = mysql_fetch_assoc($result)) {}
		array_pop($rows);
		return $rows;
	}


	// returns associative array of all values specifying a column to be
	// used as the return array keys
	// e.g. $return['3']['name'] = name of user with id = 3
	function assoc_array_id($SQL, $idColumn) {
		$result = $this->result($SQL);
		if ($result === false) return false;
		if (mysql_num_rows($result)==0) {
			return array();
		}
		$returnArray = array();
		while ($row = mysql_fetch_assoc($result)) {
			$returnArray[$row[$idColumn]] = $row;
		}
		return $returnArray;
	}


	// executes a query
	function run($SQL) {
		return ($this->result($SQL) !== false);
	}


	// returns last_insert_id after successful insert or -1 for failed insert
	function insert($SQL) {
		if (!$this->run($SQL)) return -1;
		return (mysql_affected_rows($this->_dbLink)>0)?
			mysql_insert_id($this->_dbLink) : -1;
	}


	function affected_rows() {
		return mysql_affected_rows($this->_dbLink);
	}


	// returns a mysql result variable
	// NOTE: all queries are routed through this method
	function result($SQL) {
		if (!$this->connect()) return false;
		$SQL = trim($SQL);

		$this->wasError = false;

		if ($this->timeQueries) {
			$this->_time_query(true);
			$result = mysql_query($SQL, $this->_dbLink);
			$time = $this->_time_query();
			if (!empty($time)) {
				$SQL .= " # time(s)=".$time;
				$this->lastTime = $time;
			}
		} else {
			$result = mysql_query($SQL, $this->_dbLink);
		}

		if ($result === false) {
			$this->wasError = true;
			array_push($this->executedQueries, $SQL.' # error');
			return $this->_error(mysql_error($this->_dbLink));
		} else {
			array_push($this->executedQueries, $SQL);
			return $result;
		}
	}

	// for tables with "ORDER BY placement", this will allow shifting
	// a row up/down
	function placement_reorder(
		$tableName,
		$placementColName,
		$idColName,
		$idMoving,
		$moveDirection,  // -1 (up), 1 (down), -2 (top), 2 (bottom)
		$whereClause
	) {
		// get ordered even array (2,4,6...) of current ids in order
		$result = $this->result("SELECT {$idColName} FROM {$tableName}
			{$whereClause} ORDER BY {$placementColName} ");

		// if zero or 1 elements, we're done!
		if (mysql_num_rows($result) < 2) return true;

		// make even-indexed array in order starting at 4
		$currentIds = array();
		$iEven = 4;
		while($row = mysql_fetch_array($result)) {
			$currentIds[$iEven] = (int)$row[0];
			$iEven += 2;
		}

		// move and reorder
		$keyMoving = array_search($idMoving, $currentIds);
		// if idMoving wasn't one of the ids
		if ($keyMoving === false) return false;

		if (abs($moveDirection) == 2) { // top/bottom (untested!)
			$newKey = ($moveDirection > 0)? $iEven : 0;
		} else { // up/down
			$newKey = ($moveDirection > 0)? $keyMoving + 3 : $keyMoving - 3;
		}
		$currentIds[$newKey] = $currentIds[$keyMoving];
		unset($currentIds[$keyMoving]);
		ksort($currentIds);

		// set placements
		foreach($currentIds as $placement => $id) {
			$this->result("
				UPDATE {$tableName}
				SET {$placementColName} = {$placement}
				WHERE {$idColName} = {$id} LIMIT 1
			");
		}
		return true;
	}

	// use in WHERE clause
	function column_in_array($column, $array) {
		return "FIND_IN_SET({$column},'" . join(',', $array) ."')";
	}
	
	function assoc_array_join($assoc1, $assoc2, $usingCol, $isLeft = false) {
		if ($isLeft) {
			if (empty($assoc2)) {
				return $assoc1;
			}
			$nullRow = array();
			foreach (array_keys($assoc2[0]) as $col) {
				$nullRow[$col] = NULL;
			}
		}
		$assoc = array();
		foreach ($assoc1 as $row1) {
			$hasMatch = false;
			foreach ($assoc2 as $row2) {
				if ($row1[$usingCol] === $row2[$usingCol]) {
					$hasMatch = true;
					array_push($assoc, array_merge($row2, $row1));
				}
			}
			if ($isLeft && !$hasMatch) {
				array_push($assoc, array_merge($nullRow, $row1));
			}
		}
		return $assoc;
	}

	/******************************************
	 * "Private" functions
	 ******************************************/

	function _error($msg) {
		array_push($this->errors,$msg);
		return false;
	}

	function _time_query($reset = false) {
		if (!$this->timeQueries) return '';

		static $mt_previous = 0;

		list($usec, $sec) = explode(" ",microtime());
		$mt_current = (float)$usec + (float)$sec;

		if (!$mt_previous || $reset) {
			$mt_previous = $mt_current;
			return '';
		} else {
			$mt_diff = ($mt_current - $mt_previous);
			$mt_previous = 0;
			return sprintf('%.16f',$mt_diff);
		}
	}
}

?>