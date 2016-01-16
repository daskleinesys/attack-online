<?php

namespace AttOn\Model\DataBase;

class DataSource {
	// Static Singleton Instance
	private static $singleton_instance = NULL;

	// Log4PhP
	private $logger = NULL;

	// PDO class
	private $dbh;

	// predefined SQL queries
	private $stmts_predefined = array();
	private $stmts_predefined_queries = array();
	
	// info which queries are game specific
	private $game_specific_queries = array();

	/**
	 * return singleton_instance, create it first if it hasn't been yet
	 * Singleton() == getInstance()
	 * @return DataSource
	 */
	public static function Singleton() {
		if (self::$singleton_instance == NULL) {
			self::$singleton_instance = new self(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
		}
		return self::$singleton_instance;
	}
	
	/**
	 * return singleton_instance, create it first if it hasn't been yet
	 * Singleton() == getInstance()
	 * @return DataSource
	 */
	public static function getInstance() {
		return self::Singleton();
	}

	// constructor private -> used to establish database connection (using PDO class)
	private function __construct($dbHost, $dbName, $dbuser, $dbpasswd) {
		$this->dbh = new \PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName . ';charset=utf8', $dbuser, $dbpasswd);
		//$this->dbh->exec("set names utf8");
		$this->logger = \Logger::getLogger('DataSource');
	}

	/**
	 * disconnect from database, after this no more database queries possible
	 * @return void
	 */
	public function disconnect() {
		$this->dbh = NULL;
	}

	/**
	 * starts a new transaction
	 * @return void
	 */
	public function beginTransaction() {
		$this->dbh->beginTransaction();
	}

	/**
	 * commits a running transaction
	 * @throws PDOException - if no transaction active
	 * @return void
	 */
	public function commit() {
		$this->dbh->commit();
	}
	
	/**
	 * rolls back a running transaction
	 * @throws PDOException - if no transaction active
	 * @return void
	 */
	public function rollBack() {
		$this->dbh->rollBack();
	}

	/**
	 * 
	 * load queries (used by SQL class which fills this class)
	 * @param $prep_stmt string (identifier for query)
	 * @param $sql string (sql statement)
	 * @param $game_specific boolean (if true, this queries can be reset)
	 * @return void
	 */
	public function load_query($prep_stmt, $sql, $game_specific = false) {
		if ((!is_string($prep_stmt)) || (!is_string($sql))) {
			throw new DataSourceException('query is no string, $prep_stmt: \'' . $prep_stmt . '\'; $sql: \'' . $sql . '\'');
		}
		if (!empty($this->stmts_predefined_queries[$prep_stmt])) {
			trigger_error('statement query allready set $prep_stmt: \'' . $prep_stmt . '\'; $sql: \'' . $sql . '\'',E_USER_NOTICE);
		}
		$this->stmts_predefined_queries[$prep_stmt] = $sql;
		if ($game_specific) $this->game_specific_queries[] = $prep_stmt;
	}
	
	/**
	 * deletes all game specific predefined queries
	 * @return void
	 */
	public function reset_game_specific_queries() {
		foreach ($this->game_specific_queries as $prep_stmt) {
			unset($this->stmts_predefined[$prep_stmt]);
			unset($this->stmts_predefined_queries[$prep_stmt]);
		}
	}

	/**
	 * executes a prepared statement
	 * @param $prep_stmt string (identifier for query)
	 * @param $dictionary array
	 * @return array - lines of the query result
	 */
	public function execute_predefined_prepare($prep_stmt, $dictionary = NULL) {
		if (empty($this->stmts_predefined[$prep_stmt])) {
			$this->prepare_predefined_query($prep_stmt);
		}
		
		$this->stmts_predefined[$prep_stmt]->execute($dictionary);

		if (!$this->checkPredefErrors($prep_stmt)) {
			return DATABASE_ERROR;
		}

		return $this->stmts_predefined[$prep_stmt]->fetchAll(PDO::FETCH_ASSOC);
	}
	

	/**
	 * executes a prepared statement
	 * calls execute_predefined_prepare
	 * @param $prep_stmt string (identifier for query)
	 * @param $dictionary array
	 * @return array - lines of the query result
	 */
	public function epp($prep_stmt,$dictionary = NULL) {
		return self::execute_predefined_prepare($prep_stmt,$dictionary);
	}
	
	/**
	 * returns the id of the last row inserted
	 * @return int
	 */
	public function getLastInsertId() {
		return $this->dbh->lastInsertId();
	}

	private function prepare_predefined_query($prep_stmt) {
		$sql = $this->stmts_predefined_queries[$prep_stmt];
		$this->stmts_predefined[$prep_stmt] = $this->dbh->prepare($sql);
	}

	private function checkPredefErrors($prep_stmt) {
		// check for errors
		if (($errorCode = $this->stmts_predefined[$prep_stmt]->errorCode()) > 0) {
			$error = $this->stmts_predefined[$prep_stmt]->errorInfo();
			$msg = "PDOStatement::errorInfo():".$error[2]."PDO::errorCode():".$errorCode;
			$this->logger->fatal($msg);
			throw new DataSourceException($msg);
		}
		return true;
	}
}
