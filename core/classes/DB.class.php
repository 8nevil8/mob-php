<?
/**
 * A PHP class to access MySQL database
 */
class DB {
    /**
     * Connection resource
     *
     * @var Resource
     */
    private $linkId = false;
    private $dbName;
    private $hostname;
    private $username;
    private $password;
    private static $exceptions = array(
        'parse_ini_error' => 'Erro pasing ini.',
        'sql_connect_error' => 'Couldn\'t connect to DB.',
        'sql_select_db_error' => 'Couldn\'t select DB.'
    );

    /**
     * Constructor with specified config file name.
     * ini file should contain: hostname, username, password, dbname
     * @param <type> $configFile name of config file. conn.ini - by default.
     * @exception Exception failed to parse ini or connect to db.
     */
    function DB($configFile='conn.ini') {
        $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . Config::$APP_DIR . Config::$CONFIG_DIR_PREFIX . $configFile);
        if (!$config) {
            throw new Exception(self::$exceptions['parse_ini_error']);
        }
        $this->hostname = $config['hostname'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->dbName = $config['dbname'];
        $this->connect();
        mysql_query('SET NAMES utf8');
    }

    /**
     * Connect to DB..
     *
     * @exception Exception failed to connect or select DB.
     */
    public function connect() {
        if (!$this->linkId) {
            $this->linkId = mysql_connect($this->hostname, $this->username, $this->password);
        }
        if ($this->linkId === false) {
            throw new Exception(self::$exceptions['sql_connect_error'], mysql_errno());
        }
        if (!mysql_select_db("$this->dbName", $this->linkId)) {
            throw new Exception(self::$exceptions['sql_select_db_error'], mysql_errno());
        }
        return true;
    }

    /**
     * Execute query.
     *
     * @param  string   $query query.
     * @return resource response.
     * @exception Exception query error.
     */
    public function query($query, $params = null) {
        $res = $this->executeQuery($query, $params);
        if (mysql_errno()) {
            throw new Exception('Error "' . (htmlSpecialChars($query)) . '":' . htmlSpecialChars(mysql_error()));
        }
        return $res;
    }

    /**
     * Execute query.
     *
     * @param  string   $query query.
     * @return array.
     * @exception Exception query error.
     */
    public function queryForList($query, $params = null) {
        $res = $this->executeQuery($query, $params);
        if (mysql_errno()) {
            throw new Exception('Error "' . (htmlSpecialChars($query)) . '":' . htmlSpecialChars(mysql_error()));
        }
        $list = array();
        while ($row = mysql_fetch_object($res)) {
            $list[] = $row;
        }
        return $list;
    }

    /**
     * Executes query.
     * @param string $query
     * @param array $params
     * @return result
     */
    private function executeQuery($query, $params = null) {
        Debug::printMessage($query);
        $query = $this->prepare_query($query, $params);
        return mysql_query($query);
    }

    /**
     * Rerurns number of affected rows on success, and -1 if the last query failed.
     * @return int
     */
    public function affected_rows() {
        return mysql_affected_rows();
    }

    /**
     * Fetch next object. If result == null, return false.
     *
     * @param resource $result
     * @return object.
     */
    public function fetch_object($result = NULL) {
        if ($result == NULL) {
            return false;
        }
        Debug::printMessage("Fetch object by result ($result)");
        return mysql_fetch_object($result);
    }

    public function query_unique_object($query, $params = null) {
        $query.=' LIMIT 1';
        $result = $this->query($query, $params);
        return $this->fetch_object($result);
    }

    public function getInsertId() {
        return mysql_insert_id();
    }

    /**
     * Returns row counts
     * @param string $query
     * @return int
     */
    public function count($query) {
        $result = $this->query($query);
        return mysql_result($result, 0);
    }

    /**
     * Prepare query for execution. Replace :param: with appropriate value.
     * @param string $query
     * @param array $params
     * @return string
     */
    public function prepare_query($query, $params = null) {
        if (null != $params) {
            $resultCount = 0;
            foreach ($params as $key => $value) {
                $query = str_replace(':' . $key . ':', $value, $query, $resultCount);
            }
        }
        Debug::printMessage($query);
        return $query;
    }
}
?>
