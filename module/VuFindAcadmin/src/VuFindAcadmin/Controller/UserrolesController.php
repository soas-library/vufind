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
class UserrolesController extends AbstractAcadmin
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

        $profile = $this->getTable('Profile');
        $profileList = $profile->getAll();

        $view = $this->createViewModel(array('profileList'=>$profileList));
        $view->setTemplate('acadmin/userroles/home');

	return $view;
    }

   public function userroleAction()
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
        
 
     	$profileTable = $this->getTable('Profile');
     	
     	if (isset($_GET['id_profile'])) {
    		$id_profile = $_GET['id_profile'];
    	}
    	else if (isset($_POST['id_profile'])) {
    		$id_profile = $_POST['id_profile'];
    	}

    	if($id_profile != "")
    		$profile = $profileTable->getByID($id_profile);
    	
    	$description = $_POST['description'];	
    	if(isset($_POST['modify'])){//Update 
    			$profileTable->update(array('name' => $description),array('id_profile'=>$profile['id_profile']));
			$this->flashMessenger()->addMessage($this->translate('The user role has been updated successfully'), 'success');
			return $this->redirect()->tourl('Userroles');
	}else if(isset($_POST['create'])){
			$profileTable->insert(array('name' => $description));
			$this->flashMessenger()->addMessage($this->translate('The user role has been created successfully'), 'success');
			return $this->redirect()->tourl('Userroles');
	}

        $view = $this->createViewModel(array('profile' => $profile));
        $view->setTemplate('acadmin/userroles/userrole');
  
        return $view;     	
     }
     
     public function deleteuserroleAction()
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
        
     	$profileTable = $this->getTable('Profile');

	if (isset($_POST['check'])){
    		for ($i=0;$i<count($_POST['check']);$i++){
    			$profileTable->delete(array('id_profile' =>$_POST['check'][$i])); 
    		}
    		$this->flashMessenger()->addMessage($this->translate('The user roles have been deleted successfully'), 'success');
		return $this->redirect()->tourl('Userroles');
    	}
     	
     }

}
