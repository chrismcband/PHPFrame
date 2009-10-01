<?php
/**
 * PHPFrame/Mapper/SQLIdObject.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Mapper
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since     1.0
 */

/**
 * SQL IdObject class
 * 
 * This class encapsulates the selection of rows from the database.
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_SQLIdObject extends PHPFrame_IdObject
{
    /**
     * An array with the columns to get in SELECT statement
     * 
     * @var array
     */
    private $_select = array();
    /**
     * The name of the table this collection represents
     * 
     * @var string
     */
    private $_from = null;
    /**
     * Array containing joins
     * 
     * @var array
     */
    private $_join = array();
    /**
     * An array containing conditions for the SQL WHERE clause
     * 
     * @var string
     */
    private $_where = array();
    /**
     * String containing the group by clause
     * 
     * @var string
     */
    private $_groupby = null;
    /**
     * Column to use for ordering
     * 
     * @var string
     */
    private $_orderby = null;
    /**
     * Column to use for ordering
     * 
     * @var string
     */
    private $_orderdir = "ASC";
    /**
     * Number of rows per page
     * 
     * @var int
     */
    private $_limit = -1;
    /**
     * Row number from where the current page start
     * 
     * @var int
     */
    private $_limitstart = 0;
    /**
     * Input parameters used in prepared statements.
     * 
     * @var array
     */
    private $_params = array();
    
    /**
     * Constructor
     * 
     * @param array $options An associative array with initialisation options.
     *                       For a list of available options invoke 
     *                       PHPFrame_IdObject::getOptions().
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function __construct($options=null)
    {
        parent::__construct($options);
    }
    
    /**
     * Magic method invoked when trying to use object as string.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        return $this->getSQL();
    }
    
    /**
     * Return an array with the list of available options in this object.
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getOptions()
    {   
        return parent::getOptions();
    }
    
    /**
     * Set the fields array used in select statement
     * 
     * This method takes either a string a single column name or an array of column
     * names.
     * 
     * The "*" character is allowed and used to select "all" columns.
     * 
     * This method returns the IdObject object, making it possible to use "fluent syntax".
     * 
     * Example:
     * 
     * <code>
     * // Select all columns from table "my_table"
     * $id_object = new PHPFrame_IdObject();
     * $id_object->select("*")->from("my_table");
     * // echo the SQL, this will automatically cast the IdObject to string
     * echo $id_object;
     * 
     * 
     * // The same as above but passing the "select" and "table" options to the constructor.
     * $options = array("select"=>"*", "from"->"my_table");
     * $id_object = new PHPFrame_IdObject($options);
     * // echo the SQL, this will automatically cast the IdObject to string
     * echo $id_object;
     * 
     * // Now we create a new IdObject and select only some specified fields
     * $id_object = new PHPFrame_IdObject();
     * $id_object->select(array("id", "name", "email"))->from("my_table");
     * </code>
     * 
     * @param string|array $fields a string or array of strings with field names
     * 
     * @access public
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function select($fields)
    {
        // Validate input type and set internal property
        if (!is_array($fields)) {
            $fields = array($fields);
        }
        
        $processed_fields = array();
        
        foreach ($fields as $field) {
            // Parse table.field format if needed
            $pattern = "/^([a-zA-Z_\#]+)\.([a-zA-Z_\*]+)( AS ([a-zA-Z_]+))?$/";
            preg_match($pattern, $field, $matches);
            
            if (is_array($matches) && count($matches) == 5) {
                $processed_fields[] = array(
                    "table_name"=>$matches[1],
                    "field_name"=>$matches[2],
                    "field_alias"=>$matches[4]
                );
                
            } elseif (is_array($matches) && count($matches) == 3) {
                $processed_fields[] = array(
                    "table_name"=>$matches[1],
                    "field_name"=>$matches[2],
                    "field_alias"=>null
                );
            } else {
                $processed_fields[] = array(
                    "table_name"=>null, 
                    "field_name"=>$field, 
                    "field_alias"=>null
                );
            }
        }
        
        $this->_select = $processed_fields;
        
        return $this;
    }
    
    /**
     * Set the table from which to select rows
     * 
     * This method supports only one table in the from clause. 
     * Please use the join() method to add join tables.
     * 
     * Tables may be passed with an alias. Ie: "table_name AS tn".
     * 
     * @param string $table A string with the table name
     * 
     * @access public
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function from($table)
    {
        // Check if input contaings alias
        preg_match("/([a-zA-Z_\#\.]+) (as) ([a-zA-Z_]+)/i", $table, $matches);
        if (count($matches) == 4) {
            $table = array($matches[1], $matches[3]);
        }
        
        // Validate input type and set internal property
        if (is_string($table) && !preg_match("/^[a-zA-Z_\#\.]+$/", $table)) {
            $msg = "Argument \$table contains ilegal characters";
            throw new InvalidArgumentException($msg);
        }
        
        $this->_from = $table;
        
        return $this;
    }
    
    /**
     * Add a join clause to the select statement
     * 
     * @param sting $join A join statement
     * 
     * @access public
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function join($join)
    {
        $join       = trim((string) $join);
        $join_array = explode(" ON ", $join);
        
        preg_match('/^(JOIN|INNER JOIN|OUTER JOIN|LEFT JOIN|RIGHT JOIN)\s+(.*)$/', $join_array[0], $matches);
        if (is_array($matches) && count($matches) == 3) {
            $array["type"]       = $matches[1];
            $array["table_name"] = $matches[2];
        } elseif(count($matches) == 4) {
            $array["table_alias"] = $matches[2];
        } else {
            throw new LogicException("SQL join clause not recognised");
        }
        
        preg_match('/^(.+)\s+(=|<>|<|>|<=|>=)\s+(.+)$/', $join_array[1], $matches);
        if (is_array($matches) && count($matches) > 3) {
            $array["on"] = array($matches[1], $matches[2], $matches[3]);
        } else {
            throw new LogicException("SQL join clause not recognised");
        }
        
        $this->_join[] = $array;
        
        return $this;
    }
    
    /**
     * Add "where" condition
     * 
     * @param string $left
     * @param string $operator
     * @param string $right
     * 
     * @access public
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function where($left, $operator, $right)
    {
        // Validate input types and set internal property
        $pattern1 = "/^[a-zA-Z0-9_= \-\#\.\(\)\'\%\:]+$/";
        $pattern2  = "/^(=|<|>|<=|>=|AND|OR|LIKE|BETWEEN)$/";
        if (
            !preg_match($pattern1, $left)
            || !preg_match($pattern1, $right)
            || !preg_match($pattern2, $operator)
        ) {
            $msg  = "Arguments passed to ".get_class($this)."::where() ";
            $msg .= "contain illegal characters.";
            throw new InvalidArgumentException($msg);
        }
        
        $this->_where[] = array($left, $operator, $right);
        
        return $this;
    }
    
    /**
     * Set group by clause
     * 
     * @param string $column The column name to group by
     * 
     * @access public
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function groupby($column)
    {
        // Validate input type and set internal property
        if (!preg_match("/^[a-zA-Z_ \#\.]+$/", $column)) {
            $msg = "Argument \$column contains ilegal characters";
            throw new InvalidArgumentException($msg);
        }
        
        $this->_groupby = $column;
        
        return $this;
    }
    
    /**
     * Set order by clause
     * 
     * @param string $column    The column name to order by
     * @param string $direction The order direction (either ASC or DESC)
     * 
     * @access public
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function orderby($column, $direction=null)
    {
        // Validate input type and set internal property
        if (!preg_match("/^[a-zA-Z_\#\.]+$/", $column)) {
            $msg = "Argument \$column contains ilegal characters";
            throw new InvalidArgumentException($msg);
        }
        
        $this->_orderby = $column;
        
        if (!is_null($direction)) {
            $this->orderdir($direction);
        }
        
        return $this;
    }
    
    /**
     * Set order direction
     * 
     * @param string $column    The column name to order by
     * @param string $direction The order direction (either ASC or DESC)
     * 
     * @access public
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function orderdir($direction)
    {
        // Validate input type and set internal property
        if (!preg_match("/^(ASC|DESC)$/i", $direction)) {
            $msg = "Argument \$direction contains ilegal characters";
            throw new InvalidArgumentException($msg);
        }
        
        $this->_orderdir = $direction;
        
        return $this;
    }
    
    /**
     * Set limit clause
     * 
     * @param int $limit     The total number of entries we want to limit to
     * @param int $limitstart The entry number of the first record in the current page
     * 
     * @access public
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function limit($limit, $limitstart=null)
    {
        // Validate input type and set internal property 
        $this->_limit = (int) $limit;
        
        if (!is_null($limitstart)) {
            $this->limitstart($limitstart);
        }
        
        return $this;
    }
    
    /**
     * Set row number of first row in current page
     * 
     * @param int $limitstart The entry number of the first record in the current page
     * 
     * @access public
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function limitstart($limitstart)
    {
        // Validate input type and set internal property 
        $this->_limitstart = (int) $limitstart;
 
        return $this;
    }
    
    /**
     * Set the value of a query parameter
     * 
     * @param string $key   The parameter marker
     * @param string $value The paramter value
     * 
     * @access public
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function params($key, $value)
    {
        $this->_params[$key] = $value;
        
        return $this;
    }
    
    /**
     * Get full SQL statement for this IdObject
     * 
     * @param bool $limit A flag to indicate whether or not we want to include
     *                    LIMIT clause.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getSQL($limit=true)
    {
        $sql  = $this->getSelectSQL();
        $sql .= "\n".$this->getFromSQL();
        
        if ($this->getJoinsSQL()) {
            $sql .= "\n".$this->getJoinsSQL();
        }
        
        if ($this->getWhereSQL()) {
            $sql .= "\n".$this->getWhereSQL();
        }
        
        if ($this->getGroupBySQL()) {
            $sql .= "\n".$this->getGroupBySQL();
        }
        
        if ($this->getOrderBySQL()) {
            $sql .= "\n".$this->getOrderBySQL();
        }
        
        if ($this->getLimitSQL() && $limit) {
            $sql .= "\n".$this->getLimitSQL();
        }
        
        return $sql;
    }
    
    /**
     * Get the an array with the fields in the SELECT query
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getObjectFields()
    {
        $array = array();
        
        foreach ($this->_select as $field) {
            // If the field specifies a table name we check to see whether 
            // it is an alias and replace it with table name
            if (!empty($field["table_name"])) {
                $table_name = $this->_tableAliasToName($field["table_name"]);
                $table_name = (empty($table_name)) ? $field["table_name"] : $table_name;
                $array[]    = $table_name.".".$field["field_name"];
                
            // If no table name is specified we assume main "from" table
            } else {
                $array[] = $field["field_name"];
            }
        }
        
        return $array;
    }
    
    /**
     * Get the table name in the FROM part of the query
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getTableName()
    {
        if (is_array($this->_from)) {
            return $this->_from[0];
        }
        
        return $this->_from;
    }
    
    public function getJoinTables()
    {
        return $this->_join;
    }
    
    /**
     * Get query parameters
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getParams()
    {
        return $this->_params;
    }
    
    /**
     * Get limit
     * 
     * The total number of entries the subset will be limited to.
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getLimit()
    {
        return $this->_limit;
    }
    
    /**
     * Get limit start
     * 
     * The entry number of the first record in the current subset/page.
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getLimitstart()
    {
        return $this->_limitstart;
    }
    
    /**
     * Get SELECT SQL
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getSelectSQL()
    {
        if (count($this->_select) < 1) {
            $exception_msg = "Can not build query. No fields have been selected.";
            throw new LogicException($exception_msg);
        }
        
        $sql = "SELECT ";
        
        for ($i=0; $i<count($this->_select); $i++) {
            if ($i>0) $sql .= ", ";
            
            if (!empty($this->_select[$i]["table_name"])) {
                $sql .= $this->_select[$i]["table_name"].".";
            }
            
            $sql .= $this->_select[$i]["field_name"];
            
            if (!empty($this->_select[$i]["field_alias"])) {
                $sql .= " AS ".$this->_select[$i]["field_alias"];
            }
        }
        
        return $sql;
    }
    
    /**
     * Get FROM SQL
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getFromSQL()
    {
        if (empty($this->_from)) {
            $exception_msg = "Can not build query. No table to select from.";
            throw new LogicException($exception_msg);
        }
        
        if (is_array($this->_from)) {
            $sql = "FROM ".implode(" AS ", $this->_from);
        } else {
            $sql = "FROM ".$this->_from;
        }
        
        return $sql;
    }
    
    /**
     * 
     * Get JOIN SQL
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getJoinsSQL()
    {
        $sql = "";
        
        foreach ($this->_join as $join) {
            $sql .= " ".$join["type"]." ".$join["table_name"]." ";
            if (isset($join["table_alias"])) {
                $sql .= $join["table_alias"]." ";
            }
            $sql .= "ON ".$join["on"][0]." ".$join["on"][1]." ".$join["on"][2];
        }
        
        return $sql;
    }
    
    /**
     * Get WHERE SQL
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getWhereSQL()
    {
        $sql = "";
        
        if (count($this->_where) > 0) {
            $sql .= "WHERE ";
            
            for ($i=0; $i<count($this->_where); $i++) {
                if ($i>0) {
                    $sql .= " AND ";
                }
                $sql .= "(".$this->_where[$i][0];
                $sql .= " ".$this->_where[$i][1];
                $sql .= " ".$this->_where[$i][2].")";
            }
        }
        
        return $sql;
    }
    
    /**
     * Get GROUP BY SQL
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getGroupBySQL()
    {
        $sql = "";
        
        if (!empty($this->_groupby)) {
            $sql = "GROUP BY ".$this->_groupby;   
        }
        
        return $sql;
    }
    
    /**
     * Get ORDER BY SQL statement
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getOrderBySQL() 
    {
        $sql = "";
        
        if (is_string($this->_orderby) && $this->_orderby != "") {
            $sql .= " ORDER BY ".$this->_orderby." ";
            $sql .= ($this->_orderdir == "DESC") ? $this->_orderdir : "ASC";
        }
        
        return $sql;
    }
    
    /**
     * Get LIMIT SQL statement
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function getLimitSQL() 
    {
        $sql = "";
        
        if ($this->_limit > 0) {
            $sql .= "LIMIT ".$this->_limitstart.", ".$this->_limit;
        }
        
        return $sql;
    }
    
    private function _tableAliasToName($alias)
    {
        if (isset($this->_from[1]) && $this->_from[1] == $alias) {
            return $this->_from[0];
        }
        
        foreach ($this->_join as $join) {
            if ($join["table_alias"] == $alias) {
                return $join["table_name"];
            }
        }
        
        return null;
    }
}
