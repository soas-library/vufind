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
class Location extends Gateway
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('location', 'VuFindAcadmin\Db\Row\Location');
    }

       /**
     * Get all locations.
     *
     * @return array
     */
    public function getAll() {
    	$callback = function ($select) {};
    	return $this->select($callback);
        	
    	 //$callback =  function ($select) use ($id_location) {
    	//	$select->join(array('level' => 'level'),'location.id_level = level.id_level',array('name' => 'level_name'));
    	//};
    	//return $this->select($callback);
    }
    
  /*  public function getByClassmark($id_classmark)
    {
    	$callback = function ($select) use ($id_classmark) {
    		$select->where->equalTo('id_classmark',$id_classmark);
    	};
    	return $this->select($callback);
    }
    */
    public function getByID($id_location)
    {
    	/*$row = $this->select(['id_location' => $id_location])->current();
        return $row;
        */
        $callback =  function ($select) use ($id_location) {
    		$select->where->equalTo('id_location',$id_location);
    	};
    	return $this->select($callback)->current();
    }
    
   public function getByClassmark($id_classmark){
    	$sql="select classmark.id_classmark as id_classmark, classmark.name as classmark_name, classmark.note as note, location.*, level.name as level_name
		from classmark
 		inner join location on classmark.id_classmark = location.id_classmark
 		inner join level on location.id_level = level.id_level
 		";

	$sql =$sql." where 1=1";
	
	if($id_classmark != null)
		$sql= $sql." AND classmark.id_classmark =".$id_classmark ;
	
	$sql= $sql." order by ISNULL(ordFirstLetter) ASC, ordFirstLetter ASC, classmark.name ASC";
	
	$statement = $this->adapter->query($sql); 
	return $statement->execute(); 
    }
    
    
    public function getByLevel($id_level){
    	$sql="select classmark.id_classmark as id_classmark, classmark.name as classmark_name, classmark.note as note, location.*, level.name as level_name
		from classmark
 		inner join location on classmark.id_classmark = location.id_classmark
 		inner join level on location.id_level = level.id_level
 		";

	$sql =$sql." where 1=1";
	
	if($id_level != null)
		$sql= $sql." AND level.id_level =".$id_level ;
	
        $sql= $sql." order by ISNULL(ordLibraryLocation), ordLibraryLocation ASC, classmark.name ASC";
        
	$statement = $this->adapter->query($sql); 
	return $statement->execute(); 
    }
    
   
    public function reorderDownOrdFirstLetter($previousOrdFirstLetter,$currentOrdFirstLetter,$id_location,$id_classmark){
    	$sql="UPDATE location set ordFirstLetter=ordFirstLetter-1 WHERE ordFirstLetter>=".$previousOrdFirstLetter." AND ordFirstLetter<=".$currentOrdFirstLetter." AND id_location !=".$id_location." AND id_classmark=".$id_classmark.";";
    	
	$statement = $this->adapter->query($sql); 
	return $statement->execute(); 
    }
    
     public function reorderUpOrdFirstLetter($previousOrdFirstLetter,$currentOrdFirstLetter,$id_location){
    	$sql="UPDATE location set ordFirstLetter=ordFirstLetter+1 WHERE ordFirstLetter>=".$currentOrdFirstLetter." AND ordFirstLetter<=".$previousOrdFirstLetter." AND id_location !=".$id_location;

	$statement = $this->adapter->query($sql); 
	return $statement->execute(); 
    }
    
    public function maxOrdFirstLetter($id_classmark, $id_location){
    	$sql="SELECT MAX(ordFirstLetter) AS maxOrdFirstLetter FROM location WHERE id_location<>".$id_location." id_classmark=".$id_classmark;

	$statement = $this->adapter->query($sql); 
	return $statement->execute()->current(); 
    }
   
}
