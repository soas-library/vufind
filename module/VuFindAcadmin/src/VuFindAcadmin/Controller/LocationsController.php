<?php
/**
 * Admin Controller
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace VuFindAcadmin\Controller;

$GLOBALS['OTHER_PROFILE'] = 1;
$GLOBALS['LIBRARIAN_PROFILE'] = 2;
$GLOBALS['ADMIN_PROFILE'] = 3;
$GLOBALS['ADMIN_LOCATION_PROFILE'] = 4;

/**
 * Class controls VuFind administration.
 *
 * @category VuFind
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class LocationsController extends AbstractAcadmin
{
    /**
     * Acadmin home.
     *
     * @return \Zend\View\Model\ViewModel
     */ 
     public function homeAction()
     { 
    	// Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }

        $userTable = $this->getTable('User');
        $user = $userTable->getByBarcode($patron['cat_username']);

        if(!empty($user)){
                $id_profile= $user['id_profile'];
                if($id_profile != $GLOBALS['ADMIN_PROFILE'] && $id_profile != $GLOBALS['ADMIN_LOCATION_PROFILE']){
                         return $this->forwardTo('MyResearch', 'Login');
                }
        }
 	
        $classmarkTable = $this->getTable('Classmark');
    	$classmarks = $classmarkTable->getAll();
    	$levelTable = $this->getTable('Level');
    	$levels = $levelTable->getAll();
    	
    	$locationTable = $this->getTable('Location');
    	   	
    	if (isset($_GET['id_classmark']) && $_GET['id_classmark']!="") {
    		$id_classmark = $_GET['id_classmark'];
    		$locations = $locationTable->getByClassmark($id_classmark);
    	}
    	else if (isset($_GET['id_level'])&& $_GET['id_level']!="") {		
    		$id_level = $_GET['id_level'];
    		$locations = $locationTable->getByLevel($id_level);
    	}
    	else{
    		$id_classmark = '1';
    		$locations = $locationTable->getByClassmark('1');
	}

	$classmark = $classmarkTable->getByID($id_classmark);
	$level = $levelTable->getByID($id_level);
	if(!empty($classmark))
		$note = $classmark['note'];
	else if(!empty($level))
		$note = $level['note'];
	
        $view = $this->createViewModel(array('levels'=>$levels,'classmarks' => $classmarks,'locations' => $locations,'id_classmark'=>$id_classmark, 'id_level'=>$id_level, 'note'=>$note));
        $view->setTemplate('acadmin/locations/home');
  
        return $view;
    }
    
    /**
     * Acadmin home. 
     *
     * @return \Zend\View\Model\ViewModel
     */
     public function locationAction()
     {
     	// Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }

        $userTable = $this->getTable('User');
        $user = $userTable->getByBarcode($patron['cat_username']);

        if(!empty($user)){
                $id_profile= $user['id_profile'];
                if($id_profile != $GLOBALS['ADMIN_PROFILE'] && $id_profile != $GLOBALS['ADMIN_LOCATION_PROFILE']){
                         return $this->forwardTo('MyResearch', 'Login');
                }
        }
        
     	$classmarkTable = $this->getTable('Classmark');
     	$levelTable = $this->getTable('Level');
    	$classmarks = $classmarkTable->getAll();
    	$levels = $levelTable->getAll();
    	$id_location = "";
    	$id_classmark = "";
    	$locationTable = $this->getTable('Location');
    	   	
    	if (isset($_GET['id_location'])) {
    		$id_location = $_GET['id_location'];
    	}
    	else if (isset($_POST['id_location'])) {
    		$id_location = $_POST['id_location'];
    	}
    	
    	if (isset($_GET['id_classmark'])) {
    		$id_classmark = $_GET['id_classmark'];
    	}
    	else if (isset($_POST['id_classmark'])) {
    		$id_classmark = $_POST['id_classmark'];
    	}

	if (isset($_GET['id_level'])) {
    		$id_level = $_GET['id_level'];
    	}
    	else if (isset($_POST['id_level'])) {
    		$id_level = $_POST['id_level'];
    	}

	if($id_location != ""){
		$location = $locationTable->getByID($id_location);
		$classmark= $classmarkTable->getByID($location['id_classmark']);
	}
	
	if(isset($_POST['modify'])){//Update 
		if($id_location != "") //UPDATE	
			if(!empty($location)){
				if (!$_POST['classmark'] == "")
		    			$name = $_POST['classmark'];
		    		if (!$_POST['level'] == "")
		    			$level = $_POST['level'];
		    		if (!$_POST['levels'] == "")
		    			$levelSelected = $_POST['levels'];
		    		if (!$_POST['classmarks'] == "")
		    				$classmarkSelected = $_POST['classmarks'];
		    		if (!$_POST['stack'] == "")
		    			$stack = $_POST['stack'];
		    		if (!$_POST['periodical_classmark'] == "")
		    			$periodical_name= $_POST['periodical_classmark'];
		    		if (!$_POST['periodical_level'] == "")
		    			$periodical_level = $_POST['periodical_level'];
		    		if (!$_POST['periodical_stack'] == "")
		    			$periodical_stack = $_POST['periodical_stack'];		    		
		    		$ordFirstLetter = $_POST['ordFirstLetter'];
		    		$ordLibraryLocation = $_POST['ordLibraryLocation'];		    		
				if ($_POST['ordFirstLetter'] == "")
		    				$ordFirstLetter = null;
		    		if ($_POST['ordLibraryLocation'] == "")
		    				$ordLibraryLocation = null;
		    		if($name != ""){//UPDATE
				      /*if(!empty($ordFirstLetter) && $ordFirstLetter != null && $ordFirstLetter != ""){
			    			//Detect if it has been moved up or down
			    			if($ordFirstLetter>$location['ordFirstLetter']){//DOWN
			    				echo "down";
							$locationTable->reorderDownOrdFirstLetter($location['ordFirstLetter'],$ordFirstLetter,$id_location,$location['id_classmark']);
							
			    			}
			    			else if($ordFirstLetter<$location['ordFirstLetter']){ //UP
			    				echo "up";
			    				$locationTable->reorderUpOrdFirstLetter($location['ordFirstLetter'],$ordFirstLetter,$id_location,$location['id_classmark']);
			    				$maxOrdFirstLetter = $locationTable->maxOrdFirstLetter();
			    					
			    			}
			    		}
			    		else{
			    			//ordFirstLetter will be MAX+1
			    			$maxOrdFirstLetter=$locationTable->maxOrdFirstLetter($location['id_classmark'],$location['id_location']);
			    			$ordFirstLetter = $maxOrdFirstLetter['maxOrdFirstLetter']+1;
			    			
			    		}
		    			*/		    				    			
		    			
		    	 		$locationTable->update(array('name' => $name,'level' => $level,'id_classmark'=>$classmarkSelected,'id_level'=>$levelSelected,'stack' => $stack,'periodical_name' => $periodical_name,'periodical_name' => $periodical_level,'periodical_stack' => $periodical_stack,'ordFirstLetter'=>$ordFirstLetter,'ordLibraryLocation'=>$ordLibraryLocation), array('id_location' => $id_location));

		    	 		 $this->flashMessenger()->addMessage($this->translate('The record has been modified successfully'), 'success');
		    	 		//return $this->redirect()->tourl('Location?id_location='.$id_location);
		    	 		return $this->redirect()->tourl('Locations?id_classmark='.$id_classmark.'&id_level='.$id_level);
		    		}
		    		
		    		else{//ERROR
		    			$this->flashMessenger()->setNamespace('error')->addMessage($this->translate('Class mark is required.'));
		    		}
		    		
			} 
		else{//INSERT
		}
	}
	else if(isset($_POST['create'])){
		
			

			    		$name = $_POST['classmark'];
			    		if (!$_POST['level'] == "")
			    			$level = $_POST['level'];
			    		if (!$_POST['levels'] == "")
		    				$levelSelected = $_POST['levels'];
		    			if (!$_POST['classmarks'] == "")
		    				$classmarkSelected = $_POST['classmarks'];
			    		if (!$_POST['stack'] == "")
			    			$stack = $_POST['stack'];
			    		if (!$_POST['periodical_classmark'] == "")
			    			$periodical_name= $_POST['periodical_classmark'];
			    		if (!$_POST['periodical_level'] == "")
			    			$periodical_level = $_POST['periodical_level'];
			    		if (!$_POST['periodical_stack'] == "")
			    			$periodical_stack = $_POST['periodical_stack'];
		    			$ordFirstLetter = $_POST['ordFirstLetter'];
		    			$ordLibraryLocation = $_POST['ordLibraryLocation'];
		    			if ($_POST['ordFirstLetter'] == "")
		    				$ordFirstLetter = null;
		    			if ($_POST['ordLibraryLocation'] == "")
		    				$ordLibraryLocation = null;

			    		if($name != ""){//UPDATE

			    	 		$locationTable->insert(array('id_classmark'=>$classmarkSelected,'name' => $name,'level' => $level,'id_level'=>$levelSelected,'stack' => $stack,'periodical_name' => $periodical_name,'periodical_name' => $periodical_level,'periodical_stack' => $periodical_stack,'ordFirstLetter'=>$ordFirstLetter,'ordLibraryLocation'=>$ordLibraryLocation));
			    	 		$this->flashMessenger()->addMessage($this->translate('The record has been added successfully'), 'success');	
			    	 		return $this->redirect()->tourl('Locations?id_classmark='.$id_classmark.'&id_level='.$id_level);
					 }			    		
					 else{//ERROR
					 	$this->flashMessenger()->setNamespace('error')->addMessage($this->translate('The name is required.'));
					 }
		
	}		
        $view = $this->createViewModel(array('levels'=>$levels,'classmarks' => $classmarks,'location'=>$location,'id_classmark'=>$id_classmark,'id_level'=>$id_level,'classmark'=>$classmark));
        $view->setTemplate('acadmin/locations/location');
  
        return $view;
    }
    
    
        /**
     * Acadmin home.
     *
     * @return \Zend\View\Model\ViewModel
     */
     public function classmarkAction()
     {
     	// Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }

        $userTable = $this->getTable('User');
        $user = $userTable->getByBarcode($patron['cat_username']);

        if(!empty($user)){
                $id_profile= $user['id_profile'];
                if($id_profile != $GLOBALS['ADMIN_PROFILE'] && $id_profile != $GLOBALS['ADMIN_LOCATION_PROFILE']){
                         return $this->forwardTo('MyResearch', 'Login');
                }
        }
        
     	$classmarkTable = $this->getTable('Classmark');
     	$levelTable = $this->getTable('Level');
    	$classmarks = $classmarkTable->getAll();
    	$levels = $levelTable->getAll();
    	$id_location = "";
    	$id_classmark = "";
    	$locationTable = $this->getTable('Location');
    	   	
    	if (isset($_GET['id_level'])) {
    		$id_level = $_GET['id_level'];
    	}
    	else if (isset($_POST['id_level'])) {
    		$id_level = $_POST['id_level'];
    	}
    	
    	if (isset($_GET['id_classmark'])) {
    		$id_classmark = $_GET['id_classmark'];
    	}
    	else if (isset($_POST['id_classmark'])) {
    		$id_classmark = $_POST['id_classmark'];
    	}
    	
    	if (isset($_GET['order'])) {
    		$order = $_GET['order'];
    	}
    	else if (isset($_POST['order'])) {
    		$order = $_POST['order'];
    	}

	if($order == 2){//library location
		$locations= $locationTable->getByLevel($id_level);
	}
	else{//first letter
		$locations= $locationTable->getByClassmark($id_classmark);
	}
	
		
	
	
        $view = $this->createViewModel(array('levels'=>$levels,'classmarks' => $classmarks,'locations'=>$locations,'id_level'=>$id_level,'id_classmark'=>$id_classmark,'classmark'=>$classmark));
        $view->setTemplate('acadmin/locations/classmark');
  
        return $view;
    }
    
    /**
     * Acadmin home.
     *
     * @return \Zend\View\Model\ViewModel
     */
     public function deleteAction()
     {
     	// Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }

        $userTable = $this->getTable('User');
        $user = $userTable->getByBarcode($patron['cat_username']);

        if(!empty($user)){
                $id_profile= $user['id_profile'];
                if($id_profile != $GLOBALS['ADMIN_PROFILE'] && $id_profile != $GLOBALS['ADMIN_LOCATION_PROFILE']){
                         return $this->forwardTo('MyResearch', 'Login');
                }
        }
        
     	$locationTable = $this->getTable('Location');
     	
     	
    	
    	if (isset($_GET['id_classmark'])) {
    		$id_classmark = $_GET['id_classmark'];
    	}
    	else if (isset($_POST['id_classmark'])) {
    		$id_classmark = $_POST['id_classmark'];
    	}
    	else
    		$id_classmark = 1;
    	
     	
  
	if (isset($_POST['check'])){
    		for ($i=0;$i<count($_POST['check']);$i++){
    			$locationTable->delete(array('id_location' =>$_POST['check'][$i])); 
    		}
    		$this->flashMessenger()->addMessage($this->translate('The records have been deleted successfully'), 'success');
		return $this->redirect()->tourl('Locations?id_classmark='.$id_classmark);
    	}
     	
     }
     
     
     /**
     * Acadmin home.
     *
     * @return \Zend\View\Model\ViewModel
     */
     public function noteAction()
     {
     	// Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }

        $userTable = $this->getTable('User');
        $user = $userTable->getByBarcode($patron['cat_username']);

        if(!empty($user)){
                $id_profile= $user['id_profile'];
                if($id_profile != $GLOBALS['ADMIN_PROFILE']  && $id_profile != $GLOBALS['ADMIN_LOCATION_PROFILE']){
                         return $this->forwardTo('MyResearch', 'Login');
                }
        }
        
     	$classmarkTable = $this->getTable('Classmark');     	
    	$classmarks = $classmarkTable->getAll();
    	$levelTable = $this->getTable('Level');
    	
    	if (isset($_GET['id_classmark'])) {
    		$id_classmark = $_GET['id_classmark'];
    	}
    	else if (isset($_POST['id_classmark'])) {
    		$id_classmark = $_POST['id_classmark'];
    	}
    	
    	if (isset($_GET['id_level'])) {
    		$id_level = $_GET['id_level'];
    	}
    	else if (isset($_POST['id_level'])) {
    		$id_level = $_POST['id_level'];
    	}
    	
    	if($id_classmark != "")
    		$classmark = $classmarkTable->getByID($id_classmark);
    	if($id_level != "")
    		$level = $levelTable->getByID($id_level);
    		
    	$note = $_POST['note'];	
    	if ($id_classmark != "" && isset($_POST['save'])){
    		$classmarkTable->update(array('note' => $note), array('id_classmark' => $id_classmark));
		$this->flashMessenger()->addMessage($this->translate('The note has been modified successfully'), 'success');
		return $this->redirect()->tourl('Locations?id_classmark='.$id_classmark);
    	}
    	else if ($id_level != "" && isset($_POST['save'])){
    		$levelTable->update(array('note' => $note), array('id_level' => $id_level));
		$this->flashMessenger()->addMessage($this->translate('The note has been modified successfully'), 'success');
		return $this->redirect()->tourl('Locations?id_level='.$id_level.'&order=2');
    	}

        $view = $this->createViewModel(array('level' => $level,'classmark' => $classmark));
        $view->setTemplate('acadmin/locations/note');
  
        return $view;     	
     }
     
      /**
     * Acadmin home.
     *
     * @return \Zend\View\Model\ViewModel
     */
     public function maintextAction()
     {
     	 // Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }

        $userTable = $this->getTable('User');
        $user = $userTable->getByBarcode($patron['cat_username']);

        if(!empty($user)){
                $id_profile= $user['id_profile'];
                if($id_profile != $GLOBALS['ADMIN_PROFILE']  && $id_profile != $GLOBALS['ADMIN_LOCATION_PROFILE']){
                         return $this->forwardTo('MyResearch', 'Login');
                }
        }
        
 
     	$textTable = $this->getTable('Text');
    	$text = $textTable->getByTextType(1);
    		
    	if (isset($_POST['save'])){
    		$description = $_POST['description'];
    		$textTable->update(array('description' => $description), array('id_text_type' => 1));
		$this->flashMessenger()->addMessage($this->translate('The location list main text has been modified successfully'), 'success');
		return $this->redirect()->tourl('Locations');
    	}
    	
        $view = $this->createViewModel(array('text' => $text));
        $view->setTemplate('acadmin/locations/maintext');
  
        return $view;     	
     }    
}
