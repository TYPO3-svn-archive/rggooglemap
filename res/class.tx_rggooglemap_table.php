<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Georg Ringer <typo3 et ringer ge dot org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Service to get the Geo Codes from Google
 */
#require_once(PATH_tslib.'class.tslib_pibase.php');
#require_once(PATH_t3lib.'class.t3lib_tcemain.php');


class tx_rggooglemap_table {
	var $prefixId 		= 'tx_rggooglemap_table';		// Same as class name
	var $scriptRelPath 	= 'res/class.tx_rggooglemap_table.php';	// Path to this script relative to the extension dir.
	var $extKey 		= 'rggooglemap';	// The extension key.
  var $myService;
  
	/**
	 * Initialization of the table object through serice extensions
	 * @param	string	Table
	 */  
   function init($table) {
    // use 'auth' service to find the user
    // first found user will be used

    $serviceChain='';
    while (is_object($serviceObj = t3lib_div::makeInstanceService('rggmData', $table, $serviceChain))) {
      $serviceChain.=','.$serviceObj->getServiceKey();    
      if ($tempuser=$serviceObj->init()) {

        // user found, just stop to search for more
         $this->myService =  $serviceObj;
        
        break;
      }
    }  
    return '';
  }

	/**
	 * Get the right table information
	 * @param	string	Table
	 * @return	Array with table information
	 */
  function table2($table, $field='') {
      global $TYPO3_DB;
    $neu2 = Array();
    $neu['fe_users']['lng'] = 'zip';
    $neu['fe_users']['lat'] = 'www';
      
    $neu['tt_address']['lng'] = 'tx_rggooglemap_lng';
    $neu['tt_address']['lat'] = 'tx_rggooglemap_lat';  
    $neu['tt_address']['rggmcat'] = 'tx_rggooglemap_cat2';
    $neu['tt_address']['name'] = 'name'; 

    $neu['tx_veguestbook_entries']['lng'] = 'tx_rggmveguestbook_lng';
    $neu['tx_veguestbook_entries']['lat'] = 'tx_rggmveguestbook_lat';  
    $neu['tx_veguestbook_entries']['rggmcat'] = 'tx_rggmveguestbook_cat';  
    $neu['tx_veguestbook_entries']['name'] = 'firstname';  

    $neu['tt_news']['lng'] = 'tx_rggmttnews_lng';
    $neu['tt_news']['lat'] = 'tx_rggmttnews_lat';  
    $neu['tt_news']['name'] = 'title';  
    $neu['tt_news']['rggmcat'] = 'tx_rggmttnews_cat';
    
    $neu['sys_domain']['lng'] = 'tx_rggmdomain_lng';
    $neu['sys_domain']['lat'] = 'tx_rggmdomain_lat';  
    $neu['sys_domain']['rggmcat'] = 'tx_rggmdomain_cat';  

    $neu['tx_city_record']['name'] = 'title';

     
    if ($field) return ($neu[$table][$field]) ? $neu[$table][$field] : $field;
    else return   $neu[$table];

  
  
  }
  
    function mergeFields2($table,$string) {
      $whereFields = $this->table($table);
      $whereOld = array_keys($whereFields);
      $whereNew = array_values($whereFields);
      return str_replace($whereOld, $whereNew, $string);
  }

  /*
  * selectquery
  *
  */    	
	function exec_SELECTquery($select,$table,$where,$groupBy='',$orderBy='',$offset='',$debug=0) {

    global $TYPO3_DB;
    $out = Array();
    $test = Array(); 
    $debugOut = Array();
    $out2 = '';
    $count = 0;

    // split tables
    $tables = explode(',',$table);
    $tableCount = count($tables);

    // query for each table
    foreach ($tables as $key=>$singleTable) {
      // table
      $singleTable = trim($singleTable);
      
      // get the object of the table
      $this->init($singleTable);

      // select
      $queryFields = ($select!='*') ? $this->myService->mergeFields($select) : '*';
      $debugOut[$singleTable]['fields'] = $queryFields;
      // where
      $whereFields = $this->myService->mergeFields($where);
      $debugOut[$singleTable]['where'] = $whereFields;     

      // offset
      if ($offset!='') {
        $tmp = explode(',',$offset);
        $offsetBegin = $tmp{0};
        $offsetEnd   = $tmp{0}+$tmp{1};
      }
      if ($debug==0) {
      
        // allfields
        $allFields = $this->myService->getTable();
        
        // the real query finally
        if ($tableCount==1) {
          $orderBy = $this->myService->mergeFields($orderBy);
          // if there is just 1 table, use the offset & orderBy without further code
          $res = $TYPO3_DB->exec_SELECTquery($queryFields,$singleTable,$whereFields,$groupBy,$orderBy,$offset);      
        } else {
          // if more tables, offset & orderBy has to be handeled myself
          $res = $TYPO3_DB->exec_SELECTquery($queryFields,$singleTable,$whereFields);
        }
    		while($row = $TYPO3_DB->sql_fetch_assoc($res)) {
  
          // get the correct offset. time consuming? we will see
          if (!$offset || $tableCount==1 || ($count>= $offsetBegin && $count < $offsetEnd)) {
            $out[$singleTable.'#'.$row['uid']] = $row;
            
            // mapping
            foreach ($allFields as $key=>$value) {
              if (strpos($queryFields, $key) || $queryFields == '*') {
            #    unset($out[$singleTable.'#'.$row['uid']][$value]);  
                $out[$singleTable.'#'.$row['uid']][$key] = $row[$value];
                
              }  	
              $out[$singleTable.'#'.$row['uid']][$key] = $row[$value];
            }     
             
            // additional information
            $out[$singleTable.'#'.$row['uid']] ['table'] = $singleTable;  	 	
          }
  
          // count for the offset needed
          $count++;     		
    		} # end while
      } # end foreach table
    }
      
      
    // sorting if multiple tables
    if ($tableCount>1) {
      // ASC || DESC
      $direction = stristr ($orderBy, ' DESC');
      // order field
      $orderField =  explode(' ',$orderBy);

      // if no orderfield, no sorting is needed
      if ($orderField!='') {
        $sortArray = array();
        
        // building the sort array
        foreach($out as $key => $array) {
            $sortArray[$key] = $array[$orderField{0}]; # sorting criteria
        } 
              
        // sorting process
        if ($direction) {
          array_multisort($sortArray, SORT_DESC, SORT_REGULAR, $out); # unsorted > sorted     
        } else {
          array_multisort($sortArray, SORT_ASC, SORT_REGULAR, $out); # unsorted > sorted      
        }
      }

    } # end tableCount

    if ($debug==1) return $debugOut;
    
		return $out; 
  
  }

	function exec_COUNTquery($table,$where) {
    global $TYPO3_DB;
    
    $tables = explode(',',$table);
    $tableCount = count($tables);
    $count = 0;

    // query for each table
    foreach ($tables as $key=>$singleTable) {
      // table
      $singleTable = trim($singleTable);
      $this->init($singleTable);
      // where
     # $whereFields = $this->mergeFields($singleTable,$where);
      $whereFields = $this->myService->mergeFields($where);

      // the real query finally
      $res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('count(*)',$singleTable,$whereFields); 
      $row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
      
      $count+=$row[0];   
  	}
    
    return $count;
  } # end exec_COUNTquery()
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/res/class.tx_rggooglemap_table.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/res/class.tx_rggooglemap_table.php']);
}
?>