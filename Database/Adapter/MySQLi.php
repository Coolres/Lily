<?php

class Lily_Database_Adapter_MySQLi 
	extends Lily_Database_Adapter_Abstract
{
	
	/* (non-PHPdoc)
	 * @see Database_Abstract::connect()
	 */
	protected function connect() {
		$this->connection = mysqli_connect($this->host, $this->username, $this->password);
		if (false == $this->connection) {
			$details = "Could not connect to $user@$host " 
				. ($password ? "using password" : "not using password");
			Lily_Log::error($details);
			throw new Lily_Database_Exception("Could not connect to database. Consult error log for more details.");
		}
		
		mysqli_set_charset($this->connection, "utf8");
		if ($this->database) {
			$this->selectDatabase($this->database);
		}
		return $this->connection;
	}

	/* (non-PHPdoc)
	 * @see Database_Abstract::getFoundRows()
	 */
	public function getFoundRows() {
		$conn = $this->getConnection();
		if ($result2	= mysqli_query($conn, "SELECT FOUND_ROWS()")) {
			$found_rows	= mysqli_fetch_row($result2);
			return current($found_rows);
		}
		return 0;
	}

	/* (non-PHPdoc)
	 * @see Database_Abstract::getAffectedRows()
	 */
	public function getAffectedRows($conn=null) {
		$conn = $this->getWriteConnection();
		return $conn->affected_rows();
	}

	/* (non-PHPdoc)
	 * @see Database_Abstract::query()
	 */
	public function query($query) {
		$conn = $this->getConnection();
		$start = microtime(true);
		$result = $conn->query($query);
		$end = microtime(true);
		$diff = $end - $start;
		Lily_Log::debug("[$diff] " . $query, null, 'PROFILE', constant('PROFILE_LOG'));
		
		if ($result) {
			$results = array();
			while( $row = $result->fetch_assoc()) {
				$results[] = $row;
			}
			return $results;
		}
		return $result;
	}
	
	public function update($query) {
		// TODO
	}
	
	public function insert($query) {
		// TODO
	}
	/* (non-PHPdoc)
	 * @see Database_Abstract::selectDatabase()
	 */
	public function selectDatabase($database) {
		$conn = $this->getConnection();
		return $conn->select_db($database);
	}

	/* (non-PHPdoc)
	 * @see Database_Abstract::escapeString()
	 */
	public function escapeString($string) {
		$conn = $this->getConnection();
		return $conn->escape_string($string);
	}

	/* (non-PHPdoc)
	 * @see Database_Abstract::escapeInt()
	 */
	public function escapeInt($string) {
		$conn = $this->getConnection();
		return $conn->escape_string($string);
	}

	/* (non-PHPdoc)
	 * @see Database_Abstract::getErrorMessage()
	 */
	public function getErrorMessage() {
		$conn = $this->getConnection();
		return mysqli_error($conn);	
	}
	
	/* (non-PHPdoc)
	 * @see Database_Abstract::getErrorCode()
	 */
	public function getErrorCode() {
		$conn = $this->getConnection();
		return mysqli_errno($conn);
	}
}