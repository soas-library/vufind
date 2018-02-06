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
class AccesspermissionsController extends AbstractAcadmin
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
        
        $view = $this->createViewModel();
        $view->setTemplate('acadmin/accesspermissions/home');
        
        $action = '0';
        
        if($_POST['save'] == "1"){
        	$array_ini_sections = parse_ini_file('/usr/local/vufind/local/config/vufind/access.ini', true);
        	
        	$array_types = [];
        	$array_ips = [];
        	$array_roles = [];
        	
        	foreach($array_ini_sections as $key => $value){
        		$array_types[]= $key;
	        	foreach($value as $key2 => $value2){
	        		if(strcmp($key2, 'ipRange') === 0) {
	        			$array_ips[]= $value2;
	        		}
	        	}
        	}
        	
        	for ($i = 0; $i < count($array_types); $i++) {
        		$array_roles[] = $_POST[$i];
        		$values['ipRange'] = $array_ips[$i];
        		$values['role'] = $array_roles[$i];
        		$types_values[$array_types[$i]] = $values;
        	}
        	
		    /* WRITE */
		    $path = '/usr/local/vufind/local/config/vufind/access.ini';
		    $has_sections=TRUE;
		    $content = ""; 
		    if ($has_sections) { 
		        foreach ($types_values as $key=>$elem) { 
		            $content .= "[".$key."]\n"; 
		            foreach ($elem as $key2=>$elem2) { 
		                if(is_array($elem2)) 
		                { 
		                    for($i=0;$i<count($elem2);$i++) 
		                    { 
		                        $content .= $key2."[] = \"".$elem2[$i]."\"\n"; 
		                    } 
		                } 
		                else if($elem2=="") $content .= $key2." = \n"; 
		                else $content .= $key2." = \"".$elem2."\"\n"; 
		            } 
		        } 
		    } 
		    else {
		        foreach ($types_values as $key=>$elem) { 
		            if(is_array($elem)) 
		            { 
		                for($i=0;$i<count($elem);$i++) 
		                { 
		                    $content .= $key."[] = \"".$elem[$i]."\"\n"; 
		                } 
		            } 
		            else if($elem=="") $content .= $key." = \n"; 
		            else $content .= $key." = \"".$elem."\"\n"; 
		        }
		    } 
		
		    if (file_exists($path)) {
		    	$writeDate = date ("F d Y H:i:s.", filemtime($path));
		    }

		$readDate = $_POST['readDate'];
		
		if(strcmp($writeDate,$readDate) == 0) {
		    if (!$handle = fopen($path, 'w')) { 
		        fclose($handle);
		        /* Save wrong */
		        $action = '2';
		    } else {
		    	$success = fwrite($handle, $content);
		    	fclose($handle);
		    	/* Save success */
		    	$action = '1';
		    }	
		} else {
			/* Save wrong */
			$action = '3'; 
		}
        }
        	    /* WRITE */
        
        $file = '/usr/local/vufind/local/config/vufind/access.ini';
        $array_ini_sections = parse_ini_file($file, true);
        $types_file = '/usr/local/vufind/local/config/vufind/accessTypes.ini';
        $array_ini_accessTypes = parse_ini_file($types_file, false);
        
            if (file_exists($file)) {
            	$readDate = date ("F d Y H:i:s.", filemtime($file));
            }
        
        $array_types = [];
        $array_roles = [];
        
        foreach($array_ini_sections as $key => $value){
        	$array_types[]= $key;
        	
        	foreach($value as $key2 => $value2){
        		if(strcmp($key2, 'role') === 0) {
        			$array_roles[]= $value2;
        		}
        	}
        	
        }
        
        $view->action = $action;
        $view->accessTypes = $array_ini_accessTypes['accessTypes'];
        $view->types = $array_types;
        $view->roles = $array_roles;
        $view->readDate = $readDate;
        
        return $view;
    }
    
}
