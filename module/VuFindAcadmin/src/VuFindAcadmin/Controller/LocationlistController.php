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

/**
 * Class controls VuFind administration.
 *
 * @category VuFind
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class LocationlistController extends AbstractAcadmin
{
    /**
     * Admin home.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function homeAction()
    {	
        $textTable = $this->getTable('Text');
        $text = $textTable->getByTextType(1);
        $description = "";
        if(isset($text))$description= $text['description'];
                  
        $classmarkTable = $this->getTable('Classmark');
        $classmarks = $classmarkTable->getAll();    	
        $locationTable = $this->getTable('Location');
     	$levelTable = $this->getTable('Level');
    	$levels = $levelTable->getAll();
    	$locations = $locationTable->getAll();
    	$id_location = "";
    	$id_classmark = "";
    	   	
    	$cm = "";
    	$ll = "";
    

	if (isset($_GET['cm'])) {
		$cm = $_GET['cm'];
		$arr_cm = explode("/",$cm);
		if($arr_cm[0] != ""){
			$cm = $arr_cm[0];	
    			foreach($locations as $locationsAux){
    				if( trim(strtolower($cm)) == trim(strtolower($locationsAux['name'])) ){$id_classmark=$locationsAux['id_classmark'];$classmark_name = $locationsAux['name'];}
    			}  
    			if($classmark_name == ""){
	    			foreach($locations as $locationsAux){//If there is no classmark compare only with firt letters 
	    				if( trim(substr(strtolower($locationsAux['name']),0,1)) == trim(substr(strtolower($cm),0,1))){$id_classmark=$locationsAux['id_classmark'];$classmark_name = $locationsAux['name'];}
	    			}  
    		        } 
    			if($classmark_name == ""){
	    			foreach($locations as $locationsAux){//If there is no classmark compare only with firt letters 
	    				if( trim(strpos(strtolower($locationsAux['name'])), trim(substr(strtolower($cm),0,2))) !== false){$id_classmark=$locationsAux['id_classmark'];$classmark_name = $locationsAux['name'];}
	    			}  
    		        } 	
		}
	}

	
	if (isset($_GET['ll'])) {
    		$ll = $_GET['ll'];
    		if($ll !=""){
    		foreach($levels as $levelAux){
			if(trim(strtolower($levelAux['name'])) == trim(strtolower(str_replace("xxxxx","&",$ll))) ){$id_level=$levelAux['id_level'];}}
    		}
    	}
    
    	if($id_level != "" && $id_classmark != "") //Library location and classmark	
		return $this->redirect()->tourl('LocationList/Location?ll='.$ll.'&cm='.$cm.'&id_level='.$id_level.'&id_classmark='.$id_classmark.'&order=2'.$classmark_name);
    	else if($id_level != "" && $id_classmark == "") //Library location
    		return $this->redirect()->tourl('LocationList/Location?ll='.$ll.'&cm='.$cm.'&id_level='.$id_level.'&id_classmark='.$id_classmark.'&order=2');
    	else if($id_level == "" && $id_classmark != "") //Classmark
		return $this->redirect()->tourl('LocationList/Location?ll='.$ll.'&cm='.$cm.'&id_level='.$id_level.'&id_classmark='.$id_classmark.'&order=1'.$classmark_name); //SCB-2017/11/23
        //None of them => Keep on Locationlist
    	    	
	
        $view = $this->createViewModel(array('description'=>$description,'levels'=>$levels,'classmarks' => $classmarks));

         return $view;
    }
    
    public function locationAction(){

        $classmarkTable = $this->getTable('Classmark');
        $classmarks = $classmarkTable->getAll();    	
        $locationTable = $this->getTable('Location');
     	$levelTable = $this->getTable('Level');
    	$levels = $levelTable->getAll();
    	$id_location = "";
    	$id_classmark = "";
    	   	
    	if (isset($_GET['id_level'])) {
    		$id_level = $_GET['id_level'];
    	}
    	else if (isset($_POST['id_level'])) {
    		$id_level = $_POST['id_level'];
    	}
    	
    	if($id_level != ""){
    		$level = $levelTable->getByID($id_level);
    		$note = $level['note'];
    	}
    	
    	if (isset($_GET['id_classmark'])) {
    		$id_classmark = $_GET['id_classmark'];
    	}
    	else if (isset($_POST['id_classmark'])) {
    		$id_classmark = $_POST['id_classmark'];
    	}
    	if($id_classmark != ""){
    		$classmark=$classmarkTable->getByID($id_classmark);
    		$note = $classmark['note'];
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

        $view = $this->createViewModel(array('levels'=>$levels,'classmarks' => $classmarks,'locations'=>$locations,'classmark'=>$classmark,'level'=>$level,'note'=>$note));
        $view->setTemplate('locationlist/location/home');
  
        return $view;
    }
     
}
