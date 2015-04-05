<?php
/**
 * ac_db.inc.php: Database class using the PHP OCI8 extension
 * @package Oracle
 */
 
namespace Oracle;
 
require('ac_cred.inc.php');
 
/**
 * Oracle Database access methods
 * @package Oracle
 * @subpackage Db
 */
class Db {
 
    /**
     * @var resource The connection resource
     * @access protected
     */
    protected $conn = null;
    /**
     * @var resource The statement resource identifier
     * @access protected
     */
    protected $stid = null;
    /**
     * @var integer The number of rows to prefetch with queries
     * @access protected
     */
    protected $prefetch = 100;
 /**
     * Constructor opens a connection to the database
     * @param string $module Module text for End-to-End Application Tracing
     * @param string $cid Client Identifier for End-to-End Application Tracing
     */
    function __construct($module, $cid) {
        $this->conn = @oci_pconnect(SCHEMA, PASSWORD, DATABASE, CHARSET);
        if (!$this->conn) {
            $m = oci_error();
            throw new \Exception('Cannot connect to database: ' . $m['message']);
        }
        // Record the "name" of the web user, the client info and the module.
        // These are used for end-to-end tracing in the DB.
        oci_set_client_info($this->conn, CLIENT_INFO);
        oci_set_module_name($this->conn, $module);
        oci_set_client_identifier($this->conn, $cid);
    }
 
    /**
     * Destructor closes the statement and connection
     */
    function __destruct() {
        if ($this->stid)
            oci_free_statement($this->stid);
        if ($this->conn)
            oci_close($this->conn);
    }
    /**
     * Run a SQL or PL/SQL statement
     *
     * Call like:
     *     Db::execute("insert into mytab values (:c1, :c2)",
     *                 "Insert data", array(array(":c1", $c1, -1),
     *                                      array(":c2", $c2, -1)))
     *
         * For returned bind values:
     *     Db::execute("begin :r := myfunc(:p); end",
     *                 "Call func", array(array(":r", &$r, 20),
     *                                    array(":p", $p, -1)))
     *
     * Note: this performs a commit.
     *
     * @param string $sql The statement to run
     * @param string $action Action text for End-to-End Application Tracing
     * @param array $bindvars Binds. An array of (bv_name, php_variable, length)
     */
    public function execute($sql, $action, $bindvars = array()) {
        $this->stid = oci_parse($this->conn, $sql);
        if ($this->prefetch >= 0) {
            oci_set_prefetch($this->stid, $this->prefetch);
        }
        foreach ($bindvars as $bv) {
            // oci_bind_by_name(resource, bv_name, php_variable, length)
            oci_bind_by_name($this->stid, $bv[0], $bv[1], $bv[2]);
        }
        oci_set_action($this->conn, $action);
        oci_execute($this->stid);              // will auto commit
    }
    /**
     * Run a query and return all rows.
     *
     * @param string $sql A query to run and return all rows
     * @param string $action Action text for End-to-End Application Tracing
     * @param array $bindvars Binds. An array of (bv_name, php_variable, length)
     * @return array An array of rows
     */
    public function execFetchAll($sql, $action, $bindvars = array()) {
        $this->execute($sql, $action, $bindvars);
        oci_fetch_all($this->stid, $res, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
        $this->stid = null;  // free the statement resource
        return($res);
    }
    
    /**
     * Run a query return a subset of records. Used for paging through
     * a resulset.
     * 
     * The query is used as an embedded subquery. Don't permit user
     * generated conten is $sql because of the SQL Injecction security issue
     *
     * @param string $sql The query to run
     * @param string $action Action text or End-to-End Application Tracing
     * @param integer $firstrow The first row number of the dataset to return
     * @param integer $numrows The number of rows to return 
     * @param array $bindvars Binds. An array of (bv_name, php_variable, length)
     * @return array Returns an array of rows
     * 
     */
    
    public function execFetchPage($sql, $action, $firstrow = 1, $numrows = 1,
            $bindvars = array()){
        //
        $query = 'SELECT *
                FROM (SELECT a.*, ROWNUM AS rnum
                FROM (' . $sql . ') a
                WHERE ROWNUM <= :sq_last)
                WHERE :sq_first <=RNUM';
              
        // Set up bind variables.
        array_push($bindvars, array(':sq_first', $firstrow, -1));
        array_push($bindvars, array(':sq_last', $firstrow + $numrows -1, -1));
        $res = $this->execFetchAll($query, $action, $bindvars);
        return($res);
    }
    /**
     *Run a  call to a stored that returns a REF CURSOR data
     * set in a bind variable. The data set is fetched and returned.
     * 
     * Call like Db::refcurexecfetchall("begin myproc(:rc, :p); end",
     *              "Fetch data", ":rc", array(array(":p", $p, -1)))
     * The asumption that there is only one refcursor is an artificial
     * limitation of refcurexefetchall()
     * 
     * @param string $sql a SQL strin calling PL/SQL stored procedure
     * @param string $Action text for End-to-End Application Tracing
     * @param string $rcname the name of the REF CURSOR bind variable
     * @param array $otherbindvars Binds. Array (bv_name, php_variable, length)
     * @param array Returns an array of tuples
     */
      
    public function refcurExecFetchAll($sql, $action, $rcname,
            $otherbindvars = array()){
        $this->stid = oci_parse($this->conn, $sql);
        $rc = oci_new_cursor($this->conn);
        oci_bind_by_name($this->stid, $rcname, $rc, -1, OCI_B_CURSOR);
        foreach ($otherbindvars as $bv){
            //oci_bind_by_name(resource, bv_name, php_variable, length)
            oci_bind_by_name($this->stid, $bv[0], $bv[1], $bv[2]);
        }
        oci_set_action($this->conn, $action);
        oci_execute($this->stid);
        oci_execute($rc); // run the ref cursor as if it were a statement id
        oci_fetch_all($rc, $res);
        $this->stid = null;
        return($res);
                
    }
}
 
?>