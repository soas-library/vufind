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
class UserpermissionsController extends AbstractAcadmin
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
        
        if (isset($_GET['id'])) {
    		$id = $_GET['id'];
    		$users = $userTable->getFilterUsers("","","",$id);    		
    	}
        
	if(isset($_POST['btnFind'])){//Find users
		$username = $_POST['username'];
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname']; 	
		$users = $userTable->getFilterUsers($username, $firstname, $lastname);	
		if(count($users)>20){
			$users = null;
			$this->flashMessenger()->setNamespace('error')->addMessage($this->translate('The result list is too long. Try to adjust your search.'));	
		}
		else if(count($users)==0){
			$this->flashMessenger()->setNamespace('error')->addMessage($this->translate('No result found.'));
		}
	}

        $view = $this->createViewModel(array('username'=>$username,'firstname'=>$firstname,'lastname'=>$lastname,'users'=>$users));
        $view->setTemplate('acadmin/userpermissions/home');

        return $view;
    }
    
    
     public function userpermissionAction()
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
        
        if (isset($_GET['id'])) {
    		$id = $_GET['id'];
    	}
    	else if (isset($_POST['id'])) {
    		$id = $_POST['id'];
    	}
    	
    	if(!empty($id) && $id != "")
    		$user = $userTable->getByID($id);
	$profileTable = $this->getTable('Profile');
	$profiles = $profileTable->getAll();
	
	
	 
	if(isset($_POST['modify'])){//Update 
		echo "dentro1";
		if($id != ""){ //UPDATE	
			echo "dentro2";
		    	if (!$_POST['profiles'] == ""){
		    		echo "dentro3";
		    		$profileSelected = $_POST['profiles'];		    		
		    		if($profileSelected != ""){
		    			echo "dentro4";		    		 
		    	 		$userTable->update(array('id_profile' => $profileSelected), array('id' => $id));
		    	 		 $this->flashMessenger()->addMessage($this->translate('The user has been modified successfully'), 'success');
		    	 		return $this->redirect()->tourl('Userpermissions?id='.$id);
		    		}
		    	}
		}
	}
	

        $view = $this->createViewModel(array('profiles'=>$profiles, 'user'=>$user));
        $view->setTemplate('acadmin/userpermissions/userpermission');

        return $view;
    }
}




/*

$userTable = $this->getTable('User');
        $user = $userTable->getByBarcode($patron['cat_username']);


        $userTable = $this->getTable('User');
        $user = $userTable->getByBarcode($patron['cat_username']);
        $username = array();
        foreach ($userTable->getAll() as $row) {
           $username[$row->username]['username']=str_replace('@soas.ac.uk','',$row->username);
           $username[$row->username]['firstname']=$row->firstname;
           $username[$row->username]['lastname']=$row->lastname;
        }
        //print_r($username);

        if(!empty($user)){
                $id_profile= $user['id_profile'];
                if($id_profile != $GLOBALS['ADMIN_PROFILE']){
                         return $this->forwardTo('MyResearch', 'Login');
                }
        }

        $view = $this->createViewModel(array('username'=>$username));
        $view->setTemplate('acadmin/userpermissions/home');

        return $view;
        
*/