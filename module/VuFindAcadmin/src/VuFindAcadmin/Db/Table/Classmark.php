<?php
/**
 * Table Definition for clasmark
 *
 * PHP version 5
 *
 * @category VuFindAcadmin
 * @package  Db_Table
 * @author   Scanbit
 */
namespace VuFindAcadmin\Db\Table;

use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Where;

/**
 * Table Definition for record
 *
 * @category VuFindAcadmin
 * @package  Db_Table
 * @author   Scanbit
 */
class Classmark extends Gateway
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('classmark', 'VuFindAcadmin\Db\Row\Classmark');
    }

       /**
     * Get all classmarks.
     *
     * @return array
     */
    public function getAll() {

    	$callback = function ($select) {};
    	return $this->select($callback);
    }
    
    public function getByID($id_classmark)
    {
        $callback =  function ($select) use ($id_classmark) {
    		$select->where->equalTo('id_classmark',$id_classmark);
    	};
    	return $this->select($callback)->current();
    }
    
    public function getByName($name)
    {
        $callback =  function ($select) use ($name) {
    		$select->where->equalTo('name',$name);
    	};
    	return $this->select($callback)->current();
    }
    
     public function getFromTo($from,$to)
    {
        $callback =  function ($select) use ($from,$to) {
    		$select->where->greaterThanOrEqualTo('order', $from);
    		$select->where->lessThanOrEqualTo('order',$to);
    	};
    	return $this->select($callback);
    }
    
}
