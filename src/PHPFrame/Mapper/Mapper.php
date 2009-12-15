<?php
/**
 * PHPFrame/Mapper/Mapper.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Mapper
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * This class is a facade class to simplify the interface of the whole Mapper 
 * subpackage.
 * 
 * This class should be extended to provide more specialised mappers for common 
 * persistent objects that require mapping. See the UsersMapper class in the user 
 * feature for an example. 
 * 
 * @category PHPFrame
 * @package  Mapper
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class PHPFrame_Mapper
{
    /**
     * Persistence Factory object used for the current mapper
     * 
     * @var PHPFrame_PersistenceFactory
     */
    private $_factory;
    
    /**
     * Constructor
     * 
     * @param string                   $target_class
     * @param PHPFrame_Database|string $db_or_path   Either a Database object 
     *                                               or path to directory for 
     *                                               XML storage. File name 
     *                                               will be table_name.xml, 
     *                                               where "table_name" is 
     *                                               either the supplied table 
     *                                               name or the target class.
     * @param string                   $table_name   [Optional]
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(
        $target_class, 
        $db_or_path, 
        $table_name=null
    ) {
        if ($db_or_path instanceof PHPFrame_Database) {
            $factory_class = "PHPFrame_SQLPersistenceFactory";
        } elseif (is_string($db_or_path)) {
            $factory_class = "PHPFrame_XMLPersistenceFactory";
        } else {
            $msg = "Storage mechanism not supported by mapper.";
            throw new LogicException($msg);
        }
        
        $this->_factory = new $factory_class(
            $target_class, 
            $table_name, 
            $db_or_path
        );
    }
    
    /**
     * Find a persistent object using an IdObject
     * 
     * @param PHPFrame_IdObject|int $id_obj
     * 
     * @return PHPFrame_PersistentObject
     * @since  1.0
     */
    public function findOne($id_obj)
    {
        return $this->_factory->getAssembler()->findOne($id_obj);
    }
    
    /**
     * Find a collection of persistent objects using an IdObject
     * 
     * @param PHPFrame_IdObject $id_obj
     * 
     * @return PHPFrame_PersistentObjectCollection
     * @since  1.0
     */
    public function find(PHPFrame_IdObject $id_obj=null)
    {
        return $this->_factory->getAssembler()->find($id_obj);
    }
    
    /**
     * Persist persistent object
     * 
     * @param PHPFrame_PersistentObject $obj
     * 
     * @return void
     * @since  1.0
     */
    public function insert(PHPFrame_PersistentObject $obj)
    {
        return $this->_factory->getAssembler()->insert($obj);
    }
    
    /**
     * Delete persistent object from persisted media (db or file)
     * 
     * @param int|PHPFrame_PersistentObject $obj
     * 
     * @return void
     * @since  1.0
     */
    public function delete($obj)
    {
        return $this->_factory->getAssembler()->delete($obj);
    }
    
    /**
     * Create a new IdObject to work with the target class
     * 
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    public function getIdObject()
    {
        return $this->_factory->getIdObject();
    }
    
    /**
     * Is the mapper using SQL persistance?
     * 
     * @return bool
     * @since  1.0
     */
    public function isSQL()
    {
        return ($this->_factory instanceof PHPFrame_SQLPersistenceFactory);
    }
    
    /**
     * Is the mapper using XML persistance?
     * 
     * @return bool
     * @since  1.0
     */
    public function isXML()
    {
        return ($this->_factory instanceof PHPFrame_XMLPersistenceFactory);
    }
}
