<?php
/**
 * Model for MARC records in Solr.
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
namespace VuFind\RecordDriver;
use VuFind\Exception\ILS as ILSException,
    VuFind\View\Helper\Root\RecordLink,
    VuFind\XSLT\Processor as XSLTProcessor;

/**
 * Model for MARC records in Solr.
 *
 * @category VuFind2
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 */
class SolrSobek extends SolrMarc
{    
   
    public function getTag992a()
    {
        $results = $this->getFieldArray('992', array('a'));
        return $results;
    }
    public function getTag856u()
    {
        $results = $this->getFieldArray('856', array('u'));
        
        $item_sobek_txt = "";
	foreach($results as $item){$item_sobek_txt = $item;}
	if($item_sobek_txt == ""){		
		$item_sobek_txt = $this->buid856u();
	}
        return $item_sobek_txt;
    }
    
    public function buid856u()
    {
       $covers = $this->getTag992a();     
       $cover_txt = "";
       $buid_856u = "";
       foreach($covers as $cover){$cover_txt = $cover;}
 
       if($cover_txt != ""){
       		$cover_txt_ar = explode("/",$cover_txt);
       		$build_856u =$cover_txt_ar[0].'//'.$cover_txt_ar[2].'/'.$cover_txt_ar[4].$cover_txt_ar[5].$cover_txt_ar[6].$cover_txt_ar[7].$cover_txt_ar[8].'/'.$cover_txt_ar[9];       		
       }

       return $build_856u;
    }
    
     public function getSeriesSobek()
    {
        $matches = array();

        // First check the 440, 800 and 830 fields for series information:
        $primaryFields = array(
            '440' => array('a', 'p'));
          
        $matches = $this->getSeriesFromMARC($primaryFields);
        if (!empty($matches)) {
            return $matches;
        }

        // Now check 490 and display it only if 440/800/830 were empty:
        $secondaryFields = array('490' => array('a'));
        $matches = $this->getSeriesFromMARC($secondaryFields);
        if (!empty($matches)) {
            return $matches;
        }

        // Still no results found?  Resort to the Solr-based method just in case!
        return parent::getSeries();
    }
    
    public function determineUserType($ip) {

        $file = "local/config/vufind/access.ini";
        $array_ini = parse_ini_file($file, true);
        foreach ($array_ini as $type => $access_type) {
            $position = array_search($access_type['ipRange'] , $ip);
            $range = $access_type['ipRange'];
            foreach($range as $rangeAux){
	            if (strpos($rangeAux,$ip)!==false) {
	                //echo "Matching " .$ip . " and ". $type;
	                return $type;
	            }
	    }           
        }
        return "UNKNOWN";

    }
    
    public function userPermissions($access)
    {
    	$userType = "OPAC";
    	//$browser_ip = $_SERVER['REMOTE_ADDR'];

		$browser_ip = '';
	    if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)){
			$browser_ip = $_SERVER['HTTP_CLIENT_IP'];
		}
    	elseif (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)){
			$browser_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
	    elseif (array_key_exists('HTTP_X_FORWARDED', $_SERVER)){
			$browser_ip = $_SERVER['HTTP_X_FORWARDED'];
		}
	    elseif (array_key_exists('HTTP_FORWARDED_FOR', $_SERVER)){
			$browser_ip = $_SERVER['HTTP_FORWARDED_FOR'];
		}
	    elseif (array_key_exists('HTTP_FORWARDED', $_SERVER)){
			$browser_ip = $_SERVER['HTTP_FORWARDED'];
		}
	    elseif (array_key_exists('REMOTE_ADDR', $_SERVER)){
			$browser_ip = $_SERVER['REMOTE_ADDR'];
		}
	    else {
			$browser_ip = 'UNKNOWN';
		}	

        $userType = $this->determineUserType($browser_ip);
    	
    	/*print_r('Access: ' . $access);
    	print_r('</br>');
    	print_r('IP: ' . $browser_ip);
    	print_r('</br>');*/
    	
    	$file = "local/config/vufind/access.ini";
    	$array_ini = parse_ini_file($file, true);
    	
    	if (array_key_exists($userType,$array_ini) && in_array($access, $array_ini[$userType]['role'])) {
	    	for($i=0; $i < count($array_ini[$userType]['ipRange']); $i++) {
	    		//print_r($array_ini[$userType]['ipRange'][$i]);
	    		//print_r('</br>');
	    		if (strpos($array_ini[$userType]['ipRange'][$i], '-') !== false) {
	    			$ipRanges = explode("-", $array_ini[$userType]['ipRange'][$i]);
	    			//print_r($ipRanges);
		    		if (ip2long($ipRanges[0]) <= ip2long($browser_ip) && ip2long($browser_ip) <= ip2long($ipRanges[1])) {
		    			return True;
		    		}
	    		} else {
	    			/*echo $browser_ip;
	    			echo '</br>';
	    			echo $array_ini[$userType]['ipRange'][$i];
	    			echo '</br>';*/
	    			if(ip2long($browser_ip) == ip2long($array_ini[$userType]['ipRange'][$i])) {
			    		return True;
			    	} else if (empty($array_ini[$userType]['ipRange'][$i])) {
			    		return True;
			    	}
	    		}
	    	}
    	}
    	return false;
    }

    public function getRegExpr($url, $active)
    {
    	$file = "local/config/vufind/electronicResources.ini";
    	$array_ini = parse_ini_file($file, true);
    	$array_sobek_regex = $array_ini['Sobek']['regex'];
    	
    	if($active) {
	    	for($i=0; $i < count($array_sobek_regex); $i++ ) {
	    		$regExpr = $array_sobek_regex[$i];
	    		if (preg_match("/".$regExpr."/", $url) || strcmp($url, "Not available") == 0 ) {
	    			return False;
	    		}
	    	}
    	}
    	
    	return true;
    }
    
    
    public function get246()
    {
       $results = $this->getFieldArray('246', ['a'], false);
        return $results;
    }

    /**
     * Get an array of all 856 fields.
     *
     * @return array
     */
    public function getSolrUrl()
    {
        return isset($this->fields['url']) ?
            $this->fields['url'] : [];
    }

}
