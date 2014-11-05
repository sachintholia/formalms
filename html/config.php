<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

/**
 * Db info
 * -------------------------------------------------------------------------
 * db type, now mysql and mysqli are supported
 * db server address
 * db user name
 * db user password
 * db name
 * charset to use in the db connection
 */
$cfg['db_type'] = 'mysql';
$cfg['db_host'] = 'localhost';
$cfg['db_user'] = 'root';
$cfg['db_pass'] = '';
$cfg['db_name'] = 'forma';
$cfg['db_charset'] = 'utf8';

$cfg['db_user'] = 'dba_forma';
$cfg['db_pass'] = 'dba_forma';
$cfg['db_name'] = 'forma_org';

/**
 * Tables prefix
 * -------------------------------------------------------------------------
 * prefix for the core tables
 * prefix for the lms tables
 * prefix for the cms tables
 * prefix for the scs tables
 * prefix for the ecom tables
 * prefix for the ecom tables
 */
$cfg['prefix_fw'] = 'core';
$cfg['prefix_lms'] = 'learning';
$cfg['prefix_cms']  = 'cms';
$cfg['prefix_scs'] = 'conference';
$cfg['prefix_ecom'] = 'ecom';
$cfg['prefix_crm'] = 'crm';

/**
 * File upload
 * -------------------------------------------------------------------------
 * upload type (fs|ftp)
 * ftphost: the ftp hostname
 * ftpport: the ftp port
 * ftpuser: the ftp username
 * ftppass: the ftp password
 * ftppath: the ftp path from the user main home dir to the docebo root folder
 */
$cfg['uploadType'] = 'fs';

$cfg['ftphost']  = 'localhost';
$cfg['ftpport']  = '21';
$cfg['ftpuser']  = '';
$cfg['ftppass']  = '';
$cfg['ftppath']  = '/';

/**
 * External smtp config
 * -------------------------------------------------------------------------
 */
$cfg['use_smtp'] = 'off';
$cfg['smtp_host'] ='';
$cfg['smtp_user'] ='';
$cfg['smtp_pwd'] ='';

/**
 * Session custom param
 * -------------------------------------------------------------------------
 * debug is enabled ?
 * session must survive at least X seconds
 * session save_path if specified will be used instead of the defaul one
 */
$cfg['do_debug']    = false;
$cfg['session_lenght']   = (120 * 60);
$cfg['session_save_path']  = false;
$cfg['demo_mode']   = false;

/**
 * Technical preferences
 * -------------------------------------------------------------------------
 * filter_tool: the class for input filtering that you want to use
 * mail_br: used in mail composition (no longer needed?)
 */
$cfg['filter_tool'] = 'htmlpurifier';
$cfg['mail_br']  = "\r\n";

/**
 * Template engine custom param
 * -------------------------------------------------------------------------
 * add all template_engine enabled (if exists)
 * array value=file extension
 * template_engine available: twig
 * todo: 'class'=>'TwigManager', 'lib'=>'lib.twigmanager.php'); 
 */
$cfg['template_engine'] = Array();
$cfg['template_engine']['twig'] = array('ext'=>'.twig');

//NB avoid caching
$cfg['twig_debug']    = true;

/* */