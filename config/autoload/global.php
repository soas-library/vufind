<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overridding configuration values from modules, etc.  
 * You would place values in here that are agnostic to the environment and not 
 * sensitive to security. 
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source 
 * control, so do not include passwords or other sensitive information in this 
 * file.
 */
$GLOBALS['PATH_ARCHIVE_XML'] = '/usr/local/vufind/archivecollections/';
$GLOBALS['PATH_ARCHIVE_HARVEST'] = '/usr/local/vufind/local/harvest/Archive/';
$GLOBALS['PATH_ARCHIVE_INFO'] = '/usr/local/vufind/local/harvest/Archive/info.txt';
$GLOBALS['CONTEXT'] = 'http';
$GLOBALS['MAX_WORD_DESCRIPTION'] = '20';
$GLOBALS['MAX_WORD_FIELD'] = '550';
$GLOBALS['arr_excel'] = array();
return array(
    // ...
);
