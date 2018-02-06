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
class LibrarylocationsController extends AbstractAcadmin
{
    /**
     * Admin home.
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
                if($id_profile != $GLOBALS['ADMIN_PROFILE']){
                         return $this->forwardTo('MyResearch', 'Login');
                }
        }

        $profile = $this->getTable('Level');
        $locationLibraryList = $profile->getAll();

        $view = $this->createViewModel(array('locationLibraryList'=>$locationLibraryList));
        $view->setTemplate('acadmin/librarylocations/home');

	return $view;
    }

   public function librarylocationAction()
     {
     	 // Stop now if the user does not have valid catalog credentials available:
       if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }
    
        $userTable = $this->getTable('User');
        $user = $userTable->getByBarcode($patron['cat_username']);

        if(!empty($user)){
                $id_profile= $user['id_profile'];
                if($id_profile != $GLOBALS['ADMIN_PROFILE']){
                         return $this->forwardTo('MyResearch', 'Login');
                }
        }
        
 
     	$levelTable = $this->getTable('Level');
     	
     	if (isset($_GET['id_level'])) {
    		$id_level = $_GET['id_level'];
    	}
    	else if (isset($_POST['id_level'])) {
    		$id_level = $_POST['id_level'];
    	}

    	if($id_level != "")
    		$librarylocation = $levelTable->getByID($id_level);
    	
    	$description = $_POST['description'];	
    	if(isset($_POST['modify'])){//Update 
    			$levelTable->update(array('name' => $description),array('id_level'=>$librarylocation['id_level']));
			$this->flashMessenger()->addMessage($this->translate('The library location has been updated successfully'), 'success');
			return $this->redirect()->tourl('Librarylocations');
	}else if(isset($_POST['create'])){
			$levelTable->insert(array('name' => $description));
			$this->flashMessenger()->addMessage($this->translate('The library location has been created successfully'), 'success');
			return $this->redirect()->tourl('Librarylocations');
	}

        $view = $this->createViewModel(array('librarylocation' => $librarylocation));
        $view->setTemplate('acadmin/librarylocations/librarylocation');
  
        return $view;     	
     }
     
     public function deletelibrarylocationAction()
     {
     	// Stop now if the user does not have valid catalog credentials available:
        if (!is_array($patron = $this->catalogLogin())) {
            return $patron;
        }

        $userTable = $this->getTable('User');
        $user = $userTable->getByBarcode($patron['cat_username']);

        if(!empty($user)){
                $id_profile= $user['id_profile'];
                if($id_profile != $GLOBALS['ADMIN_PROFILE']){
                         return $this->forwardTo('MyResearch', 'Login');
                }
        }
        
     	$levelTable = $this->getTable('Level');

	if (isset($_POST['check'])){
    		for ($i=0;$i<count($_POST['check']);$i++){
    			$levelTable->delete(array('id_level' =>$_POST['check'][$i])); 
    		}
    		$this->flashMessenger()->addMessage($this->translate('The library locations have been deleted successfully'), 'success');
		return $this->redirect()->tourl('Librarylocations');
    	}
     	
     }

}
