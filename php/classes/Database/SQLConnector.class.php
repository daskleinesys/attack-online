<?php
namespace Attack\Database;

use Attack\Exceptions\DatabaseException;

class SQLConnector {

    /**
     * @var $singleton SQLConnector
     */
    private static $singleton;

    /**
     * @var \Logger
     */
    private $logger;

    /**
     * @var \PDO
     */
    private $dbh;

    /**
     * @var array(\PDOStatement)
     */
    private $preparedStatements = array();

    /**
     * return singleton_instance, create it first if it hasn't been yet
     * Singleton() == getInstance()
     *
     * @return SQLConnector
     */
    public static function Singleton() {
        if (self::$singleton == null) {
            self::$singleton = new self(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
        }
        return self::$singleton;
    }

    /**
     * return singleton_instance, create it first if it hasn't been yet
     * Singleton() == getInstance()
     *
     * @return SQLConnector
     */
    public static function getInstance() {
        return self::Singleton();
    }

    // constructor private -> used to establish database connection (using PDO class)
    private function __construct($dbHost, $dbName, $dbuser, $dbpasswd) {
        $this->dbh = new \PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbuser, $dbpasswd);
        $this->logger = \Logger::getLogger('SQLConnector');
    }

    /**
     * disconnect from database, after this no more database queries possible
     *
     * @return void
     */
    public function disconnect() {
        $this->dbh = null;
    }

    /**
     * starts a new transaction
     *
     * @return void
     */
    public function beginTransaction() {
        $this->dbh->beginTransaction();
    }

    /**
     * commits a running transaction
     *
     * @return void
     * @throws \PDOException - if no transaction active
     */
    public function commit() {
        $this->dbh->commit();
    }

    /**
     * rolls back a running transaction
     *
     * @return void
     * @throws \PDOException - if no transaction active
     */
    public function rollBack() {
        $this->dbh->rollBack();
    }

    /**
     * executes a prepared statement
     *
     * @param $key string (identifier for query)
     * @param $dictionary array|null
     * @return array - lines of the query result
     * @throws DatabaseException
     */
    public function executePredefinedPreparedStatement($key, $dictionary = null) {
        if (empty($this->preparedStatements[$key])) {
            $query = SQLCommands::getQuery($key);
            if ($query == null) {
                throw new DatabaseException("unkown query key: {$key}");
            }
            $this->prepareStatement($key, $query);
        }

        $this->preparedStatements[$key]->execute($dictionary);
        if (($errorCode = $this->preparedStatements[$key]->errorCode()) > 0) {
            $error = $this->preparedStatements[$key]->errorInfo();
            $msg = "PDOStatement::errorInfo():{$error[2]}PDO::errorCode():{$errorCode}";
            $this->logger->fatal($msg);
            throw new DatabaseException($msg);
        }

        return $this->preparedStatements[$key]->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * executes a prepared statement
     * calls execute_predefined_prepare
     *
     * @param $key string (identifier for query)
     * @param $dictionary array
     * @return array - lines of the query result
     * @throws DatabaseException
     */
    public function epp($key, $dictionary = null) {
        return self::executePredefinedPreparedStatement($key, $dictionary);
    }

    /**
     * returns the id of the last row inserted
     *
     * @return int
     */
    public function getLastInsertId() {
        return $this->dbh->lastInsertId();
    }

    private function prepareStatement($key, $query) {
        if (isset($this->preparedStatements[$key])) {
            throw new DatabaseException('duplicate prepared statement declaration');
        }
        $this->preparedStatements[$key] = $this->dbh->prepare($query);
    }
}
