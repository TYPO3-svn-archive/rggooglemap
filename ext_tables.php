<?phpif (!defined ('TYPO3_MODE')) 	die ('Access denied.');global $TYPO3_CONF_VARS;// class for displaying the category tree in BE forms.include_once(t3lib_extMgm::extPath($_EXTKEY).'res/class.tx_rggm_treeview.php');// Default items for tt_address$tempColumns = Array (	"tx_rggooglemap_lng" => Array (				"exclude" => 1,				"label" => "LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_lng",				"config" => Array (			"type" => "input",				"size" => "20",		)	),	"tx_rggooglemap_lat" => Array (				"exclude" => 1,				"label" => "LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_lat",				"config" => Array (			"type" => "input",				"size" => "20",		)	),	"tx_rggooglemap_display" => Array (				"exclude" => 1,				"label" => "LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_display",				"config" => Array (			"type" => "check",			"default" => "1" 		)	),  "tx_rggooglemap_cat2" => Array (				'exclude' => 1,		'label' => 'LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_cat',			'config' => Array (			'type' => 'select',			'form_type' => 'user',			'userFunc' => 'tx_rggm_treeview->displayCategoryTree',			'treeView' => 1,			'treeName' => 'txchtreeviewexample',			'foreign_table' => 'tx_rggooglemap_cat',			'size' => 5,			'autoSizeMax' => 10,			'minitems' => 0,			'maxitems' => 10,			'wizards' => Array(			),		)	),  "tx_rggooglemap_ce" => Array (              "exclude" => 1,        		'label' => 'LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_ce',              "config" => Array (          "type" => "group",              "internal_type" => "db",              "allowed" => "tt_content",              "size" => 2,              "minitems" => 0,          "maxitems" => 5,      )  ),);// fields for tt_address$value = 'tt_address';t3lib_div::loadTCA($value);t3lib_extMgm::addTCAcolumns($value,$tempColumns,1);t3lib_extMgm::addToAllTCAtypes($value,"tx_rggooglemap_lng;;;;1-1-1, tx_rggooglemap_lat, tx_rggooglemap_display,  tx_rggooglemap_cat2, tx_rggooglemap_ce");/** GENERIC PART*/ // include generic table access#require_once(t3lib_extMgm::extPath('rggooglemap').'res/class.tx_rggooglemap_table.php');#$genericTable = t3lib_div::makeInstance('tx_rggooglemap_table');// get tables from EM settings$tmp_confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);$tables = explode(',',$tmp_confArr['tables']);foreach ($tables as $key=>$singleTable) {  $singleTable = trim($singleTable);  $serviceChain='';  while (is_object($serviceObj = t3lib_div::makeInstanceService('rggmData', $singleTable, $serviceChain))) {    $serviceChain.=','.$serviceObj->getServiceKey();        if ($tempuser=$serviceObj->init()) {      // user found, just stop to search for more#       $this->myService =  $serviceObj;        if ($tmp_confArr['wizards'])	{          $lat = $serviceObj->getTable('lat');          $lng = $serviceObj->getTable('lng');          $cat = $serviceObj->getTable('rggmcat');              	t3lib_div::loadTCA($singleTable);         	$TCA[$singleTable]['columns'][$lng]['config']['wizards'] = array(        		'_POSITION' => 'right',        		'googlemap' => array(        			'title' => 'LLL:EXT:rggooglemap/locallang_db.xml:wizard.title',        			'icon' => $BACK_PATH.t3lib_extMgm::extRelPath('rggooglemap').'mod1/moduleicon.gif',        			'type' => 'popup',        			'script' => 'EXT:rggooglemap/class.tx_rggooglemap_wizard.php',        			'JSopenParams' => 'height=630,width=800,status=0,menubar=0,scrollbars=0',        			'lat_field' => $lat,        			'lng_field' => $lng,        		),        /*		'test' => array(        			'title' => 'LLL:EXT:rggooglemap/locallang_db.xml:wizard.title',        			'icon' => $BACK_PATH.t3lib_extMgm::extRelPath('rggooglemap').'mod1/moduleicon.gif',        			'type' => 'popup',        			'notNewRecords'=> 1,        			'script' => 'EXT:rggooglemap/class.tx_rggooglemap_wizardGeocoding.php',        			'JSopenParams' => 'height=630,width=800,status=0,menubar=0,scrollbars=0',        			'lat_field' => $lat,        			'lng_field' => $lng,        		),*/        	);        	$TCA[$singleTable]['columns'][$lat]['config']['wizards'] = array(        		'_POSITION' => 'right',        		'googlemap' => array(        			'title' => 'LLL:EXT:rggooglemap/locallang_db.xml:wizard.title',        			'icon' => $BACK_PATH.t3lib_extMgm::extRelPath('rggooglemap').'mod1/moduleicon.gif',        			'type' => 'popup',        			'script' => 'EXT:rggooglemap/class.tx_rggooglemap_wizard.php',        			'JSopenParams' => 'height=630,width=800,status=0,menubar=0,scrollbars=0',        			'lat_field' => $lat,        			'lng_field' => $lng,        		),        	); 	        	$TCA[$singleTable]['columns'][$cat]['config'] = Array (      			'type' => 'select',      			'form_type' => 'user',      			'userFunc' => 'tx_rggm_treeview->displayCategoryTree',      			'treeView' => 1,      			'treeName' => 'txchtreeviewexample',      			'foreign_table' => 'tx_rggooglemap_cat',      			'size' => 5,      			'autoSizeMax' => 10,      			'minitems' => 0,      			'maxitems' => 10,      			'wizards' => Array(      			),        	);              }            break;    }  }}  #echo t3lib_div::view_array($TCA['tt_news']['columns']);t3lib_div::loadTCA('tt_content');$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key, pages';t3lib_extMgm::addPlugin(Array('LLL:EXT:rggooglemap/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Google Map");$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';t3lib_extMgm::addPiFlexFormValue('rggooglemap_pi1', 'FILE:EXT:rggooglemap/flexform_ds.xml');// Wiziconif (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_rggooglemap_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_rggooglemap_pi1_wizicon.php';// Be Moduleif (TYPO3_MODE=="BE")	{	t3lib_extMgm::addModule("web","txrggooglemapM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");}// Category $TCA["tx_rggooglemap_cat"] = Array (    "ctrl" => Array (        'title' => 'LLL:EXT:rggooglemap/locallang_db.xml:tx_rggooglemap_cat',                'label' => 'title',            'tstamp' => 'tstamp',        'crdate' => 'crdate',        'cruser_id' => 'cruser_id',        "default_sortby" => "ORDER BY crdate",            "delete" => "deleted",            'treeParentField' => 'parent_uid',        "enablecolumns" => Array (                    "disabled" => "hidden",        ),        "dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",        "iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_rggooglemap_cat.gif",    ),    "feInterface" => Array (        "fe_admin_fieldList" => "hidden, title, parent_uid",    ));/*$TCA["tx_rggooglemap_generic"] = array (    "ctrl" => array (        'title'     => 'LLL:EXT:rggooglemap/locallang_db.xml:tx_rggooglemap_generic',                'label'     => 'tablename',            'tstamp'    => 'tstamp',        'crdate'    => 'crdate',        'cruser_id' => 'cruser_id',        'default_sortby' => "ORDER BY tablename",            'delete' => 'deleted',            'enablecolumns' => array (                    'disabled' => 'hidden',        ),        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_rggooglemap_generic.gif',    ),    "feInterface" => array (        "fe_admin_fieldList" => "hidden, tablename, mapping",    ));*/?>