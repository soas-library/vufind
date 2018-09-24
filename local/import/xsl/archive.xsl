<!-- available fields are defined in solr/biblio/conf/schema.xml -->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xmlns:xlink="http://www.w3.org/2001/XMLSchema-instance">
    <xsl:output method="xml" indent="yes" encoding="utf-8"/>
    <xsl:param name="institution">SOAS, University of London</xsl:param>
    <xsl:param name="collection">SOAS Archive</xsl:param>
    <xsl:template match="DScribeRecord">

        <add>
            <doc>
                <!-- ID -->
                <!-- Important: This relies on an <identifier> tag being injected by the OAI-PMH harvester. -->       
                <!-- COLLECTION -->
                <field name="collection">SOAS Archive</field>
                
                <field name="id">
                        <xsl:value-of select="php:function('Archive::buidID', normalize-space(//RefNo))"/>
                </field>

                <!-- RECORDTYPE -->
                <field name="recordtype">archive</field>

				<!-- ITEM LOCATION BY DEFAULT -->	             
	             <field name="scb_item_location">Archive &amp; Special Collections</field>
				<field name="item_location">Archive &amp; Special Collections</field>
	             
	           <!-- ITEM LOCATION BY DEFAULT -->	             
	             <field name="scb_loan_type">Reference only</field>
	             
	             <!-- LOCATION BY DEFAULT -->
                <field name="scb_calm_location_display">Archive &amp; Special Collections</field>
    			
    			<!-- USERWRAPPED2 -->
                 <xsl:if test="//UserWrapped2">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_order_with">
	                        <xsl:value-of select="//UserWrapped2" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- REFNO -->
                <xsl:if test="//RefNo">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="callnumber">
	                        <xsl:value-of select="//RefNo" />
	                    </field>
	                    <field name="callnumber_txt">
	                        <xsl:value-of select="//RefNo" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                 
                <!-- ALTREFNO -->   
                <xsl:if test="//AltRefNo">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_alt_ref_no">
	                        <xsl:value-of select="//AltRefNo" />
	                    </field>
                    </xsl:if>
                </xsl:if>
				
				<!--ADDED BY sb174 2018-08-21 FOR VERSION Sept-2018-->
				<!-- PREFIX NUMBER -->
				<xsl:if test="//RefNo">
                 	<field name="prefix_number">
	                    <xsl:value-of select="php:function('Archive::getPrefixNumber', normalize-space(//RefNo))" />
	                </field>
                </xsl:if>
				<!--END 2018-08-21-->
                
                <!-- PREVIOUSNUMBER -->
                <xsl:if test="//PreviousNumbers">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_previous_numbers">
	                        <xsl:value-of select="//PreviousNumbers" />
	                    </field>
                    </xsl:if>
                </xsl:if>
               
               <!-- TITLE -->
                 <field name="title">
                    <xsl:value-of select="//Title[normalize-space()]"/>
                </field>
                <field name="title_short">
                    <xsl:value-of select="//Title[normalize-space()]"/>
                </field>
                <field name="title_full">
                    <xsl:value-of select="//Title[normalize-space()]"/>
                </field>
                <field name="title_sort">
                    <xsl:value-of select="php:function('VuFind::stripArticles', string(//Title[normalize-space()]))"/>
                </field>
                
               <!-- DATE -->
               <xsl:for-each select="//Date">
                    <xsl:if test="string-length(.) > 0">
		                <field name="scb_date_creation">
		                    <xsl:value-of select="."/>
		                </field>
	                </xsl:if>
                </xsl:for-each>
                
                <!-- LEVEL -->
                <xsl:if test="//Level !=''">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_level">
	                        <xsl:value-of select="php:function('Archive::getLevel', normalize-space(//Level))"/>
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <xsl:if test="//Level !=''">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_level_facet">
	                        <xsl:value-of select="php:function('Archive::getLevelFacet', normalize-space(//Level))"/>
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- EXTENT -->
                <xsl:if test="//Extent">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_extent">
	                        <xsl:value-of select="//Extent" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- CREATORNAME -->
                 <xsl:if test="//CreatorName">
                    <xsl:for-each select="//CreatorName">
                        <xsl:if test="normalize-space()">            
                            <xsl:if test="position()=1">
                                <field name="author">
                                    <xsl:value-of select="."/>
                                </field>
                                <field name="author-letter">
                                    <xsl:value-of select="."/>
                                </field>
                            </xsl:if>
                            <xsl:if test="position()>1">
                                <field name="author2">
                                    <xsl:value-of select="."/>
                                </field>
                            </xsl:if>
                        </xsl:if>
                    </xsl:for-each>
                </xsl:if>
                
                <!-- FORMAT -->
                <field name="format">Archive</field>
                
                <!-- ADMINHISTORY -->
                <xsl:if test="//AdminHistory">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_admin_history">
	                        <xsl:value-of select="//AdminHistory" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- CUSTODIALHISTORY -->
                <xsl:if test="//CustodialHistory">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_custodial_history">
	                        <xsl:value-of select="//CustodialHistory" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
				<!-- ACQUISITION -->
                <xsl:if test="//Acquisition">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_acquisition">
	                        <xsl:value-of select="//Acquisition" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- DESCRIPTION -->
                <xsl:if test="//Description">
                	<xsl:if test="string-length(.) > 0">
	                    <field name="description">
	                        <xsl:value-of select="//Description" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- APPRAISAL -->
                <xsl:if test="//Appraisal">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_appraisal">
	                        <xsl:value-of select="//Appraisal" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- ACCRUALS -->
                <xsl:if test="//Accruals">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_accruals">
	                        <xsl:value-of select="//Accruals" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
			   <!-- ARRANGEMENT -->
                <xsl:if test="//Arrangement">
                 <xsl:if test="string-length(.) > 0">
	                   <field name="scb_arrangement">
	                       <xsl:value-of select="//Arrangement" />
	                   </field>
                 </xsl:if>
               </xsl:if>
               
                <!-- DOCUMENT -->
                <xsl:if test="//Document">
                 <xsl:if test="string-length(.) > 0">
	                   <field name="scb_document">
	                       <xsl:value-of select="//Document" />
	                   </field>
                 </xsl:if>
               </xsl:if>
                
                <!-- ACCESSSTATUS -->
                <xsl:if test="//AccessStatus">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_access_status">
	                        <xsl:value-of select="//AccessStatus" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- CLOSEDUNTIL -->
                <xsl:if test="//ClosedUntil">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_closed_until">
	                        <xsl:value-of select="//ClosedUntil" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- ACCESSCONDITIONS -->
                <xsl:if test="//AccessConditions">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_conditions_gov_access">
	                        <xsl:value-of select="//AccessConditions" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- COPYRIGHT -->
                <xsl:if test="//Copyright">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_copyright">
	                        <xsl:value-of select="//Copyright" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- USERRESTRICTIONS -->
                <xsl:if test="//UseRestrictions">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_use_restrictions">
	                        <xsl:value-of select="//UseRestrictions" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- LANGUAGE -->
                <xsl:for-each select="//Language">
                    <xsl:if test="string-length(.) > 0">
                        <field name="language">
                            <xsl:value-of select="php:function('VuFind::mapString', normalize-space(string(.)), 'language_map_iso639-1.properties')"/>
                        </field>
                    </xsl:if>
                </xsl:for-each>
                
                 <!-- USERTEXT4 -->
                <xsl:if test="//UserText4">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_scripts_material">
	                        <xsl:value-of select="//UserText4" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                 <!-- USERTEXT3 -->
                <xsl:if test="//UserText3">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_file_number">
	                        <xsl:value-of select="//UserText3" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- PHYSICALDESCRIPTION -->
                <xsl:if test="//PhysicalDescription">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_physc_charac_tech_reqs">
	                        <xsl:value-of select="//PhysicalDescription" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- FINDINGAIDS -->
                <xsl:if test="//FindingAids">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_finding_aids">
	                        <xsl:value-of select="//FindingAids" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- ORIGINALS -->
                <xsl:if test="//Originals">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_originals">
	                        <xsl:value-of select="//Originals" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- COPIES -->
                <xsl:if test="//Copies">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_copies">
	                        <xsl:value-of select="//Copies" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- RELATEDMATERIAL -->
                <xsl:if test="//RelatedMaterial">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_related_material">
	                        <xsl:value-of select="//RelatedMaterial" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- PUBLNNOTE -->
                <xsl:if test="//PublnNote">
                 	<xsl:if test="string-length(.) > 0">
	                    <field name="scb_publications">
	                        <xsl:value-of select="//PublnNote" />
	                    </field>
                    </xsl:if>
                </xsl:if>
                
                <!-- NOTES -->          
                <xsl:for-each select="//Notes">
                    <xsl:if test="string-length(.) > 0">
		                <field name="note">
		                    <xsl:value-of select="."/>
		                </field>
	                </xsl:if>
                </xsl:for-each>  
                
                <!-- RULES -->          
                <xsl:for-each select="//Rules">
                    <xsl:if test="string-length(.) > 0">
		                <field name="scb_rules">
		                    <xsl:value-of select="//Rules" />
		                </field>
	                </xsl:if>
                </xsl:for-each>    
                
                <!-- DESCDATE -->          
                <xsl:for-each select="//DescDate">
                    <xsl:if test="string-length(.) > 0">
		                <field name="scb_date_description">
		                    <xsl:value-of select="//DescDate" />
		                </field>
	                </xsl:if>
                </xsl:for-each> 
                
               <!-- TERM -->  
               <xsl:for-each select="//Term">
 					<xsl:if test="string-length(.) > 0">
		                <field name="scb-callnumber-first">
		                    <xsl:value-of select="normalize-space()"/>
		                </field>
	                </xsl:if>
                </xsl:for-each> 
                
               <xsl:for-each select="//Term">
	                <xsl:if test="string-length(.) > 0">
		                <field name="topic">
		                    <xsl:value-of select="normalize-space()"/>
		                </field>
	                </xsl:if>
                </xsl:for-each>         
                                
                <!-- COLLECTION / HIERARCHY -->                
	               <field name="hierarchytype">Default</field>	                
	                
	               <field name="hierarchy_top_id">
	               		<xsl:if test="php:function('Archive::getTopParent', normalize-space(//RefNo))">
	               			<xsl:if test="string-length(.) > 0">
                        		<xsl:value-of select="php:function('Archive::getTopParent', normalize-space(//RefNo))"/>
                        	</xsl:if>
                        </xsl:if>
                    </field>
					
					<!--ADDED BY sb174 2018-08-21 FOR VERSION Sept-2018-->
					<field name="hierarchy_top_id_raw">
	               		<xsl:if test="php:function('Archive::getTopParent', normalize-space(//RefNo))">
	               			<xsl:if test="string-length(.) > 0">
                        		<xsl:value-of select="php:function('Archive::getTopParentRaw', normalize-space(//RefNo))"/>
                        	</xsl:if>
                        </xsl:if>
                    </field>
					<!--END 2018-08-21-->
                    
                    <field name="hierarchy_parent_id">
                      	<xsl:if test="php:function('Archive::getAboveParent', normalize-space(//RefNo))">
                      		<xsl:if test="string-length(.) > 0">
                        		<xsl:value-of select="php:function('Archive::getAboveParent', normalize-space(//RefNo))"/>
                        	</xsl:if>
                        </xsl:if>
                    </field> 
                    
                    <field name="hierarchy_top_title">
                    <xsl:if test="php:function('Archive::getTopTitle', normalize-space(//RefNo))">
                        	<xsl:value-of select="php:function('Archive::getTopTitle', normalize-space(//RefNo))"/>
                        </xsl:if>
                    </field>  
                    
                    <field name="hierarchy_top_title_browse">
                    <xsl:if test="php:function('Archive::getTopTitle', normalize-space(//RefNo))">
                        	<xsl:value-of select="php:function('Archive::getTopTitle', normalize-space(//RefNo))"/>
                        </xsl:if>
                    </field>  
                    
                     <field name="hierarchy_parent_title">
                    <xsl:if test="php:function('Archive::getAboveTitle', normalize-space(//RefNo))">
                        	<xsl:value-of select="php:function('Archive::getAboveTitle', normalize-space(//RefNo))"/>
                        </xsl:if>
                    </field>  
                    
                  <field name="is_hierarchy_id">
                  	<xsl:if test="php:function('Archive::getHierarchyID', normalize-space(//RefNo))">
                    	<xsl:value-of select="php:function('Archive::getHierarchyID', normalize-space(//RefNo))"/>
                    </xsl:if>
                  </field>  
                  
                  <field name="is_hierarchy_title">
                    <xsl:if test="php:function('Archive::getHierarchyTitle', normalize-space(//RefNo))">
                    	<xsl:value-of select="php:function('Archive::getHierarchyTitle', normalize-space(//RefNo))"/>
                    </xsl:if>
                  </field>  
                                   
	              <field name="hierarchy_sequence">
						<xsl:value-of select="php:function('Archive::buildHierarchySequence', normalize-space(//RefNo))"/>
	              </field>

            </doc>
        </add>
    </xsl:template>
</xsl:stylesheet>
