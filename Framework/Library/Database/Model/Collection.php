<?php

/**
 * Hoa Framework
 *
 *
 * @license
 *
 * GNU General Public License
 *
 * This file is part of Hoa Open Accessibility.
 * Copyright (c) 2007, 2010 Ivan ENDERLIN. All rights reserved.
 *
 * HOA Open Accessibility is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * HOA Open Accessibility is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with HOA Open Accessibility; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *
 * @category    Framework
 * @package     Hoa_Database
 * @subpackage  Hoa_Database_Model_Collection
 *
 */

/**
 * Hoa_Core
 */
require_once 'Core.php';

/**
 * Hoa_Database
 */
import('Database.~');

/**
 * Hoa_Database_Model_Exception
 */
import('Database.Model.Exception');

/**
 * Hoa_Database_Model_Table
 */
import('Database.Model.Table');

/**
 * Hoa_Database_QueryBuilder_Table
 */
import('Database.QueryBuilder.Table');

/**
 * Hoa_Database_Dal
 */
import('Database.Dal.~');

/**
 * Class Hoa_Database_Model_Collection.
 *
 * (Alpha class, not used for now).
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2010 Ivan ENDERLIN.
 * @license     http://gnu.org/licenses/gpl.txt GNU GPL
 * @since       PHP 5
 * @version     0.1a
 * @package     Hoa_Database
 * @subpackage  Hoa_Database_Model_Collection
 */

class Hoa_Database_Model_Collection implements Iterator, Countable {

    /**
     * Type of collection (usefull ?).
     *
     * @const int
     */
    const TYPE_BAG   = 0;
    const TYPE_SET   = 1;
    const TYPE_LIST  = 2;
    const TYPE_ARRAY = 4;

    /**
     * The table name.
     *
     * @var Hoa_Database_Model_Collection string
     */
    protected $_tableName = null;

    /**
     * The table.
     *
     * @var Hoa_Database_Model_Table object
     */
    protected $_table     = null;

    /**
     * The collection.
     *
     * @var Hoa_Database_Model_Collection array
     */
    private $_collection  = array();



    /**
     * Set the reference table.
     *
     * @access  public
     * @return  void
     * @throw   Hoa_Database_Model_Exception
     */
    public function __construct ( ) {

        $this->setTable();
    }

    /**
     * Set the table.
     *
     * @access  public
     * @return  string
     * @throw   Hoa_Database_Model_Exception
     */
    protected function setTableName ( ) {

        $pattern = Hoa_Database::getInstance()
                   ->getParameter('collection.classname', null, false);
        $pattern = preg_replace(
                       '#(\(:[^\)]+\)(.))#e',
                       "'(.*)' . preg_quote('\\2')",
                       $pattern);

        if(0 === preg_match('#' . $pattern . '$#', get_class($this), $match))
            throw new Hoa_Database_Model_Exception(
                'Bad collection name, given %s ; ' .
                'should match with <TableName>Collection.',
                0, get_class($this));

        $name = Hoa_Database::getInstance()
                ->getParameter('table.classname', $match[1]);

        $old              = $this->_tableName;
        $this->_tableName = $name;

        return $old;
    }

    /**
     * Set the reference table.
     *
     * @access  public
     * @return  void
     * @throw   Hoa_Database_Model_Exception
     */
    private function setTable ( ) {

        $this->setTableName();
        $name = $this->getTableName();

        if(!class_exists($name))
            throw new Hoa_Database_Model_Exception(
                'The table %s is not found from the %s collection.',
                1, array($name, get_class($this)));

        $old          = $this->_table;
        $this->_table = new $name();

        return $old;
    }

    /**
     * Get the table name.
     *
     * @access  public
     * @return  string
     */
    public function getTableName ( ) {

        return $this->_tableName;
    }

    /**
     * Get the reference table.
     *
     * @access  public
     * @return  Hoa_Database_Model_Table
     */
    protected function getTable ( ) {

        return $this->_table;
    }

