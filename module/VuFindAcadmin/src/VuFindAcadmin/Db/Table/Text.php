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
class Text extends Gateway
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('text', 'VuFindAcadmin\Db\Row\Text');
    }

       /**
     * Get all locations.
     *
     * @return array
     */
    public function getAll() {
    	$callback = function ($select) {};
    	return $this->select($callback);
    }
    
  
    public function getByTextType($id_text_type)
    {
        $callback =  function ($select) use ($id_text_type) {
    		$select->where->equalTo('id_text_type',$id_text_type);
    	};
    	return $this->select($callback)->current();
    }
    
}
