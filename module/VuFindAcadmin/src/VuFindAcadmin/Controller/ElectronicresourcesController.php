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
class ElectronicresourcesController extends AbstractAcadmin
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
        $view->setTemplate('acadmin/electronicresources/home');
        
        $file = '/usr/local/vufind/local/config/vufind/electronicResources.ini';
        $array_ini_sections = parse_ini_file($file, true);
        
        $array_tipos = [];
	$array_valores_regex = [];
	
	foreach($array_ini_sections as $key => $value){
	    $array_tipos[]= $key;
	    
	    foreach($value as $key2 => $value2){
	    	
	    	if(strcmp($key2, 'regex') === 0) {
	    		$array_valores_regex[]= $value2;
	    	}
	    	
	    }
	}
        
        $view->resource_names = $array_tipos;
        $view->regex_values = $array_valores_regex;
        
        return $view;
    }

    public function ResourceAction()
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
        
        $file = '/usr/local/vufind/local/config/vufind/electronicResources.ini';
        $array_ini_sections = parse_ini_file($file, true);
        
        if (file_exists($file)) {
        	$readDate = date ("F d Y H:i:s.", filemtime($file));
        }
        
        //print_r($array_ini_sections);
        
        $resource = $_GET['res'];
        
        if($_POST['delete'] == "1"){
        	/*print_r($_POST['check']);
        	print_r('</br>');
        	print_r($resource);
        	print_r('</br>');*/
        	
        	$array = $array_ini_sections[$resource];
        	
        	for($i=0; $i < count($_POST['check']); $i++ ) {
        		$pos_to_delete = $_POST['check'][$i];
        		//print_r($pos_to_delete);
        		unset($array['regex'][$pos_to_delete]);
        	}
        	
        	$array['regex'] = array_values($array['regex']);
        	$array_ini_sections[$resource] = $array;
        	//print_r($array_ini_sections);
        	
        	//Save in electronicResources.ini
        	
		    /* WRITE */
		    
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
		    /* WRITE */
        	
        }
        
        if($_POST['edit'] == "1"){
        	/*print_r($_POST['RegExp']);
        	print_r('</br>');
        	print_r($_POST['pos']);
        	print_r('</br>');*/
        	$array = $array_ini_sections[$resource];
        	$array['regex'][$_POST['pos']] = $_POST['RegExp'];
        	$array_ini_sections[$resource] = $array;
        	//print_r($array_ini_sections);
        	
        	//Save in electronicResources.ini
        	
		    /* WRITE */
		    
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
		    /* WRITE */
        	
        }
        
        if($_POST['add'] == "1"){
        	/*print_r($_POST['RegExp']);
        	print_r('</br>');*/
        	$array = $array_ini_sections[$resource];
        	$array['regex'][] = $_POST['RegExp'];
        	$array_ini_sections[$resource] = $array;
        	//print_r($array_ini_sections);
        	
        	//Save in electronicResources.ini
        	
		    /* WRITE */
		    
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
		    /* WRITE */
        	
        }
        
        $view->resource = $resource;
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
	        	$view->regex = $array_ini_sections[$resource]['regex'][$pos];
	        	$view->position = $pos;
	        	$view->act_type = 1;
	        // To show
	        } else {
	        	$view->regex = $array_ini_sections[$resource]['regex'];
	        	$view->act_type = 0;
	        }
	}
        
        $view->setTemplate('acadmin/electronicresources/resource');
        
        return $view;
    }

}
