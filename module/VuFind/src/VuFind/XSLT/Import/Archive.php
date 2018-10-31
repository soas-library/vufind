<?php
/**
 * XSLT importer support methods.
 *
 * PHP version 5
 *
 * Copyright (c) Demian Katz 2010.
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
 * @package  Import_Tools
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/importing_records Wiki
 */
namespace VuFind\XSLT\Import;
use DOMDocument, VuFind\Config\Locator as ConfigLocator;

/**
 * XSLT support class -- all methods of this class must be public and static;
 * they will be automatically made available to your XSL stylesheet for use
 * with the php:function() function.
 *
 * @category VuFind2
 * @package  Import_Tools
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/importing_records Wiki
 */
class Archive
{
	public static function buidID($ref)
	{
		$ref = str_replace("/",".",$ref);
		$ref = str_replace(" ","_",$ref);
		return $ref;
	}
	
	public static function getTopParent($ref)
	{
		$arIDs = explode("/", $ref);
		//if(count($arIDs)<2) return "";

		$id= $arIDs[0];
		$id = str_replace("/",".",$id);
		$id = str_replace(" ","_",$id);
		return $id;
	}
	
	#ADDED BY sb174 2018-08-21 FOR VERSION Sept-2018-->
	public static function getTopParentRaw($ref)
	{
		$arIDs = explode("/", $ref);
		//if(count($arIDs)<2) return "";

		$id= $arIDs[0];
		return $id;
	}
	
	public static function getPrefixNumber($ref)
	{
		$prefix_number = preg_replace('/^.*\/\s*/', '', $ref);
		return $prefix_number;
	}
	
	public static function buildHierarchySequence($ref)
	{
		$ref = str_replace("/",".00",$ref);
		$ref = str_replace(" ","_",$ref);
		return $ref;
	}
	#END 2018-08-21
	
	public static function getAboveParent($ref)
	{
		$arIDs = explode("/", $ref);
		$total = count($arIDs);
		if(count($arIDs)<2) return "";
		
		$id = "";
		if($total > 1){
			array_pop($arIDs);
			$id = implode(".", $arIDs);
		}
		else{
			$id= $arIDs[0];
		}
		$id = str_replace(" ","_",$id);
		
		return $id;
	}	
	
	
	public static function getTopTitle($ref){
		
		$obj = new Archive();
		$params1 = array($ref);
		$topID = call_user_func_array(array($obj, 'getTopParent'), $params1);
		
		
		//$topID = getTopParent($ref);
	
		if(strlen($topID)>0){
			$params2 = array($topID);
			return call_user_func_array(array($obj, 'getTitleFromID'), $params2);
		}
		
		
		return "";

	}
	
	public static function getAboveTitle($ref){
		
		$obj = new Archive();
		$params1 = array($ref);
		$parentID = call_user_func_array(array($obj, 'getAboveParent'), $params1);
		
	
		if(strlen($parentID)>0){
			$params2 = array($parentID);
			return call_user_func_array(array($obj, 'getTitleFromID'), $params2);
		}
	
	    return "";
	
	}
	
	
	public static function getHierarchyID($ref){
		$arIDs = explode("/", $ref);
		if(count($arIDs)!=1) return "";
		else{
			$obj = new Archive();
			$params1 = array($ref);
			$ID = call_user_func_array(array($obj, 'buidID'), $params1);
			return $ID;
		}
	}
	
	
	public static function getHierarchyTitle($ref){
		$arIDs = explode("/", $ref);
		if(count($arIDs)!=1) return "";
		else{
			$obj = new Archive();
			$params1 = array($ref);
			$ID = call_user_func_array(array($obj, 'buidID'), $params1);
			
			$params2 = array($ID);
			$title = call_user_func_array(array($obj, 'getTitleFromID'), $params2);
			return $title;
		}
	}
	
	
	public static function getTitleFromID($id)
	{
		$GLOBALS['PATH_ARCHIVE_XML'] = '/usr/local/vufind/archivecollections/';
		$GLOBALS['PATH_ARCHIVE_HARVEST'] = '/usr/local/vufind/local/harvest/Archive/';
		$GLOBALS['PATH_ARCHIVE_INFO'] = '/usr/local/vufind/local/harvest/Archive/info.txt';
		try{
			if(file_exists($GLOBALS['PATH_ARCHIVE_INFO'])){
				$file = fopen($GLOBALS['PATH_ARCHIVE_INFO'], "r");
				while(!feof($file))
				{
					$arr_row = explode ( "*****" , fgets($file));
					#Edited by sb174 on 2018-06-28
					#if(strpos($id,$arr_row[0])===false){}
					if((preg_match("/\b".$id."$\b/", $arr_row[0]))===0){}
					else{return trim($arr_row[1]);}
				}
				fclose($file);
			}
			return "";
		}catch(Exception $e){
			return "";
		}
	}
	
	
	public static function getLevel($level)
	{
		$levelAux = trim($level);
		if($level == "mFile" || $level == "file")$levelAux = "File";
		return $levelAux;
	}
	
	
	public static function getLevelSort($level)
	{
		$level_facet = "";
		$levelAux = trim($level);
		$level = trim($level);
		if($level == "mFile" || $level == "file")$levelAux = "File";
		
		if($level=="Collection")$level_facet = "0/Collection/";
		else if($level=="Sub-Collection")$level_facet = "1/Collection/Sub-Collection/";
		else if($level=="Sub-Sub-Collection")$level_facet = "2/Collection/Sub-Collection/Sub-Sub-Collection/";
		else if($level=="Sub-Sub-Sub-Collection")$level_facet = "3/Collection/Sub-Collection/Sub-Sub-Collection/Sub-Sub-Sub-Collection/";
		else if($level=="Series")$level_facet = "4/Collection/Sub-Collection/Sub-Sub-Collection/Sub-Sub-Sub-Collection/Series/";
		else if($level=="Sub-Series")$level_facet = "5/Collection/Sub-Collection/Sub-Sub-Collection/Sub-Sub-Sub-Collection/Series/Sub-Series/";
		else if($level=="Sub-Sub-Series")$level_facet = "6/Collection/Sub-Collection/Sub-Sub-Collection/Sub-Sub-Sub-Collection/Series/Sub-Series/Sub-Sub-Series/";
		else if($level=="File")$level_facet = "7/Collection/Sub-Collection/Sub-Sub-Collection/Sub-Sub-Sub-Collection/Series/Sub-Series/Sub-Sub-Series/File";
		else if($level=="Item")$level_facet = "8/Collection/Sub-Collection/Sub-Sub-Collection/Sub-Sub-Sub-Collection/Series/Sub-Series/Sub-Sub-Series/File/Item";
		else $level_facet = "7/Collection/Sub-Collection/Sub-Sub-Collection/Sub-Sub-Sub-Collection/Series/Sub-Series/Sub-Sub-Series/File";
		
		return $level_facet;
	}
}
	
