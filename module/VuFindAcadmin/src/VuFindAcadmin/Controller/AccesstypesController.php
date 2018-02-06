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
class AccesstypesController extends AbstractAcadmin
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
        $view->setTemplate('acadmin/accesstypes/home');
        
        $file = '/usr/local/vufind/local/config/vufind/access.ini';
        $array_ini_sections = parse_ini_file($file, true);
        
        if (file_exists($file)) {
        	$readDate = date ("F d Y H:i:s.", filemtime($file));
        }
        
        if($_POST['delete'] == "1"){
        	
        	//print_r($_POST['check']);
        	//print_r('</br>');
        	
        	for($i=0; $i < count($_POST['check']); $i++ ) {
        		$pos_to_delete = $_POST['check'][$i];
        		//print_r($pos_to_delete);
        		//print_r('</br>');
        		unset($array_ini_sections[$pos_to_delete]);
        	}
        	
        	//print_r($array_ini_sections);
        	//print_r('</br>');
        	
        	//Save in access.ini
        	
		    
		    $has_sections=TRUE;
		    $content = ""; 
		    if ($has_sections) { 
		        foreach ($array_ini_sections as $key=>$elem) { 
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
		        foreach ($array_ini_sections as $key=>$elem) { 
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
		
		    if (file_exists($file)) {
		    	$writeDate = date ("F d Y H:i:s.", filemtime($file));
		    }
		
		$readDate = $_POST['readDate'];
		
		if(strcmp($writeDate,$readDate) == 0) {
		    if (!$handle = fopen($file, 'w')) { 
		        fclose($handle);
		        $action = '2'; 
		    } else {
		    	$success = fwrite($handle, $content);
		    	fclose($handle);
		        $action = '1';
		    }
		} else {
		        $action = '3'; 
		}
        	
        }
        
        if($_POST['add'] == "1"){
        	
        	/*print_r($_POST['name']);
        	print_r('</br>');
        	print_r($_POST['ip']);
        	print_r('</br>');
        	print_r($array_ini_sections);
        	print_r('</br>');*/
        	
        	$values['ipRange'][] = $_POST['ip'];
        	$values['role'] = '';
        	
        	/*print_r($values['ipRange']);
        	print_r('</br>');
        	print_r($values['role']);
        	print_r('</br>');*/
        	
        	$array_ini_sections[$_POST['name']] = $values;
        	/*print_r($array_ini_sections);
        	print_r('</br>');*/
        	
        	//Save in access.ini
        	
		    
		    $has_sections=TRUE;
		    $content = ""; 
		    if ($has_sections) { 
		        foreach ($array_ini_sections as $key=>$elem) { 
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
		        foreach ($array_ini_sections as $key=>$elem) { 
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
		
		    if (file_exists($file)) {
		    	$writeDate = date ("F d Y H:i:s.", filemtime($file));
		    }
		
		$readDate = $_POST['readDate'];
		
		if(strcmp($writeDate,$readDate) == 0) {
		    if (!$handle = fopen($file, 'w')) { 
		        fclose($handle);
		        $action = '2'; 
		    } else {
		    	$success = fwrite($handle, $content);
		    	fclose($handle);
		        $action = '1';
		    }
		} else {
		        $action = '3'; 
		}
        	
        }
        
        $view->readDate = $readDate;
        $view->action = $action;
        
        // To add
        $add = $_GET['add'];
        if($add != null) {
        	$view->act_type = 1;
        } else {
	        $array_ini_sections = parse_ini_file($file, true);
	        // To show
	        $array_tipos = [];
	        $array_valores_ips = [];
	        
	        foreach($array_ini_sections as $key => $value){
	        	$array_tipos[]= $key;
	        	foreach($value as $key2 => $value2){
	        		if(strcmp($key2, 'ipRange') === 0) {
	        			$array_valores_ips[]= $value2;
	        		}
	        	}
	        }
	        
	        $view->type_names = $array_tipos;
	        $view->ip_values = $array_valores_ips;
	        $view->act_type = 0;
	        
	}
	return $view;
    }

    public function TypeAction()
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
        
        $action = '0';
        
        $file = '/usr/local/vufind/local/config/vufind/access.ini';
        $array_ini_sections = parse_ini_file($file, true);
        
        if (file_exists($file)) {
        	$readDate = date ("F d Y H:i:s.", filemtime($file));
        }
        
        //print_r($array_ini_sections);
        
        $type = $_GET['t'];
        
        if($_POST['delete'] == "1"){
        	
        	/*print_r($_POST['check']);
        	print_r('</br>');
        	print_r($type);
        	print_r('</br>');
        	print_r($array_ini_sections[$type]);
        	print_r('</br>');*/
        	
        	$array = $array_ini_sections[$type];
        	
        	for($i=0; $i < count($_POST['check']); $i++ ) {
        		$pos_to_delete = $_POST['check'][$i];
        		//print_r($pos_to_delete);
        		unset($array['ipRange'][$pos_to_delete]);
        	}
        	
        	$array['ipRange'] = array_values($array['ipRange']);
        	$array_ini_sections[$type] = $array;
        	/*print_r($array_ini_sections);
        	print_r('</br>');*/
        	
        	//Save in access.ini
        	
		    
		    $has_sections=TRUE;
		    $content = ""; 
		    if ($has_sections) { 
		        foreach ($array_ini_sections as $key=>$elem) { 
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
		        foreach ($array_ini_sections as $key=>$elem) { 
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
		
		    if (file_exists($file)) {
		    	$writeDate = date ("F d Y H:i:s.", filemtime($file));
		    }
		
		$readDate = $_POST['readDate'];
		
		if(strcmp($writeDate,$readDate) == 0) {
		    if (!$handle = fopen($file, 'w')) { 
		        fclose($handle);
		        $action = '2'; 
		    } else {
		    	$success = fwrite($handle, $content);
		    	fclose($handle);
		        $action = '1';
		    }
		} else {
		        $action = '3'; 
		}
        	
        }
        
        if($_POST['edit'] == "1"){
        	
        	/*print_r($_POST['ip']);
        	print_r('</br>');
        	print_r($_POST['pos']);
        	print_r('</br>');
        	print_r($type);
        	print_r('</br>');
        	print_r($array_ini_sections);
        	print_r('</br>');*/
        	
        	$array = $array_ini_sections[$type];
        	$array['ipRange'][$_POST['pos']] = $_POST['ip'];
        	$array_ini_sections[$type] = $array;
        	/*print_r($array_ini_sections);
        	print_r('</br>');*/
        	
        	//Save in access.ini
        	
		    
		    $has_sections=TRUE;
		    $content = ""; 
		    if ($has_sections) { 
		        foreach ($array_ini_sections as $key=>$elem) { 
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
		        foreach ($array_ini_sections as $key=>$elem) { 
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
		
		    if (file_exists($file)) {
		    	$writeDate = date ("F d Y H:i:s.", filemtime($file));
		    }
		
		$readDate = $_POST['readDate'];
		
		if(strcmp($writeDate,$readDate) == 0) {
		    if (!$handle = fopen($file, 'w')) { 
		        fclose($handle);
		        $action = '2'; 
		    } else {
		    	$success = fwrite($handle, $content);
		    	fclose($handle);
		        $action = '1';
		    }
		} else {
		        $action = '3'; 
		}
        	
        }
        
        if($_POST['add'] == "1"){
        	
        	/*print_r($_POST['ip']);
        	print_r('</br>');
        	print_r($type);
        	print_r('</br>');
        	print_r($array_ini_sections);
        	print_r('</br>');*/
        	
        	$array = $array_ini_sections[$type];
        	$array['ipRange'][] = $_POST['ip'];
        	$array_ini_sections[$type] = $array;
        	/*print_r($array_ini_sections);
        	print_r('</br>');*/
        	
        	//Save in access.ini
        	
		    
		    $has_sections=TRUE;
		    $content = ""; 
		    if ($has_sections) { 
		        foreach ($array_ini_sections as $key=>$elem) { 
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
		        foreach ($array_ini_sections as $key=>$elem) { 
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
		
		    if (file_exists($file)) {
		    	$writeDate = date ("F d Y H:i:s.", filemtime($file));
		    }
		
		$readDate = $_POST['readDate'];
		
		if(strcmp($writeDate,$readDate) == 0) {
		    if (!$handle = fopen($file, 'w')) { 
		        fclose($handle);
		        $action = '2'; 
		    } else {
		    	$success = fwrite($handle, $content);
		    	fclose($handle);
		        $action = '1';
		    }
		} else {
		        $action = '3'; 
		}
        	
        }
        
        $view->type = $type;
        $view->readDate = $readDate;
        $view->action = $action;
        
        // To add
        $add = $_GET['add'];
        if($add != null) {
        	$view->act_type = 2;
        } else {
	        $array_ini_sections = parse_ini_file($file, true);
	        // To edit
	        $pos = $_GET['pos'];
	        if($pos != null) {
	        	$view->ips = $array_ini_sections[$type]['ipRange'][$pos];
	        	$view->position = $pos;
	        	$view->act_type = 1;
	        // To show
	        } else {
	        	$view->ips = $array_ini_sections[$type]['ipRange'];
	        	$view->act_type = 0;
	        }
	}
        
        $view->setTemplate('acadmin/accesstypes/type');
        return $view;
    }

}
