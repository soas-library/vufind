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
class SolrArchive extends SolrDefault
{
	//USERWRAPPED2
	public function getOrderWith()
	{
		return isset($this->fields['scb_order_with']) ?
		$this->fields['scb_order_with'] : '';
	}
	//REFNO
	public function getClassmark()
	{
		return isset($this->fields['callnumber']) ?
		$this->fields['callnumber'] : '';
	}
	//ALTREFNO
	public function getAltRefNo()
	{
		return isset($this->fields['scb_alt_ref_no']) ?
		$this->fields['scb_alt_ref_no'] : '';
	}
	
	//PREVIOUSNUMBERS
	public function getPreviousNumbers()
	{
		return isset($this->fields['scb_previous_numbers']) ?
		$this->fields['scb_previous_numbers'] : '';
	}
	
	//TITLE
	
	//DATECREATION
	public function getDateCreation()
	{
		return isset($this->fields['scb_date_creation']) ?
		$this->fields['scb_date_creation'] : '';
	}
	//LEVEL
	public function getLevel()
	{
		return isset($this->fields['scb_level']) ?
		$this->fields['scb_level'] : '';
	}
	//EXTENT
	public function getExtent()
	{
		return isset($this->fields['scb_extent']) ?
		$this->fields['scb_extent'] : '';
	}
	
	//FORMAT
	
	//ADMINHISTORY
	public function getAdminHistory()
	{
		return isset($this->fields['scb_admin_history']) ?
		$this->fields['scb_admin_history'] : '';
	}
	//CUSTODIALHISTORY
	public function getCustodialHistory()
	{
		return isset($this->fields['scb_custodial_history']) ?
		$this->fields['scb_custodial_history'] : '';
	}
	//ACQUISITION
	public function getAcquisition()
	{
		return isset($this->fields['scb_acquisition']) ?
		$this->fields['scb_acquisition'] : '';
	}
	
	//DESCRIPTION
	
	//APPRAISAL
	public function getAppraisal()
	{
		return isset($this->fields['scb_appraisal']) ?
		$this->fields['scb_appraisal'] : '';
	}
	
	//ACCRUALS
	public function getAccruals()
	{
		return isset($this->fields['scb_accruals']) ?
		$this->fields['scb_accruals'] : '';
	}
		
	//ARRANGAMENT
	public function getArrangement()
	{
		return isset($this->fields['scb_arrangement']) ?
		$this->fields['scb_arrangement'] : '';
	}
	
	//DOCUMENT
	public function getDocument()
	{
		return isset($this->fields['scb_document']) ?
		$this->fields['scb_document'] : '';
	}
	
	//ACCESSSTATUS
	public function getAccessStatus()
	{
		return isset($this->fields['scb_access_status']) ?
		$this->fields['scb_access_status'] : '';
	}
	
	//CLOSEDUNTIL
	public function getClosedUntil()
	{
		return isset($this->fields['scb_closed_until']) ?
		$this->fields['scb_closed_until'] : '';
	}
	
	//ACCESSCONDTIONS
	public function getAccessConditions()
	{
		return isset($this->fields['scb_conditions_gov_access']) ?
		$this->fields['scb_conditions_gov_access'] : '';
	}
	
	//COPYRIGHT
	public function getCopyright()
	{
		return isset($this->fields['scb_copyright']) ?
		$this->fields['scb_copyright'] : '';
	}
	
	//USERESTRICTIONS
	public function getUseRestrictions()
	{
		return isset($this->fields['scb_use_restrictions']) ?
		$this->fields['scb_use_restrictions'] : '';
	}
	
	//LANGUAGE
	
	//SCRIPTSMATERIAL
	public function getScriptsMaterial()
	{
		return isset($this->fields['scb_scripts_material']) ?
		$this->fields['scb_scripts_material'] : '';
	}
	
	//FILENUMBER
	public function getFileNumber()
	{
		return isset($this->fields['scb_file_number']) ?
		$this->fields['scb_file_number'] : '';
	}
	
	//PHYSICALDESCRIPTION
	public function getPhysicalDescription()
	{
		return isset($this->fields['scb_physc_charac_tech_reqs']) ?
		$this->fields['scb_physc_charac_tech_reqs'] : '';
	}
	
	//FINDING AIDS
	public function getArchiveFindingAids()
	{
		return isset($this->fields['scb_finding_aids']) ?
		$this->fields['scb_finding_aids'] : '';
	}
	
	//ORIGINALS
	public function getOriginals()
	{
		return isset($this->fields['scb_originals']) ?
		$this->fields['scb_originals'] : '';
	}
	
	//COPIES
	public function getCopies()
	{
		return isset($this->fields['scb_copies']) ?
		$this->fields['scb_copies'] : '';
	}
	
	//RELATED MATERIAL
	public function getRelatedMaterial()
	{
		return isset($this->fields['scb_related_material']) ?
		$this->fields['scb_related_material'] : '';
	}
	
	//PUBLICACTIONS
	public function getPublications()
	{
		return isset($this->fields['scb_publications']) ?
		$this->fields['scb_publications'] : '';
	}
	
	//NOTES
		
	//RULES
	public function getRules()
	{
		return isset($this->fields['scb_rules']) ?
		$this->fields['scb_rules'] : '';
	}
	
	//DESCDATE
	public function getDescDate()
	{
		return isset($this->fields['scb_date_description']) ?
		$this->fields['scb_date_description'] : '';
	}
	
	//TERM
	
    //LOCATION
	public function getLocationDisplay()
	{
		return isset($this->fields['scb_calm_location_display']) ?
		$this->fields['scb_calm_location_display'] : '';
	}
	
	public function getTopParentTitle()
	{
		return isset($this->fields['hierarchy_parent_title']) ?
		$this->fields['hierarchy_parent_title'] : '';
	}
	
	public function getTopTitle()
	{
		return isset($this->fields['hierarchy_top_title']) ?
		$this->fields['hierarchy_top_title'] : '';
	}
	
	public function getTopID()
	{
		return isset($this->fields['hierarchy_top_id']) ?
		$this->fields['hierarchy_top_id'] : '';
	}
	
	
	
	
	public function getNumberItems()
	{
		$topIDAr=$this->getTopID();
		$topID = "";
		$itemNumber = 0;
		if($topIDAr != null && $topIDAr[0]!="")$topID=$topIDAr[0];	
		if($topID != null && $topID != ""){
			//$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
			//$url = $protocol.'://localhost:8080/solr/biblio/select?q=collection%3A%22SOAS+Archive%22+AND+hierarchy_top_id%3A%22'.$topID.'%22&wt=xml&indent=true';
			$protocol = "http";
			$url = $protocol.'://'.$_SERVER['SERVER_NAME'].':8080/solr/biblio/select?q=collection%3A%22SOAS+Archive%22+AND+hierarchy_top_id%3A%22'.$topID.'%22&wt=xml&indent=true';
			$xml = simplexml_load_string(file_get_contents($url));
			$itemNumber = $xml->result["numFound"];
			
		}
		return $itemNumber;	
	}
	
	public function getLoanType()
	{
		return isset($this->fields['scb_loan_type']) ?
		$this->fields['scb_loan_type'] : '';
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
