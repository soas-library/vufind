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
class User extends Gateway
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('user', 'VuFindAcadmin\Db\Row\User');
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
    
    public function getByID($id)
    {
        $callback =  function ($select) use ($id) {
    		$select->where->equalTo('id',$id);
    	};
    	return $this->select($callback)->current();
    }
    
    public function getByBarcode($barcode)
    {
        $callback =  function ($select) use ($barcode) {
    		$select->where->equalTo('cat_username',$barcode);
    	};
    	return $this->select($callback)->current();
    }
    
    public function getFilterUsers($username, $firstname, $lastname,$id=""){
    	$sql="select user.id, user.username, user.firstname, user.lastname, user.email, profile.name as profile_name from user";
    	$sql =$sql." inner join profile on profile.id_profile = user.id_profile";
	$sql =$sql." where 1=1";
	
	if($username != null)
		$sql= $sql." AND user.username LIKE '%".trim($username)."%'";
	if($firstname != null)
		$sql= $sql." AND user.firstname LIKE '%".trim($firstname)."%'";
	if($lastname != null)
		$sql= $sql." AND user.lastname LIKE '%".trim($lastname)."%'";
	if($id != null && $id != "")
		$sql= $sql." AND user.id=".$id;
	
        $sql= $sql." order by username ASC";
	$statement = $this->adapter->query($sql); 
	return $statement->execute(); 
    }
}
