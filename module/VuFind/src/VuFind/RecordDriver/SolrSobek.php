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
}
