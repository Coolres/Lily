<?php

class Lily_Database_Adapter_MySQLp 
	extends Lily_Database_Adapter_MySQL
{

	/* (non-PHPdoc)
	 * @see Database_Abstract::connect()
	 */
	protected function connect() {
		$this->connection = mysql_pconnect($this->host, $this->username, $this->password, MYSQL_CLIENT_COMPRESS);
		if (false == $this->connection) {
			$details = "Could not connect to $user@$host "
				. ($password ? "using password" : "not using password");
			Lily_Log::error($details);
			throw new Lily_Database_Exception("Could not connect to database. Consult error log for more details.");
		}

		mysql_set_charset("utf8",$this->connection);
		return $this->connection;
	}

}