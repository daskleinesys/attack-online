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
    private $preparedStatements = [];

    /**
     * return singleton_instance, create it first if it hasn't been yet
     * Singleton() == getInstance()
     *
     * @return SQLConnector
     */
    public static function Singleton(): self {
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
    public static function getInstance(): self {
        return self::Singleton();
    }

    // constructor private -> used to establish database connection (using PDO class)
    private function __construct(string $dbHost, string $dbName, string $dbuser, string $dbpasswd) {
        $this->dbh = new \PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbuser, $dbpasswd);
        $this->logger = \Logger::getLogger('SQLConnector');
    }

    /**
     * disconnect from database, after this no more database queries possible
     *
     * @return void
     */
    public function disconnect(): void {
        $this->dbh = null;
    }

    /**
     * starts a new transaction
     *
     * @return void
     */
    public function beginTransaction(): void {
        $this->dbh->beginTransaction();
    }

    /**
     * commits a running transaction
     *
     * @return void
     * @throws \PDOException - if no transaction active
     */
    public function commit(): void {
        $this->dbh->commit();
    }

    /**
     * rolls back a running transaction
     *
     * @return void
     * @throws \PDOException - if no transaction active
     */
    public function rollBack(): void {
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
    public function executePredefinedPreparedStatement(string $key, ?array $dictionary = null): array {
        if (empty($this->preparedStatements[$key])) {
            $query = SQLCommands::getQuery($key);
            if (empty($query)) {
                throw new DatabaseException("unknown query key: {$key}");
            }
            $this->prepareStatement($key, $query);
        }

        $requiredValues = SQLCommands::getQueryParameters($key);
        if ($requiredValues) {
            foreach ($requiredValues as $valueKey => $valueType) {
                if (!array_key_exists($valueKey, $dictionary)) {
                    throw new DatabaseException("missing '{$valueKey}' for '{$key}'");
                }
                $this->preparedStatements[$key]->bindValue($valueKey, $dictionary[$valueKey], $valueType);
            }
        }
        $this->preparedStatements[$key]->execute();
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
    public function epp(string $key, ?array $dictionary = null): array {
        return self::executePredefinedPreparedStatement($key, $dictionary);
    }

    /**
     * returns the id of the last row inserted
     *
     * @return int
     */
    public function getLastInsertId(): ?int {
        return $this->dbh->lastInsertId();
    }

    /**
     * @param $key
     * @param $query
     * @throws DatabaseException
     */
    private function prepareStatement(string $key, string $query) {
        if (isset($this->preparedStatements[$key])) {
            throw new DatabaseException('duplicate prepared statement declaration');
        }
        $this->preparedStatements[$key] = $this->dbh->prepare($query);
    }

}
