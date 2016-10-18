<?php
	/**
  * PDO wrapper. Provides basic PDO operations.
  *
  * Visible in every context od application.
  *
  * @author		Oleg Zorin <zorinoa@yandex.ru>
	* @link			http://oleg.zorin.ru homepage
	*
	* @license https://opensource.org/licenses/GPL-3.0 GNU Public License, version 3
	*
	* @package	OZ\
  * @version	1.0
	*/
	namespace OZ;
	
	abstract class DB {
		/** @var object $DB				Instance of PDO */
		static $DB = null;
		/** @var boolean $_debug	Debug flag */
		private static $_debug = true;
		
		/**
		* Initialization method. Create instance of DB.
		*
		* @param string $drvr		DB driver.
		* @param string $host		DB server.
		* @param string $name		DB name.
		* @param string $user		DB user.
		* @param string $pass		DB user password.
		*/
		static function init($drvr, $host, $name, $user, $pass) {
			try {
				switch($drvr) {
					case 'mysql': self::$DB = new \PDO('mysql:host=' . $host . ';dbname=' . $name, $user, $pass); break;
					case 'mssql': self::$DB = new \PDO('mssql:host=' . $host . ';dbname=' . $name, $user, $pass); break;
					case 'sybase': self::$DB = new \PDO('sybase:host=' . $host . ';dbname=' . $name, $user, $pass); break;
					default: self::$DB = new \PDO('mysql:host=' . $host . ';dbname=' . $name, $user, $pass); break;
				}
				if(self::$_debug) {
					self::$DB->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); 
				}
				/* You can send some params to DB server here */
				//self::query('SET NAMES utf8');
				//self::query('SET CHARACTER SET utf8');
				//self::query('SET COLLATION_CONNECTION="utf8_general_ci"');
			}  
			catch(\PDOException $e) {
				if(self::$_debug) {
					$trace = debug_backtrace();
					$message = $e->getMessage() . '<br />';
					$message .= 'File: ' . $trace[0]['file'] . '<br />';
					$message .= 'Line: ' . $trace[0]['line'] . '<br />';
					die($message);
				}
			}
		}
		
		/**
		* Executes an SQL statement.
		*
		* @param string $query				SQL request.
		*
		* @return resource Executes an SQL statement, returning a result set as a PDOStatement object.
		*/
		static function query($query) {
			$result = false;
			if(!is_null(self::$DB)) {
				try {
					$result = self::$DB->query($query);
					try {
						$result->setFetchMode(\PDO::FETCH_ASSOC);
					}
					catch(\PDOException $e) {
						if(self::$_debug) {
							$trace = debug_backtrace();
							$message = $e->getMessage() . '<br />';
							$message .= 'Query: ' . $query . '<br />';
							$message .= 'File: ' . $trace[0]['file'] . '<br />';
							$message .= 'Line: ' . $trace[0]['line'] . '<br />';
							$message .= 'File: ' . $trace[1]['file'] . '<br />';
							$message .= 'Line: ' . $trace[1]['line'] . '<br />';
							die($message);
						}
					}
				}
				catch(\PDOException $e) {
					if(self::$_debug) {
						$trace = debug_backtrace();
						$message = $e->getMessage() . '<br />';
						$message .= 'Query: ' . $query . '<br />';
						$message .= 'File: ' . $trace[0]['file'] . '<br />';
						$message .= 'Line: ' . $trace[0]['line'] . '<br />';
						$message .= 'File: ' . $trace[1]['file'] . '<br />';
						$message .= 'Line: ' . $trace[1]['line'] . '<br />';
						die($message);
					}
				}
			}
			return $result;
		}

		/**
		* Prepares an SQL statement to be executed by the DB::execute() method.
		*
		* @param string $query				SQL request.
		*
		* @return resource Prepares a statement for execution and returns a PDOStatement object.
		*/
		static function prepare($query) {
			$result = false;
			if(!is_null(self::$DB)) {
				try {
					$result = self::$DB->prepare($query);
					try {
						$result->setFetchMode(\PDO::FETCH_ASSOC);
					}
					catch(\PDOException $e) {
						if(self::$_debug) {
							$trace = debug_backtrace();
							$message = $e->getMessage() . '<br />';
							$message .= 'Query: ' . $query . '<br />';
							$message .= 'File: ' . $trace[0]['file'] . '<br />';
							$message .= 'Line: ' . $trace[0]['line'] . '<br />';
							$message .= 'File: ' . $trace[1]['file'] . '<br />';
							$message .= 'Line: ' . $trace[1]['line'] . '<br />';
							die($message);
						}
					}
				}
				catch(\PDOException $e) {
					if(self::$_debug) {
						$trace = debug_backtrace();
						$message = $e->getMessage() . '<br />';
						$message .= 'Query: ' . $query . '<br />';
						$message .= 'File: ' . $trace[0]['file'] . '<br />';
						$message .= 'Line: ' . $trace[0]['line'] . '<br />';
						$message .= 'File: ' . $trace[1]['file'] . '<br />';
						$message .= 'Line: ' . $trace[1]['line'] . '<br />';
						die($message);
					}
				}
			}
			return $result;
		}
		
		
		/**
		* Execute the prepared statement by DB::prepare().
		*
		* @param resource $res				PDOStatement object.
		* @param array $placeholders	Array of placeholders (placeholder => value).
		*
		* @return boolean Returns true on success or false on failure.
		*/
		static function execute($res, $placeholders = array()) {
			$result = false;
			if(is_object($res)) {
				try {
					$result = $res->execute($placeholders);
				}
				catch(\PDOException $e) {
					if(self::$_debug) {
						$trace = debug_backtrace();
						$message = $e->getMessage() . '<br />';
						$message .= 'Res: ' . $res->queryString . '<br />';
						$message .= 'File: ' . $trace[0]['file'] . '<br />';
						$message .= 'Line: ' . $trace[0]['line'] . '<br />';
						$message .= 'File: ' . $trace[1]['file'] . '<br />';
						$message .= 'Line: ' . $trace[1]['line'] . '<br />';
						die($message);
					}
				}
			}
			return $result;
		}
		
		/**
		* Fetches the next row from a result set.
		*
		* @param resource $res				PDOStatement object.
		*
		* @return array|false Returns DB record as associated array.
		*/
		static function fetch($res) {
			$result = false;
			if(is_object($res)) {
				try {
					$result = $res->fetch();
				}
				catch(\PDOException $e) {
					if(self::$_debug) {
						$trace = debug_backtrace();
						$message = $e->getMessage() . '<br />';
						$message .= 'File: ' . $trace[0]['file'] . '<br />';
						$message .= 'Line: ' . $trace[0]['line'] . '<br />';
						$message .= 'File: ' . $trace[1]['file'] . '<br />';
						$message .= 'Line: ' . $trace[1]['line'] . '<br />';
						die($message);
					}
				}
			}
			return $result;
		}
		
		/**
		* Returns the ID of the last inserted row or sequence value.
		*
		* @return string Returns ID of the last inserted row or sequence value.
		*/
		static function id() {
			$result = false;
			if(!is_null(self::$DB)) {
				try {
					$result = self::$DB->lastInsertId();
				}
				catch(\PDOException $e) {
					if(self::$_debug) {
						$trace = debug_backtrace();
						$message = $e->getMessage() . '<br />';
						$message .= 'File: ' . $trace[0]['file'] . '<br />';
						$message .= 'Line: ' . $trace[0]['line'] . '<br />';
						$message .= 'File: ' . $trace[1]['file'] . '<br />';
						$message .= 'Line: ' . $trace[1]['line'] . '<br />';
						die($message);
					}
				}
			}
			return $result;
		}
		
		/**
		* Returns the number of rows affected by the last SQL statement.
		*
		* @param resource $res				PDOStatement object.
		*
		* @return int Returns the number of rows.
		*/
		static function rows($res) {
			$result = false;
			if(is_object($res)) {
				try {
					$result = $res->rowCount();
				}
				catch(\PDOException $e) {
					if(self::$_debug) {
						$trace = debug_backtrace();
						$message = $e->getMessage() . '<br />';
						$message .= 'File: ' . $trace[0]['file'] . '<br />';
						$message .= 'Line: ' . $trace[0]['line'] . '<br />';
						$message .= 'File: ' . $trace[1]['file'] . '<br />';
						$message .= 'Line: ' . $trace[1]['line'] . '<br />';
						die($message);
					}
				}
			}
			return $result;
		}
		
		 
		/**
		* Quotes a string for use in a query.
		*
		* @param string $string				The string to be quoted.
		*
		* @return string Returns a quoted string that is theoretically safe to pass into an SQL statement.
		*/
		static function escape($string) {
			if(!is_null(self::$DB)) {
				if(is_array($string)) {
					foreach($string as $k => $v) {
						$string[$k] = self::escape($v);
					}
				}
				else {
					try {
						$string = self::$DB->quote($string);
					}
					catch(\PDOException $e) {
						if(self::$_debug) {
							$trace = debug_backtrace();
							$message = $e->getMessage() . '<br />';
							$message .= 'File: ' . $trace[0]['file'] . '<br />';
							$message .= 'Line: ' . $trace[0]['line'] . '<br />';
							$message .= 'File: ' . $trace[1]['file'] . '<br />';
							$message .= 'Line: ' . $trace[1]['line'] . '<br />';
							die($message);
						}
					}
				}
			}
			return $string;
		}
	}