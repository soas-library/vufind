Open-source VuFind software [https://vufind-org.github.io/vufind/](https://vufind-org.github.io/vufind/) (developed by Villanova University's Falvey Memorial Library) is used as the OPAC and resource discovery service for SOAS Library. The main catalogue is available at the URL: [https://library.soas.ac.uk](https://library.soas.ac.uk)

This page collects notes and instructions on SOAS Library's VuFind search and discovery service.

For updates and support with VuFind, join the vufind-general@lists.sourceforge.net and the vufind-tech@lists.sourceforge.net mailing lists at [http://sourceforge.net/p/vufind/mailman/](http://sourceforge.net/p/vufind/mailman/). Thorough documentation and manuals on VuFind can be found at [https://vufind.org/wiki/](https://vufind.org/wiki/).

# 1.0: VuFind application

## 1.1: Servers

VuFind is installed on three servers for dev (vfdev01), UAT (vftest01), and live (vfprod01). 

The intended change management process is to develop new features or customisations on vfdev01, transfer the code to vftest01 for testing and UAT, and then transfer the code to vfprod01. 

### 1.1.1: Version control

GitHub is used for version control and transferring files. To commit changes on the dev server:

`git commit -a --author="SimonXIX <sb174@soas.ac.uk>" -m "Changes to header"`

`git push origin [BRANCH_NAME]`

To retrieve changes in the UAT server:

`git fetch origin`

`git reset --hard origin/[BRANCH_NAME]`

To merge changes from a dev branch into the master 'soas' branch, merge [BRANCH NAME] into soas via pull request: [https://help.github.com/articles/merging-a-pull-request/](https://help.github.com/articles/merging-a-pull-request/) 

New releases are thoroughly documented as part of the pull request. See [https://github.com/soas-library/vufind/pulls?q=is%3Apr+is%3Aclosed](https://github.com/soas-library/vufind/pulls?q=is%3Apr+is%3Aclosed).

### 1.1.2: Crontab 

The crontab on the VuFind server is under the root username. To see it:

`sudo -i`

Enter root password

`crontab -e`

## 1.2: Basic Linux

VuFind is installed on Linux servers and therefore requires at least a basic understanding of administration in a Linux environment and how to access Linux servers.

On Windows PCs, use PuTTY for access to VuFind servers: http://www.chiark.greenend.org.uk/~sgtatham/putty/

On Macs, use the built-in Terminal application.

For Linux server administration, the best text available is: Nemeth, E., et al, 2011. _Unix and Linux systems administration handbook_. Fourth Edition. Boston: Pearson Education, Inc. Buy or otherwise acquire a copy of that. See also: Shotts, W. E., 2013. _The Linux command line_. Second Internet Edition. This book is available under Creative Commons at [http://linuxcommand.org/tlcl.php](http://linuxcommand.org/tlcl.php) and is a gentler introduction to the more advanced command line functions in Linux.

In a pinch, there's a handy list of basic Unix commands here: [http://mally.stanford.edu/~sr/computing/basic-unix.html](http://mally.stanford.edu/~sr/computing/basic-unix.html) and here: [http://journal.code4lib.org/articles/9158](http://journal.code4lib.org/articles/9158)

Some good general commands to use to troubleshoot problems on the Linux servers:

* w - who is logged in at this moment. Load averages are fine up to about 5.

* top - Top is a load-checking program similar to Task Manager in Windows.

* htop - more advanced load-checking program. Shows virtual and real memory. Use F6 to sort the process list. Kill processes in a safe way by matching by PID in Innopac client. DON'T KILL ANYTHING USING TOP OR HTOP. IT ISN'T SAFE TO DO SO.

* ps -ef will give a quick view of processes running. Under a heavy load ps might work where htop doesn’t.

* df will show disk space.

* df -i will show available inodes.

* df -ih will show the memory load for every partition.

* ls -l | wc -l shows how many files are in the current directory.

* ls -lrt will show which user owns (and therefore probably created) those files.

* $ for i in /*; do echo $i; find $i |wc -l; done will list directories and number of files in them.

* du -smh * to check disk space

* free -m to show available RAM

## 1.3: VuFind application structure

VuFind is a PHP application with all files held in the /usr/local/vufind directory on the server. The application comprises a SolrMarc indexer to build a search index out of Marc records, an Apache website to display it, and a PHP Zend framework to manage catalogue functions. The website runs on port 443 as a https site.

Important sub-directories are:

* /usr/local/vufind/config (Original blank config files)

* /usr/local/vufind/harvest (Files to configure OAI-PMH harvest)

* /usr/local/vufind/import (Files to configure Marc import and indexing)

* /usr/local/vufind/languages (Language files for display languages)

* /usr/local/vufind/local (Local files)

* /usr/local/vufind/local/cache (Cache files for cover images, languages, objects, and searchspecs)

* /usr/local/vufind/local/config (Copies of the /usr/local/vufind2/config files which have been customised for SOAS Library)

* /usr/local/vufind/module (VuFind application code)

* /usr/local/vufind/public (Files for public display on the website: logos, favicons, sitemaps, etc.)

* /usr/local/vufind/solr	(Solr config and indexes)

* /usr/local/vufind/solr/vufind/biblio (Bibliographic records indexes and configuration)

* /usr/local/vufind/solr/vufind/biblio/conf (Configuration for Solr's bibliographic records search and indexing)

* /usr/local/vufind/solr/vufind/alphabetical_browse (Alphabrowse records indexes)

* /usr/local/vufind/themes (HTML and CSS files for website display)

* /usr/local/vufind/themes/templates/scb-soas (Highly customised files for SOAS Library display)

* /usr/local/vufind/util	(Utilities scripts: optimization, sitemap building, dedupe, etc.)

### 1.3.1: Configuration files

.ini configuration files are where the configuration for VuFind is kept. These are not versioned in git because they contain details that should not be made publicly accessible. Most are kept in the local directory in the config folder: /usr/local/vufind/local/config/vufind/... The important ones are:

* /usr/local/vufind/local/config/vufind/config.ini (Main configuration file for VuFind. Contains most general configuration: theme, ILS, debug mode, proxy settings, languages, authentication, links to external sites, SFX link, browsing, alphabrowsing, and more.)

* /usr/local/vufind/local/config/vufind/OLE.ini	(Configuration file for connection to OLE.)

* /usr/local/vufind/local/config/vufind/facets.ini (Controls the facets / filters.)

* /usr/local/vufind/local/config/vufind/searches.ini (Controls search settings.)

* /usr/local/vufind/local/config/vufind/searchspecs.yaml	(Controls distribution of 'points' for searches and hence relevancy ranking. More information here: https://vufind.org/wiki/searches_customizing_tuning_adding)

* /usr/local/vufind/local/config/vufind/sitemap.ini (Controls how the sitemap is built.)

* /usr/local/vufind/local/config/vufind/searchbox.ini (Controls the searchbox: largely used to turn on the 'combined search' module.)

* /usr/local/vufind/harvest/oai.ini (Defines OAI-PMH connections. Used for SOAS Research Online, SOAS Archives, SOAS Digital Collections, Directory of Open Access Books.)

### 1.3.2: Basic Apache control

VuFind runs on an Apache http server. The Apache configuration file can be found at /etc/httpd/conf.d/vufind.conf (which is a symlink to /usr/local/vufind/local/httpd-vufind.conf). Other Apache config files are at /etc/httpd/conf/ and /etc/httpd/conf.d/. 

To start Apache:

`service httpd start`

To restart Apache:

`service httpd restart`

### 1.5.3: Basic VuFind control

The main files to control VuFind are kept in the base /usr/local/vufind folder. This basic control is scripted using scripts in the vufind_integration_scripts repository but can also be run manually.

solr.sh starts and stops the application. import-marc.sh imports Marc files and starts the indexing process. index-alphabetic-browse.sh starts the alphabrowse indexing for the 'Browse Alphabetically' feature.

To start the VuFind application (user must be root):

`/usr/local/vufind/solr.sh start`

To stop the VuFind application (user must be root):

`/usr/local/vufind/solr.sh stop`

To restart the VuFind application (user must be root):

`/usr/local/vufind/solr.sh restart`

To run the indexing process:

`/usr/local/vufind/import-marc.sh [FILE CONTAINING MARC RECORDS]`

For example: /usr/local/vufind/import-marc.sh /home/vufind/input/daily/vufind_update_full-21.05.15.mrc

To run the alphabrowse indexing process:

`/usr/local/vufind/import-alphabetic-browse.sh`

### 1.5.4: Solr interface

VuFind's Apache Solr has a separate interface for debugging issues with the Solr search index. It's available on port 8080.

This interface allows you to run direct queries on the Solr index to check how fields have been indexed, to delete the Solr indexes, and to view JVM performance statistics. It also logs errors so you can see which errors get thrown up during the indexing process. 

If you want to see the Solr interface, port 8080 will need to be opened. The larger SOAS firewall should prevent port 8080 from being seen outside the network so even opening this port will only make the interface available within SOAS' network.

### 1.5.5: Cache

Clearing the cache may sometimes be necessary to make changes appear (in the case of languages or certain feature changes). To clear the caches, run:

`cd /usr/local/vufind`

`rm -rf ./local/cache/*`

## 1.6: Indexing VuFind

VuFind's index is built through a Solr indexing process. This creates two Solr 'cores': biblio and authority. It also creates a flat alphabetic-browse index used for alphabrowse features.

### 1.6.1: How indexing works

For much more documentation on indexing, see [https://github.com/solrmarc/solrmarc/wiki](https://github.com/solrmarc/solrmarc/wiki)

SolrMarc takes a Marc file and turns it into a searchable index by taking data from specified Marc fields and putting them into fields suitable for search. 

In the /usr/local/vufind/local/import/ directory, marc.properties and marc_local.properties specify which Marc fields are turned into fields. The program looks at marc.properties first and then looks at marc_local.properties for any amendments or additions. All SOAS' indexing customisations are in marc_local.properties. For example, we see that the 'note' field in the Solr index is constructed from subfield $a of the 500 field and from subfield $n of the 533 field. The 'callnumber' field is more complicated and is constructed from any subfield from the first 082 field in the Marc record. 

Solr then looks at /usr/local/vufind/solr/vufind/biblio/conf/schema.xml to see how to store that field in the index. All SOAS' customisations are marked up. The 'note' field is a string field (i.e. its contents can only be searched as discrete strings: a search for 'whatever' will only return results containing the discrete word 'whatever') which can have multiple values (i.e. can be stored as an array). The 'callnumber_txt' by contrast is a text field (i.e. it can be searched for in bits: a search for 'P896' will return 'P896', 'P89676483', or '789348P896734834' because they all contain P896) which can only be a single value (i.e. can be stored as a scalar).

The completed index will then be stored in /usr/local/vufind/solr/vufind/biblio/index. 

The search engine works by comparing the user's search input against the index and assigning 'points' to documents in the index based on the points distribution schema in /usr/local/vufind/local/config/vufind/searchspecs.yaml. Types of searches are configured in searches.ini and must have points distributions specified in searchspecs.yaml.

#### 1.6.1.1: Complex indexing

There's also more complicated indexing for certain fields that makes use of custom scripts and properties files. The 'format' field is a good example.

The 'format' field uses a script called format.bsh to turn a data element into a human-readable form. format.bsh is in /usr/local/vufind/import/index_scripts. It looks at various elements of the Marc record (principally the Leader, 007, 008, 245, and 082) and then runs through a script to look at the characters in those fields and assess what the format of the item is.

This script, for example, looks at character 6 of the Leader and assigns a value based on the character. If it's D then the format is 'MusicalScore'; if it's F then the format is 'Map'. 

Format then looks at the format_map.properties file which maps those values on to human-readable values. It's kept in /usr/local/vufind/import/translation_maps/. Our example above was assigned 'MusicalScore' so that is here translated into 'Musical Score'. Others are more extreme like 'VideoDisc' becoming the more understandable 'DVD'. 

### 1.6.2: Alphabrowse index


VuFind's alphabrowse index is the index behind the alphabrowse feature https://library.soas.ac.uk/Alphabrowse/Home. 

The alphabrowse index builds a flat hierarchical index out of specific fields from the bib index. The alphabrowse index is kept in /usr/local/vufind/solr/vufind/alphabetical-browse/.

For adding an alphabrowse field, basically it's defined in /usr/local/vufind/solr/vufind/biblio/conf/solrconfig.xml with stanzas for each alphabrowse search index. New alphabrowse fields also need to be added to index-alphabetic-browse.sh

It's important to note that only fields defined as 'strings' in schema.xml can be used as alphabrowse fields. Sometimes this has necessitated creating two fields: one 'text' file for searching and one 'string' field for browsing (hence 'linked_topic' for search and 'linked_topic_browse' for alphabrowse).

To run the alphabrowse indexing (as root):

`cd /usr/local/vufind2`

`./index-alphabetic-browse.sh`

## 1.7: OAI-PMH harvesting

As well as indexing Marc records, VuFind can use OAI-PMH harvesting to harvest metadata from OAI-PMH servers (Open Archives Initiative Protocol for Metadata Harvesting). For more information, see [https://vufind.org/wiki/importing_records#oai-pmh_harvesting](https://vufind.org/wiki/importing_records#oai-pmh_harvesting)

SOAS uses this to harvest metadata from the SOAS Research Online institutional repository. SOAS Research Online is an EPrints repository and puts its metadata out on an OAI-PMH server. The OAI-PMH harvest is scripted as /home/vufind/bin/vufind_600_oai_harvest.pl.

OAI-PMH sources are defined in /usr/local/vufind/harvest/oai.ini. SOAS Research Online is picked up from the OAI URL [http://eprints.soas.ac.uk/cgi/oai2](http://eprints.soas.ac.uk/cgi/oai2)

# 2.0: Customisation for SOAS Library

The version of VuFind that SOAS Library is running is heavily customised in terms of display, indexing, search refinement, and deep-level functions. All customisations should be either confined to 'local' files (i.e. those in the ./local directory) or should be marked up.

## 2.1: Theme and display

SOAS has a completely bespoke theme for VuFind's display. The theme is designed to be attractive and usable with a focus on accessibility: it was designed with input from the Disability Office in order to ensure that it meets guidelines for web accessibility.

The theme is kept in /usr/local/vufind/themes/scb-soas. This directory was originally a copy of /usr/local/vufind/themes/bootstrap3 and therefore makes use of the Bootstrap enhancements for VuFind 2.3. More information on Bootstrap is available here: [http://getbootstrap.com/](http://getbootstrap.com/)

### 2.1.1: HTML
The theme's HTML files are in /usr/local/vufind/themes/scb-soas/templates and contain customisations in many files. These are usually to alter the way the page is structured and sometimes to add new features using new PHP functions. 

### 2.1.2: CSS
CSS files are kept in /usr/local/vufind/themes/scb-soas/css

## 2.2: Other customisations

### 2.2.1: Custom searches

Searches were customised for SOAS Library's requirements. These changes were all made in the config files: config.ini, searches.ini, searchbox.ini, and searchspecs.yaml. 

This includes refining the Journal Title search and refining the Call Number search. It also includes adding fields (e.g. linked fields for vernacular script) to the various searches. 

### 2.2.2: Non-Roman scripts

In order to make non-Roman scripts displayable and searchable in VuFind, SOAS' installation makes use of the 880 field in Marc records and the getLinkedField function of the SolrMarc indexer. 

880 fields are bibliographic fields that link to another Marc field, usually for expressing the same field in another language. A 245 field, for example, would contain the title in English: an 880 field specifying a link to the 245 field might have that same title in Arabic. Links between regular fields and 880 fields are expressed in |6 subfields.

getLinkedField(fieldSpec) allows you to retrieve the linked original-language version of a given field, if such an original language version exists, a 880 field. For example, items that are originally in Chinese, Japanese, Korean, Arabic, Hebrew, or Russian, the cataloger usually transliterates the title and author fields from their original language into a latin-alphabet, phonic representation of the original title or author string, placing the original title or author in a 880 field with a tag indicating that that entry is linked to the transliterated title or author string in the main body of the MARC record.

SOAS' copy of marc_local.properties contains a number of linked fields to cover most fields required in other languages. 

The indexer will therefore create a linked_title field which contains the 880 field corresponding to any 245 subfields a, b, p. 

All new fields in marc_local.properties must also be added to /usr/local/vufind/solr/vufind/biblio/conf/schema.xml (after which Solr must be restarted). 

To make these fields searchable, they should be added to searchspecs.yaml. To make these fields displayable, you need to write a PHP function to retrieve them from the Solr index. Retrieving fields from Solr is done in the /usr/local/vufind/module/VuFind/src/VuFind/RecordDriver/SolrDefault.php file. This will return that field client-side when the function is called. Call the function in the HTML files on the template. For example, /usr/local/vufind/themes/scb-soas/templates/RecordDriver/SolrDefault/core.phtml governs the display of bib records.

### 2.2.3: Shelving location

See [https://github.com/solrmarc/solrmarc/wiki/Translation-maps#defining-a-pattern-based-translation-map](https://github.com/solrmarc/solrmarc/wiki/Translation-maps#defining-a-pattern-based-translation-map) for more information on the pattern translation map technique used here.

Shelving locations were not previously represented on the SOAS Library OPAC. Users had to switch between the catalogue and the location list on the SOAS Library website. We used VuFind's translation map indexing feature to design an automated way of determining the shelf location and present it to the user.

This basically uses the fact that classmarks can be directly mapped onto shelf locations. All books with a location of MAIN and a classmark between the range A360-A399 will be on Level E in Stacks 25-34. 

So during indexing the 'shelf_location' field uses the f and a subfields of the 082 field and uses the shelf_map.properties file to map those values on to another value. shelf_map.properties is kept in /usr/local/vufind/local/import/translation_maps.

shelf_map.properties matches the patterns in the 082 field and assigns them a value. It uses regular expressions to 'read' the classmark and determine what the appropriate shelving location would be for each record. 

In vim, hit 'Esc' and type:

`: let @a=0 | 1,$s/^shelf.pattern_\d* /\='shelf.pattern_'.(@a+setreg('a',@a+1)).' '/`

to update all of the shelf_pattern_xxx numerals to be in sequence.

### 2.2.4: Formats

SOAS has a couple of extra formats and some custom scripts to add these formats based on metadata. DVDs in SOAS Library are determined by classmark so we added a portion of script to /usr/local/vufind/local/import/index_scripts/format.bsh. 

This looks at the callnumber field and checks if subfield a is 'MDVD'. 
~~~~
    // CUSTOM CODE FOR SOAS LIBRARY

    // @author  Simon Barron <sb174@soas.ac.uk>

    // check the classmark for MDVD

    if (callnumber != null) {

        if (callnumber.getSubfield('a') != null){

            if (callnumber.getSubfield('a').getData().toLowerCase().contains("mdvd")) {

                result.add("VideoDisc");

                return result;

            }

        }

    }
~~~~

Study carrel keys are determined by title:
~~~~
    if (title != null) {

        if (title.getSubfield('a') != null){

            if (title.getSubfield('a').getData().toLowerCase().contains("carrel")) {

                result.add("Carrel");

                return result;

            }

        }

    }
~~~~

### 2.2.5: Admin section

Scanbit created an administration section of VuFind which is restricted to defined users at https://library.soas.ac.uk/Acadmin/ This admin section is used to control access types and permissions for walk-in users and to edit the location list.

All users able to access the admin section have an id_profile value of ‘3’ in the vufind.user table in VuFind’s database. To add users, update their record in the database by running
~~~~
UPDATE `vufind`.`user` SET `id_profile`='3' WHERE  `id`=[ID NUMBER];
~~~~

from the database command line or by editing the value in a database programme like HeidiSQL.

To avoid double-login, users must have a matching record in the version of OLE.
