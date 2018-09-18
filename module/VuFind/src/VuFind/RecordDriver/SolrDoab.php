<?php
/**
 * Default model for Solr records -- used when a more specific model based on
 * the recordtype field cannot be found.
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
use VuFind\Code\ISBN, VuFind\View\Helper\Root\RecordLink;

/**
 * Default model for Solr records -- used when a more specific model based on
 * the recordtype field cannot be found.
 *
 * This should be used as the base class for all Solr-based record models.
 *
 * @category VuFind2
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_drivers Wiki
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class SolrDoab extends SolrDefault
{
    // NEW FIELDS FOR SOLRDOAB - ADDED BY SB174 ON 2018-09-18
	public function getRights()
    {
        return isset($this->fields['rights'])
            ? $this->fields['rights'] : '';
    }
	# END 2018-09-18

    public function userPermissions($access)
    {
    	$userType = "OPAC";
    	//$browser_ip = $_SERVER['REMOTE_ADDR'];

	$browser_ip = '';
	    if ($_SERVER['HTTP_CLIENT_IP'])
        $browser_ip = $_SERVER['HTTP_CLIENT_IP'];
    	    else if($_SERVER['HTTP_X_FORWARDED_FOR'])
        $browser_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    else if($_SERVER['HTTP_X_FORWARDED'])
        $browser_ip = $_SERVER['HTTP_X_FORWARDED'];
	    else if($_SERVER['HTTP_FORWARDED_FOR'])
        $browser_ip = $_SERVER['HTTP_FORWARDED_FOR'];
	    else if($_SERVER['HTTP_FORWARDED'])
        $browser_ip = $_SERVER['HTTP_FORWARDED'];
	    else if($_SERVER['REMOTE_ADDR'])
        $browser_ip = $_SERVER['REMOTE_ADDR'];
	    else
        $browser_ip = 'UNKNOWN';    	

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
	    			if(ip2long($browser_ip) == ip2long($array_ini[$userType]['ipRange'][$i])) {
			    		return True;
			    	} else if (empty($array_ini[$userType]['ipRange'][$i])) {
			    		return True;
			    	}
	    		}
	    	}
    	}
    	return False;
    }

    public function getRegExpr($url, $active)
    {
    	$file = "local/config/vufind/electronicResources.ini";
    	$array_ini = parse_ini_file($file, true);
    	$array_sobek_regex = $array_ini['Archive']['regex'];
    	
    	if($active) {
	    	for($i=0; $i < count($array_sobek_regex); $i++ ) {
	    		$regExpr = $array_sobek_regex[$i];
	    		if (preg_match("/".$regExpr."/", $url) || strcmp($url, "Not available") == 0 ) {
	    			return False;
	    		}
	    	}
    	}
    	
    	return True;
    }

}
