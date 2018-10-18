<!-- available fields are defined in solr/biblio/conf/schema.xml -->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
	xmlns:tei="http://www.tei-c.org/ns/1.0">
    <xsl:output method="xml" indent="yes" encoding="utf-8"/>
    <xsl:param name="institution">SOAS, University of London</xsl:param>
    <xsl:param name="collection">SOAS Manuscripts</xsl:param>
    <xsl:template match="tei:teiHeader">
	
		<add>
	
	<!-- CODEX -->
			<doc>
			    <!-- ID -->
                <field name="id">
                    <xsl:value-of select="tei:fileDesc/tei:publicationStmt/tei:idno[@type='msID']" />
                </field>

                <!-- RECORDTYPE -->
                <field name="recordtype">manuscript</field>

				<!-- FORMAT -->
                <field name="format">Manuscript</field>
						
                <!-- FULLRECORD -->
                <field name="fullrecord">
                    <xsl:value-of select="normalize-space(string(.))"/>
                </field>

                <!-- INSTITUTION -->
                <field name="institution">
                    <xsl:value-of select="$institution" />
                </field>

                <!-- COLLECTION -->
                <field name="collection">
                    <xsl:value-of select="$collection" />
                </field>
				
				<!-- SCB_LEVEL_FACET -->
				<field name="scb_level_facet">0/Codex</field>
				
				<!-- SUMMARY -->
				<xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msContents/tei:summary">
					<field name="summary">
						<xsl:value-of select="normalize-space(tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msContents/tei:summary)"/>
					</field>
				</xsl:if>
				
				<!-- CLASSMARK -->
				<xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msIdentifier/tei:idno">
					<field name="callnumber">
						<xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msIdentifier/tei:idno"/>
					</field>
				</xsl:if>
				
				<!-- TITLE -->
                <xsl:if test="tei:fileDesc/tei:titleStmt/tei:title">
					<field name="title">
						<xsl:value-of select="tei:fileDesc/tei:titleStmt/tei:title"/>
					</field>
					
					<field name="title_full">
						<xsl:value-of select="tei:fileDesc/tei:titleStmt/tei:title"/>
					</field>
					
					<field name="title_short">
						<xsl:value-of select="tei:fileDesc/tei:titleStmt/tei:title"/>
					</field>
					
  					<field name="title_sort">
						<xsl:value-of select="php:function('VuFind::stripArticles', string(tei:fileDesc/tei:titleStmt/tei:title))"/>
					</field>
                </xsl:if>
				
				<!-- PUBLISHER -->
                <xsl:if test="tei:fileDesc/tei:publicationStmt/tei:publisher">
                    <field name="publisher">
                        <xsl:value-of select="tei:fileDesc/tei:publicationStmt/tei:publisher"/>
                    </field>
                </xsl:if>
				
				<!-- PHYSICAL DESCRIPTION -->
 				<xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc">
                    <field name="form">
                        <xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/@form"/>
                    </field>
					
					<field name="material">
						<xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/@material"/>
					</field>
				
					<field name="extent">
						<xsl:value-of select="normalize-space(tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent)"/>
					</field>
					
					<xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent/tei:dimensions[@type='leaf']/tei:height">			
						<field name="leaf_height">
							<xsl:value-of select="concat(tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent/tei:dimensions[@type='leaf']/tei:height,' ',tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent/tei:dimensions[@type='leaf']/@unit)"/>
						</field>
					</xsl:if>
					
					<xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent/tei:dimensions[@type='leaf']/tei:width">		
						<field name="leaf_width">
							<xsl:value-of select="concat(tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent/tei:dimensions[@type='leaf']/tei:width,' ',tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent/tei:dimensions[@type='leaf']/@unit)"/>
						</field>
					</xsl:if>
					
					<xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent/tei:dimensions[@type='written']/tei:height">				
						<field name="written_height">
							<xsl:value-of select="concat(tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent/tei:dimensions[@type='written']/tei:height,' ',tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent/tei:dimensions[@type='written']/@unit)"/>
						</field>
					</xsl:if>
					
					<xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent/tei:dimensions[@type='written']/tei:width">	
						<field name="written_width">
							<xsl:value-of select="concat(tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent/tei:dimensions[@type='written']/tei:width,' ',tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:objectDesc/tei:supportDesc/tei:extent/tei:dimensions[@type='written']/@unit)"/>
						</field>
					</xsl:if>
                </xsl:if>
				
				<!-- HAND --> 
				<xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:handDesc">
                    <field name="hand_scope">
                        <xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:handDesc/tei:handNote/@scope"/>
                    </field>
					
					<field name="hand_script">
                        <xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:handDesc/tei:handNote/@script"/>
                    </field>
					
					<field name="hand_medium">
                        <xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:handDesc/tei:handNote/@medium"/>
                    </field>
					
					<field name="hand_desc">
                        <xsl:value-of select="normalize-space(tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:physDesc/tei:handDesc/tei:handNote/tei:desc)"/>
                    </field>
                </xsl:if>
		
				<!-- HISTORY -->
				<xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:additional/tei:adminInfo/tei:recordHist">
                    <field name="history">
                        <xsl:value-of select="normalize-space(tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:additional/tei:adminInfo/tei:recordHist/tei:source)"/>
                    </field>
                </xsl:if>
				
				<xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:history/tei:acquisition">
                    <field name="acquisition">
                        <xsl:value-of select="normalize-space(tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:history/tei:acquisition)"/>
                    </field>
                </xsl:if>
				
				<!-- AVAILABILITY -->
				<xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:additional/tei:adminInfo/tei:availability">
                    <field name="availability">
                        <xsl:value-of select="normalize-space(tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:additional/tei:adminInfo/tei:availability)"/>
                    </field>
					
					<field name="availability_status">
                        <xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:additional/tei:adminInfo/tei:availability/@status"/>
                    </field>
                </xsl:if>
				
				<!-- SUBJECTS -->
				<xsl:for-each select="tei:fileDesc/tei:profileDesc/tei:textClass/tei:keywords/tei:list/tei:item">
                    <field name="topic">
                        <xsl:value-of select="tei:ref/tei:term"/>
                    </field>
					
					<field name="topic_unstemmed">
                        <xsl:value-of select="tei:ref/tei:term"/>
                    </field>
					
					<field name="topic_facet">
                        <xsl:value-of select="tei:ref/tei:term"/>
                    </field>
					
					<field name="topic_browse">
                        <xsl:value-of select="tei:ref/tei:term"/>
                    </field>
                </xsl:for-each>
				
				<!-- HIERARCHY -->
				<field name="hierarchytype">Default</field>		

				<field name="hierarchy_top_id">
					<xsl:value-of select="tei:fileDesc/tei:publicationStmt/tei:idno[@type='msID']" />
				</field>

				<field name="hierarchy_top_title">
					<xsl:value-of select="tei:fileDesc/tei:titleStmt/tei:title" />
				</field>
				
				<field name="is_hierarchy_id">
					<xsl:value-of select="tei:fileDesc/tei:publicationStmt/tei:idno[@type='msID']" />
				</field>
				
				<field name="is_hierarchy_title">
					<xsl:value-of select="tei:fileDesc/tei:titleStmt/tei:title"/>
				</field>
				
				<field name="hierarchy_sequence">
					<xsl:value-of select="tei:fileDesc/tei:publicationStmt/tei:idno[@type='msID']" />
				</field>
			</doc>
	
	<!-- ITEMS -->
	<xsl:for-each select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msContents/tei:msItem">
		
			<doc>
                <!-- ID -->
                <field name="id">
                    <xsl:value-of select="@xml:id" />
                </field>

                <!-- RECORDTYPE -->
                <field name="recordtype">manuscript</field>
				
				<!-- FORMAT -->
                <field name="format">Manuscript</field>

                <!-- FULLRECORD -->
                <field name="fullrecord">
                    <xsl:copy-of select="normalize-space(string(//tei:teiHeader))"/>
                </field>

                <!-- INSTITUTION -->
                <field name="institution">
                    <xsl:value-of select="$institution" />
                </field>

                <!-- COLLECTION -->
                <field name="collection">
                    <xsl:value-of select="$collection" />
                </field>
				
				<!-- SCB_LEVEL_FACET -->
				<field name="scb_level_facet">1/Item</field>
				
                <!-- ITEM NO. -->
                <field name="item_number">
                    <xsl:value-of select="@xml:n" />
                </field>
				
				<!-- TITLE -->
                <xsl:if test="tei:title">
					<field name="title">
						<xsl:value-of select="tei:title"/>
					</field>
					
					<field name="title_full">
						<xsl:value-of select="tei:title"/>
					</field>
					
					<field name="title_short">
						<xsl:value-of select="tei:title"/>
					</field>
					
  					<field name="title_sort">
						<xsl:value-of select="php:function('VuFind::stripArticles', string(tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msContents/tei:msItem/tei:title))"/>
					</field>
					
					<xsl:if test="tei:title[@type='alt']">
						<field name="title_alt">
							<xsl:value-of select="tei:title[@type='alt']"/>
						</field>
					</xsl:if>
					
					<xsl:if test="tei:title[@xml:lang='ar']">
						<field name="linked_title">
							<xsl:value-of select="tei:title[@xml:lang='ar']"/>
						</field>
					</xsl:if>
					
					<xsl:if test="tei:title[@xml:lang='ar' and @type='alt']">
						<field name="linked_title_alt">
							<xsl:value-of select="tei:title[@xml:lang='ar' and @type='alt']"/>
						</field>
					</xsl:if>
					
                </xsl:if>
				
				<!-- AUTHOR -->
                <xsl:if test="tei:author/tei:persName">
					<field name="author">
						<xsl:value-of select="tei:author/tei:persName"/>
					</field>
				</xsl:if>
				
				<xsl:if test="tei:author/tei:persName[@xml:lang='ar']">
					<field name="linked_author">
						<xsl:value-of select="tei:author/tei:persName[@xml:lang='ar']"/>
					</field>
				</xsl:if>
				
				<!-- INCIPIT -->
				<xsl:if test="tei:incipit">
					<field name="incipit">
						<xsl:value-of select="tei:incipit"/>
					</field>
				</xsl:if>
				
				<!-- COLOPHON -->
				<xsl:if test="tei:colophon">
					<field name="colophon">
						<xsl:value-of select="tei:colophon"/>
					</field>
				</xsl:if>
				
				<!-- AVAILABILITY -->
 				<xsl:if test="//tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:additional/tei:adminInfo/tei:availability">
                    <field name="availability">
                        <xsl:value-of select="normalize-space(//tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:additional/tei:adminInfo/tei:availability)"/>
                    </field>
                </xsl:if>
				
				<xsl:if test="//tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:additional/tei:adminInfo/tei:availability/@status">
                    <field name="availability_status">
                        <xsl:value-of select="//tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:additional/tei:adminInfo/tei:availability/@status"/>
                    </field>
                </xsl:if>

				<!-- HIERARCHY -->
				<field name="hierarchytype">Default</field>		

				<field name="hierarchy_top_id">
					<xsl:value-of select="//tei:fileDesc/tei:publicationStmt/tei:idno[@type='msID']" />
				</field>

				<field name="hierarchy_top_title">
					<xsl:value-of select="//tei:fileDesc/tei:titleStmt/tei:title" />
				</field>
				
				<field name="hierarchy_parent_id">
					<xsl:value-of select="//tei:fileDesc/tei:publicationStmt/tei:idno[@type='msID']" />
				</field>
				
				<field name="hierarchy_parent_title">
					<xsl:value-of select="//tei:fileDesc/tei:titleStmt/tei:title" />
				</field>
				
				<field name="is_hierarchy_id">
					<xsl:value-of select="@xml:id" />
				</field>
				
				<field name="is_hierarchy_title">
					<xsl:value-of select="tei:title"/>
				</field>
				
				<field name="hierarchy_sequence">
					<xsl:value-of select="@xml:id" />
				</field>
				
			</doc>
	</xsl:for-each>
	</add>
    </xsl:template>
</xsl:stylesheet>
