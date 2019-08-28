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
class SolrManuscript extends SolrDefault
{
    // NEW FIELDS FOR SOLRMANUSCRIPT - ADDED BY SB174 ON 2018-07-09
	public function getSummary()
	{
		return isset($this->fields['summary']) ?
		$this->fields['summary'] : '';
	}
	
	public function getForm()
	{
		return isset($this->fields['form']) ?
		$this->fields['form'] : '';
	}
	
	public function getAvailability()
	{
		return isset($this->fields['availability']) ?
		$this->fields['availability'] : '';
	}
	
	public function getAvailabilityStatus()
	{
		return isset($this->fields['availability_status']) ?
		$this->fields['availability_status'] : '';
	}
	
	public function getFullRecord()
	{
		return isset($this->fields['fullrecord']) ?
		$this->fields['fullrecord'] : '';
	}
	
	public function getExtent()
	{
		return isset($this->fields['extent']) ?
		$this->fields['extent'] : '';
	}
	
	public function getLeafHeight()
	{
		return isset($this->fields['leaf_height']) ?
		$this->fields['leaf_height'] : '';
	}
	
	public function getLeafWidth()
	{
		return isset($this->fields['leaf_width']) ?
		$this->fields['leaf_width'] : '';
	}

	public function getWrittenHeight()
	{
		return isset($this->fields['written_height']) ?
		$this->fields['written_height'] : '';
	}
	
	public function getWrittenWidth()
	{
		return isset($this->fields['written_width']) ?
		$this->fields['written_width'] : '';
	}
	
	public function getHandDesc()
	{
		return isset($this->fields['hand_desc']) ?
		$this->fields['hand_desc'] : '';
	}
	
	public function getHandScope()
	{
		return isset($this->fields['hand_scope']) ?
		$this->fields['hand_scope'] : '';
	}
	
	public function getHandScript()
	{
		return isset($this->fields['hand_script']) ?
		$this->fields['hand_script'] : '';
	}
	
	public function getHandMedium()
	{
		return isset($this->fields['hand_medium']) ?
		$this->fields['hand_medium'] : '';
	}
	
	public function getAcquisition()
	{
		return isset($this->fields['acquisition']) ?
		$this->fields['acquisition'] : '';
	}
	
	public function getHistory()
	{
		return isset($this->fields['history']) ?
		$this->fields['history'] : '';
	}
	
	public function getTextLang()
	{
		return isset($this->fields['textLang']) ?
		$this->fields['textLang'] : '';
	}
	
	public function getNote()
	{
		return isset($this->fields['note']) ?
		$this->fields['note'] : '';
	}
	
	public function getIncipit()
	{
		return isset($this->fields['incipit']) ?
		$this->fields['incipit'] : '';
	}
		
	public function getExplicit()
	{
		return isset($this->fields['explicit']) ?
		$this->fields['explicit'] : '';
	}
	
	public function getColophon()
	{
		return isset($this->fields['colophon']) ?
		$this->fields['colophon'] : '';
	}
	
	public function getFiliation()
	{
		return isset($this->fields['filiation']) ?
		$this->fields['filiation'] : '';
	}
	
	public function getPagination()
	{
		return isset($this->fields['pagination']) ?
		$this->fields['pagination'] : '';
	}
	
	public function getArchiveCollection()
	{
		return isset($this->fields['archive_collection']) ?
		$this->fields['archive_collection'] : '';
	}
	
    // EXTANT FIELDS COPIED FROM SOLRARCHIVE.PHP - 2018-07-09
	//REFNO
	public function getClassmark()
	{
		return isset($this->fields['callnumber']) ?
		$this->fields['callnumber'] : '';
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
			$url = $protocol.'://'.$_SERVER['SERVER_NAME'].':8080/solr/biblio/select?q=collection%3A%22SOAS+Manuscripts%22+AND+hierarchy_top_id%3A%22'.$topID.'%22+AND+form%3A%22work%22&wt=xml&indent=true';
			$xml = simplexml_load_string(file_get_contents($url));
			$itemNumber = $xml->result["numFound"];
		}
		return $itemNumber;	
	}

}