    /**
     * Get the current field for the iterator.
     *
     * @access  public
     * @return  Hoa_Database_Model_Table
     */
    public function current ( ) {

        return current($this->_collection);
    }

    /**
     * Get the current table name for the iterator.
     *
     * @access  public
     * @return  string
     */
    public function key ( ) {

        return key($this->_collection);
    }

    /**
     * Advance the internal field collection pointer, and return the current
     * table.
     *
     * @access  public
     * @return  Hoa_Database_Model_Table
     */
    public function next ( ) {

        return next($this->_collection);
    }

    /**
     * Rewind the internal table collection pointer, and return the first
     * table.
     *
     * @access  public
     * @return  Hoa_Database_Model_Table
     */
    public function rewind ( ) {

        return reset($this->_collection);
    }

    /**
     * Check if there is a current element after calls to the rewind or the next
     * methods.
     *
     * @access  public
     * @return  bool
     */
    public function valid ( ) {

        if(empty($this->_collection))
            return false;

        $key    = key($this->_collection);
        $return = (next($this->_collection) ? true : false);
        prev($this->_collection);

        if(false === $return) {

            end($this->_collection);
            if($key === key($this->_collection))
                $return = true;
        }

        return $return;
    }

    /**
     * Count the number of field in this table.
     *
     * @access  public
     * @return  int
     */
    public function count ( ) {

        return count($this->_collection);
    }

    /**
     * Wrap the Hoa_Database_Model_Table attributes.
     *
     * @access  public
     * @param   string  $name    The attribute name.
     * @return  mixed
     */
    public function __get ( $name ) {

        return $this->getTable()->$name;
    }

    /**
     * Wrap the Hoa_Database_Model_Table methods.
     *
     * @access  public
     * @param   string  $name     The method name.
     * @param   array   $value    The method parameters.
     * @return  mixed
     */
    public function __call ( $name, $value ) {

        if(!method_exists($this->getTable(), $name))
            throw new Hoa_Database_Model_Table(
                'Call to undefined method %s::%s().',
                2, array(get_class($this), $name));

        return call_user_func_array(
            array($this->getTable(), $name),
            $value
        );
    }

    /**
     * “Overload” the Hoa_Database_Model_Table::query() method.
     * It is not really an overlading, but all methods call are redirected
     * throught the self::__call() method, except this one. We got the same
     * comportement like a overloading.
     *
     * @access  public
     * @return  void
     * @throw   Hoa_Database_Model_Exception
     * @todo    Considere the TYPE_* constants.
     */
    public function query ( ) {

        $this->_collection = array();
        $query             = $this->getQuery();
        $qType             = $this->getType();

        if(empty($query))
            throw new Hoa_Database_Model_Exception(
                'No query to execute.', 3);

        echo '**QUERY (collection)**' . "\n";
        echo $query;
        echo "\n\n";

        $preparedValue = array();
        foreach($this->getTable() as $foo => $field)
            $preparedValue = array_merge(
                                 $preparedValue,
                                 $field->getCriterion()->getPreparedValue()
                             );

        $statement = Hoa_Database_Dal::getLastInstance()->prepare($query);
        $a = $statement->execute($preparedValue);
        $fetch = $statement->fetchAll();
        // Bug with $this->eraseQuery(). Why ?

        print_r($fetch);

        if(count($fetch) == 0)
            return;

        $tableName = $this->getTableName();

        // Assign value in field.
        switch($qType) {

            case Hoa_Database_QueryBuilder_Table::TYPE_SELECT:
                foreach($fetch as $i => $record) {

                    $this->_collection[$i] = new $tableName();

                    foreach($record as $name => $value) {

                        if(true === $this->_collection[$i]->fieldExists($name))
                            $this->_collection[$i]->getField($name)->setValue($value);
                    }
                }
        }
    }
}
