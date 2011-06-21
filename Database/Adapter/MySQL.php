<?php

class Lily_Database_Adapter_MySQL 
	extends Lily_Database_Adapter_Abstract
{


	/* (non-PHPdoc)
	 * @see Database_Abstract::connect()
	 */
	protected function connect() {
		$this->connection = mysql_connect($this->host, $this->username, $this->password);
		if (false == $this->connection) {
			$details = "Could not connect to $user@$host "
				. ($password ? "using password" : "not using password");
			Lily_Log::error($details);
			throw new Lily_Database_Exception("Could not connect to database. Consult error log for more details.");
		}

		mysql_set_charset("utf8", $this->connection);
		return $this->connection;
	}
	
	
	/* (non-PHPdoc)
	 * @see Database_Abstract::getFoundRows()
	 */
	public function getFoundRows() {
		$conn = $this->getConnection();
		if ($result2	= mysql_query("SELECT FOUND_ROWS()", $conn)) {
			$found_rows	= mysql_fetch_row($result2);
			return current($found_rows);
		}
		return 0;
	}

	/* (non-PHPdoc)
	 * @see Database_Abstract::getAffectedRows()
	 */
	public function getAffectedRows() {
		$conn = $this->getConnection();
		$result = mysql_affected_rows($conn);
		return $result;
	}

	/* (non-PHPdoc)
	 * @see Database_Abstract::query()
	 */
	public function query($query) {
		$conn = $this->getConnection();
		
		$results = null;
		$start = microtime(true);
		$result = mysql_query($query, $conn);
		$end = microtime(true);
		$diff = $end - $start;
		Lily_Log::debug("[{$diff}] " . $query, null, 'PROFILE', constant('PROFILE_LOG'));

		if ( $result === false ) {
			throw new Lily_Database_Exception(
				$this->getErrorMessage($conn),
				$this->getErrorCode($conn)
			);
		}
		if ( $result === true ) return $result;

		if ( $result ) {
			$results = array();
			while ($row = mysql_fetch_assoc($result)) {
				$results[] = $row;
			}
			return $results;
		}
		return $results;
	}

	public function update($query) {
		$conn = $this->getConnection();
		$start = microtime(true);
		$result = mysql_query($query, $conn);
		$end = microtime(true);
		$diff = $end - $start;
		Lily_Log::debug("[{$diff}] " . $query, null, 'PROFILE', constant('PROFILE_LOG'));

		if ($result === false) {
			throw new Lily_Database_Exception(
				$this->getErrorMessage($conn),
				$this->getErrorCode($conn)
			);
		}

		return mysql_affected_rows($conn);
	}

	public function insert($query) {
		$conn = $this->getConnection();
		$start = microtime(true);
		$result = mysql_query($query, $conn);
		$end = microtime(true);
		$diff = $end - $start;
		Lily_Log::debug("[{$diff}] " . $query, null, 'PROFILE', constant('PROFILE_LOG'));

		if ($result === false) {
			throw new Lily_Database_Exception(
				$this->getErrorMessage($conn),
				$this->getErrorCode($conn)
				);
		}

		return mysql_insert_id($connection);
	}

	/* (non-PHPdoc)
	 * @see Database_Abstract::selectDatabase()
	 */
	public function selectDatabase($database) {
		$conn = $this->getConnection();
		$result = mysql_select_db($database, $conn);
		if (!$result) {
			Lily_Log::error(mysql_error($conn));
		}
		return $result;
	}

	/* (non-PHPdoc)
	 * @see Database_Abstract::escapeString()
	 */
	public function escapeString($string) {
		return mysql_escape_string($string);
	}

	/* (non-PHPdoc)
	 * @see Database_Abstract::escapeInt()
	 */
	public function escapeInt($string) {
		return mysql_escape_string($string);
	}

	/* (non-PHPdoc)
	 * @see Database_Abstract::getErrorMessage()
	 */
	public function getErrorMessage() {
		$conn = $this->getConnection();
		return mysql_error($conn);
	}

	/* (non-PHPdoc)
	 * @see Database_Abstract::getErrorCode()
	 */
	public function getErrorCode() {
		$conn = $this->getConnection();
		return mysql_errno($conn);
	}

	public function buildInsertQuery($table, $rows)
	{
		if (empty($table)) throw new Lily_Database_Exception("table is empty");
		if (empty($rows)) throw new Lily_Database_Exception("data is empty");
		$conn = $this->getConnection();
		
		$columns = null;
		$sprintf = null;
		$data = array();
		foreach ($rows as $row) {
			ksort($row);
			if ($columns === null) {
				$columns = implode(',', array_keys($row));
			}
			if ($sprintf === null) {
				$sprintf = array();
				foreach ($row as $column => $value) {
					if (is_int($value)) {
						$sprintf[] = '%d';
					} else {
						$sprintf[] = '%s';
					}
				}
				$sprintf = '(' . implode(',', $sprintf) .')';
			}

			// Build escaped input
			$temp = array();
			$sprintf = array();
			foreach ($row as $column => $value) {
				if (is_int($value)) {
					$temp[] = $this->escapeInt($value);
					$sprintf[] = '%d';
				} else {
					$temp[] = $this->escapeString($value);
					$sprintf[] = "'%s'";
				}
			}
			$sprintf = '(' . implode(',', $sprintf) .')';
			array_unshift($temp, $sprintf);
			$data[] = call_user_func_array('sprintf', $temp);
		}

		$query = "INSERT IGNORE INTO {$table} ($columns) VALUES " . implode(',', $data);
		return $query;
	}
}