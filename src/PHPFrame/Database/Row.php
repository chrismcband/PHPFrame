<?php
/**
 * PHPFrame/Database/Row.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Database
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */

/**
 * Row Class
 * 
 * The "row" class is an abstraction of a table row in a database.
 * 
 * Note that this class uses the Application Registry object to cache table 
 * structures and primary keys in order to avoid unnecessary trips to the database.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Database
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Database_Row
{
    /**
     * A reference to the DB connection to use for mapping the row
     * 
     * @var PHPFrame_Database
     */
    private $_db=null;
    /**
     * An IdObject used to map the row to the db
     * 
     * @var PHPFrame_Database_IdObject
     */
    private $_id_obj=null;
    /**
     * An array containing the "field" objects that make up the row
     * 
     * @var array
     */
    private $_fields=array();
    
    /**
     * Constructor
     * 
     * The constructor takes only one required parameter ($table_name), that being
     * the name of the database table the row will mapped to.
     * 
     * Note that this can be overriden after instantiation when invoking 
     * PHPFrame_Database_Row::load(), as it can take an IdObject instead of an 
     * id as an argument.
     * 
     * @param string            $table_name  The table to map this row to in the db.
     * @param PHPFrame_Database $db          Optionally use an alternative database 
     *                                       to the default one provided by 
     *                                       PHPFrame::DB() as defined in config 
     *                                       class.
     * @param string            $primary_key This parameter allows to set a different
     *                                       primary key from the one automatically
     *                                       read from the table structure in the db.
     * @access public
     * @return void
     * @see    PHPFrame_Database_IdObject, PHPFrame_Database_RowCollection
     * @since  1.0
     */
    public function __construct(
        $table_name,
        PHPFrame_Database $db=null,
        $primary_key=null
    ) {
        $table_name = (string) $table_name;
        
        if ($db instanceof PHPFrame_Database) {
            $this->_db = $db;
        } else {
            $this->_db = PHPFrame::DB();
        }
        
        // Acquire IdObject
        $this->_id_obj = new PHPFrame_Database_IdObject();
        // Initialise fiels selection and table name in IdObject
        $this->_id_obj->select("*")->from($table_name);
        
        // Read table structure from application registry
        $this->_fetchFields();
        
        // Override primary key detected from db with given value
        if (!is_null($primary_key)) {
            $this->setPrimaryKey($primary_key);
        }
    }
    
    /**
     * Magic method invoked when trying to use an IdObject as a string.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        return $this->toString();
    }
    
    /**
     * Magic getter
     * 
     * This is called when we try to access a public property and tries to 
     * find the key in the columns array.
     * 
     * This method also enforces that public properties are not mistakenly 
     * referenced.
     * 
     * @param string $key The key to retrieve a value from internal array.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __get($key)
    {
        return $this->get($key);
    }
    
    /**
     * Convert object to string
     * 
     * @param bool $show_keys Boolean to indicate whether we want to show the
     *                        column names. Default is TRUE.
     *                        
     * @access public
     * @return string
     * @since  1.0
     */
    public function toString($show_keys=true)
    {
        $str = "";
        
        foreach ($this->_fields as $field) {
            if ($show_keys) {
                $str .= $field->getField().": ".$field->getValue()."\n";
            } else {
                $str .= PHPFrame_Base_String::fixLength($field->getValue(), 16)."\t";
            }
        }
        
        return $str;
    }
    
    /**
     * Get a column value from this row
     * 
     * @param string $key The column we want to get the value for.
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function get($key)
    {
        foreach ($this->_fields as $field) {
            if ($field->getField() == $key) {
                return $field->getValue();
            }
        }
        
        throw new PHPFrame_Exception("Tried to get column '".$key
                                     ."' that doesn't exist in "
                                     .$this->_id_obj->getTableName(), 
                                      PHPFrame_Exception::E_PHPFRAME_WARNING);
    }
    
    public function setPrimaryKey($field_name)
    {
        foreach ($this->_fields as $field) {
            // Reset other fields that might have been set as primary keys
            if ($field->isPrimaryKey()) {
                $field->setPrimaryKey("");
            }
            
            // Set the field as primary key
            if ($field->getField() == $field_name) {
                $field->setPrimaryKey("PRI");
            }
        }
    }
    
    public function setPrimaryKeyValue($value)
    {
        foreach ($this->_fields as $field) {
            // Reset other fields that might have been set as primary keys
            if ($field->isPrimaryKey()) {
                $field->setValue($value);
            }
        }
    }
    
    public function getPrimaryKey()
    {
        foreach ($this->_fields as $field) {
            if ($field->isPrimaryKey()) {
                return $field->getField();
            }
        }
        
        return null;
    }
    
    public function getPrimaryKeyValue()
    {
        foreach ($this->_fields as $field) {
            if ($field->isPrimaryKey()) {
                return $field->getValue();
            }
        }
        
        return null;
    }
    
    /**
     * Set a column value in this row
     *  
     * @param string $key   The column we want to set the value for.
     * @param string $value The value to set the column to.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function set($key, $value)
    {
        if (!$this->hasField($key)) {
            throw new PHPFrame_Exception("Tried to set column '".$key
                                         ."' that doesn't exist in "
                                         .$this->_id_obj->getTableName(), 
                                          PHPFrame_Exception::E_PHPFRAME_WARNING);
        }
        
        foreach ($this->_fields as $field) {
            if ($field->getField() == $key) {
                $field->setValue($value);
            }
        }
    }
    
    /**
     * Get column keys
     * 
     * @access public
     * @return array
     * @since  1.0
     */
    public function getKeys()
    {
        $array = array();
        
        foreach ($this->_structure as $col) {
            $array[] = $col->Field;
        }
        
        return $array;
    }
    
    /**
     * Check if row has a given column
     * 
     * @param string $column_name The column name we want to check.
     * 
     * @access public
     * @return bool
     * @since  1.0
     */
    public function hasField($column_name)
    {
        // Loop through table structure to find key
        foreach ($this->_fields as $field) {
            if ($field->getField() == $column_name) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Load row data from database given a row id.
     * 
     * @param int|string|PHPFrame_Database_IdObject $id      Normally an integer or string 
     *                                                       with the primary key value of
     *                                                       the row we want to load.
     *                                                       Alternatively you can pass an
     *                                                       IdObject.
     * @param string                                $exclude A list of key names to exclude 
     *                                                       from binding process separated 
     *                                                       by commas.
     * 
     * @access public
     * @return PHPFrame_Database_Row
     * @since  1.0
     */
    public function load($id, $exclude='')
    {
        if ($id instanceof PHPFrame_Database_IdObject) {
            $this->_id_obj = $id;
        } else {
            $this->_id_obj->where($this->getPrimaryKey(), "=", ":id");
            $this->_id_obj->params(":id", $id);
        }
        
        // Cast IdObject to string to convert to SQL query
        $sql = (string) $this->_id_obj;
        
        // Prepare SQL statement
        $stmt = $this->_db->prepare($sql);
        // Execute SQL statement
        $stmt->execute($this->_id_obj->getParams());
        
        // Fetch result as assoc array
        $array = $stmt->fetch(PDO::FETCH_ASSOC);
        // If result is array we bind it to the row
        if (is_array($array) && count($array) > 0) {
            $this->bind($array, $exclude);   
        }
        
        return $this;
    }
    
    /**
     * Bind array to row
     * 
     * @param array  $array   The array to bind to the object.
     * @param string $exclude A list of key names to exclude from binding 
     *                        process separated by commas.
     * 
     * @access public
     * @return PHPFrame_Database_Row
     * @since  1.0
     */
    public function bind($array, $exclude='')
    {
        // Process exclude
        if (!empty($exclude)) {
            $exclude = explode(',', $exclude);
        } else {
            $exclude = array();
        }
        
        if (!is_array($array)) {
            $exception_msg = 'Argument 1 ($array) has to be of type array.';
            throw new PHPFrame_Exception_Database($exception_msg);
        }
        
        if (count($array) > 0) {
            // Rip values using known structure
            foreach ($this->_fields as $field) {
                $field_name = $field->getField();
                if (array_key_exists($field_name, $array) 
                    && !in_array($field_name, $exclude)
                ) {
                    $field->setValue($array[$field_name]);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Store row in database
     * 
     * @access public
     * @return PHPFrame_Database_Row
     * @since  1.0
     */
    public function store()
    {
        // Check types and required columns before saving
        $this->_check();
        
        // Do insert or update depending on whether primary key is set
        $id = $this->getPrimaryKeyValue();
        if (is_null($id) || empty($id)) {
            // Insert new record
            $this->_insert();
        } else {
            $this->_update();
        }
        
        return $this;
    }
    
    /**
     * Delete row from database
     * 
     * @param int|string $id Normally an integer value reprensting the row primary 
     *                       key value. This could also be a string.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function delete($id)
    {
        $query = "DELETE FROM `".$this->_id_obj->getTableName()."` ";
        $query .= " WHERE `".$this->getPrimaryKey()."` = '".$id."'";
        $this->_db->query($query);
    }
    
    /**
     * Read row structure from database and store in app registry
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _fetchFields()
    {
        $table_name = $this->_id_obj->getTableName();
        
        // Fetch the structure of the table that contains this row
        $table_structure = $this->_fetchTableStructure($table_name);
        
        // Loop through structure array to build field objects
        foreach ($table_structure as $field_array) {
            $this->_fields[] = new PHPFrame_Database_Field($field_array);
        }
        
        // Add foreign fields from joined tables
        $join_tables = $this->_id_obj->getJoinTables();
        if (is_array($join_tables) && count($join_tables) > 0) {
            foreach ($join_tables as $join_table) {
                // Fetch the structure of the table
                $table_structure = $this->_fetchTableStructure($join_table);
                
                // Loop through structure array to build field objects
                foreach ($table_structure as $field_array) {
                    array_push($field_array, true);
                    $this->_fields[] = new PHPFrame_Database_Field($field_array);
                }
            }
        }
    }
    
    private function _fetchTableStructure($table_name)
    {
        $app_registry = PHPFrame::AppRegistry();
        $table_structures = $app_registry->get('table_structures');
        
        // Load structure from db if not in application registry already
        if (!isset($table_structures[$table_name]) 
            || !is_array($table_structures[$table_name])) {
            $sql = "SHOW COLUMNS FROM `".$table_name."`";
            
            $stmt = $this->_db->prepare($sql);
            $stmt->execute();
            $array = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $error_info = $stmt->errorInfo();
            if (is_array($error_info) && count($error_info) > 1) {
                $exception_msg = "Couldn't read table structure for ";
                $exception_msg .= $table_name;
                throw new PHPFrame_Exception_Database($exception_msg, $error_info[2]);
            }
            
            // Add table structure to structures array
            $table_structures[$table_name] = $array;
            
            // Store data in app registry
            $app_registry->set('table_structures', $table_structures);
        }
        
        return $table_structures[$table_name];
    }
    
    /**
     * Check columns data types and required fields before saving to db.
     * 
     * @access private
     * @return bool
     * @since  1.0
     */
    private function _check()
    {
        // Delegate field validation to field objects
        foreach ($this->_fields as $field) {
            //if (!$field->isValid()) {
                //return false;
            //}
        }
        
        return true;
    }
    
    /**
     * Insert new record into database
     *   
     * @access private
     * @return void
     * @since  1.0
     */
    private function _insert()
    {
        // Build SQL insert query
        $query = "INSERT INTO `".$this->_id_obj->getTableName()."` ";
        
        foreach ($this->_fields as $field) {
            $columns[] = $field->getField();
            $values[] = $field->getValue();
        }
        
        $query .= " (`".implode("`, `", $columns)."`) ";
        $query .= " VALUES ('".implode("', '", $values)."')";
        
        $insert_id = $this->_db->query($query);
        
        if ($insert_id === false) {
            throw new PHPFrame_Exception($this->_db->getLastError());
        }
        
        $this->setPrimaryKeyValue($insert_id);
    }
    
    /**
     * Update existing row in database
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _update()
    {
        // Build SQL insert query
        $query = "UPDATE `".$this->_id_obj->getTableName()."` SET ";
        $i=0;
        foreach ($this->_fields as $field) {
            if ($i>0) {
                $query .= ", ";
            }
            $query .= " `".$field->getField()."` = '".$field->getValue()."' ";
            $i++;
        }
        $query .= " WHERE `".$this->getPrimaryKey()."` = '";
        $query .= $this->getPrimaryKeyValue()."'";
        
        if ($this->_db->query($query) === false) {
            throw new PHPFrame_Exception("Error updating database row",
                                         PHPFrame_Exception::E_PHPFRAME_WARNING,
                                         "Query: ".$query);
        }
        
    }
}
