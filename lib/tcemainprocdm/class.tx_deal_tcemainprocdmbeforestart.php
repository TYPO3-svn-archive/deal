<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2014 - Dirk Wildt <http://wildt.at.die-netzmacher.de>
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
 * ************************************************************* */


/**
 * The class tx_deal_tcemainprocdmbeforestart bundles methods for evaluating data in backend forms
 *
 * @author    Dirk Wildt <http://wildt.at.die-netzmacher.de>
 * @package    TYPO3
 * @subpackage  deal
 *
 * @version 0.0.3
 * @since 0.0.3
 */

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   66: class tx_deal_tcemainprocdmbeforestart
 *
 *              SECTION: Hook: processDatamap_postProcessFieldArray
 *  111:     public function processDatamap_postProcessFieldArray( $status, $table, $id, &$fieldArray, &$reference )
 *
 *              SECTION: amazon
 *  172:     private function amazon( &$fieldArray )
 *
 *              SECTION: ebay
 *  207:     private function ebay( &$fieldArray )
 *  234:     private function ebayApi( &$fieldArray )
 *  254:     private function ebayApiInit( )
 *
 *              SECTION: Log
 *  284:     public function log( $prompt, $status=-1, $action=2, $header=2 )
 *
 *              SECTION: row
 *  372:     private function getRowUpdateBefore( )
 *  462:     private function getRowUpdateAfter( $fieldArray )
 *
 * TOTAL FUNCTIONS: 8
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_deal_tcemainprocdmbeforestart
{

  // [String] status of the current process: update, edit, delete, moved
  private $prefixLog = 'tx_deal';
  // [Array] datamap record
  private $datamapRecord = null;
  // [String] label of the datamap table
  private $datamapTable = null;
  // [Integer] uid of the datamap record
  private $datamapRecordUid = null;
  // [Object] parent object
  private $reference = null;
  // [Array] configuration by the extension manager
  public $confArr = null;
  // [Object] ebay API
  private $ebayApi = null;

  /*   * *********************************************
   *
   * Hook: processDatamap_beforeStart
   *
   * ******************************************** */

  public function processDatamap_beforeStart(&$reference)
  {

    if (!$this->init($reference))
    {
      return;
    }

    if ($this->tcaIsWithoutDealMarketplaces())
    {
      return; // RETURN : current table is without any tx_deal configuration
    }

    //var_dump( __METHOD__, __CLASS__, array_keys( $reference->datamap ) );
    //$this->initVarsReference($reference);
    // marketplace amazon
    if (is_array($GLOBALS['TCA'][$this->datamapTable]['ctrl']['tx_deal']['marketplaces']['amazon']))
    {
      $this->amazon();
    }

    // marketplace ebay
    if (is_array($GLOBALS['TCA'][$this->datamapTable]['ctrl']['tx_deal']['marketplaces']['ebay']))
    {
      $this->ebay();
    }

    return;
  }

//public function processDatamap_afterAllOperations(&$pObj) {
//  var_dump( __METHOD__, __CLASS__, $pObj->datamap);
//}


  /*   * *********************************************
   *
   * amazon
   *
   * ******************************************** */

  /**
   * amazon( )
   *
   * @param	array		$fieldArray : Array of modified fields
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function amazon()
  {
    switch (true)
    {
      case(!$GLOBALS['TCA'][$this->datamapTable]['ctrl']['tx_deal']['marketplaces']['amazon']['enabled'] ):
        // RETURN : amazon isn't enabled
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:amazonEnabledFalse');
        $this->log($prompt, 0, 2);
        return;
      default:
        // Follow the workflow
        break;
    }
    // RETURN : current table is without any tx_deal configuration

    return;
  }

  /*   * *********************************************
   *
   * ebay
   *
   * ******************************************** */

  /**
   * ebay( )
   *
   * @param	array		$fieldArray : Array of modified fields
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
//  private function ebay(&$fieldArray)
  private function ebay()
  {
    $ebayEnvironment = $GLOBALS['TCA'][$this->datamapTable]['ctrl']['tx_deal']['marketplaces']['ebay']['environment']['key'];

    switch (true)
    {
      case( $ebayEnvironment == 'production' ):
      case( $ebayEnvironment == 'sandbox' ):
        $this->ebayApi();
        break;
      case( empty($ebayEnvironment) ):
        // RETURN : ebay isn't enabled
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:ebayEnvironmentFalse');
        $this->log($prompt, 0, 2);
        break;
      default:
        // RETURN : ebay isn't enabled
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:ebayEnvironmentError');
        $prompt = str_replace('%ebayEnvironment%', $ebayEnvironment, $prompt);
        $this->log($prompt, 4, 2);
        break;
    }
  }

  /**
   * ebayApi( )
   *
   * @param	array		$fieldArray : Array of modified fields
   * @return	array		$result     : lon, lat
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  //private function ebayApi(&$fieldArray)
  private function ebayApi()
  {
    $this->ebayApiInit();

    $this->ebayApi->setVarPobj($this);

    $result = $this->ebayApi->main();

    return $result;
  }

  /**
   * ebayApiInit( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function ebayApiInit()
  {
    // Require map library
    require_once( PATH_typo3conf . 'ext/deal/lib/marketplaces/ebay/api/class.tx_deal_ebayApi.php' );
    // Create object
    $this->ebayApi = new tx_deal_ebayApi( );
  }

  /*   * *********************************************
   *
   * Log
   *
   * ******************************************** */

  /**
   * log( )
   *
   * @param	string		$prompt : prompt
   * @param	integer		$status : -1 = no flash message, 0 = notice, 1 = info, 2 = OK, 3 = warn, 4 = error
   * @param	string		$action : 0=No category, 1=new record, 2=update record, 3= delete record, 4= move record, 5= Check/evaluate
   * @param	string		$header : 0=No header, 1=Deal! TYPO3 for amazon and ebay, 2=Deal! TYPO3 for amazon and ebay
   * @return	void
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function log($prompt, $status = -1, $action = 2, $header = 2)
  {
    $this->initVarsConfArr();

    $table = $this->datamapTable;
    $uid = $this->datamapRecordUid;
    $pid = null;

    if ($table)
    {
      $logPrompt = '[' . $this->prefixLog . ' (' . $table . ':' . $uid . ')] ' . $prompt . PHP_EOL;
    }
    else
    {
      $logPrompt = '[' . $this->prefixLog . '] ' . $prompt . PHP_EOL;
    }
    //$fmPrompt = $prompt;
    $fmPrompt = nl2br(htmlentities($prompt));

    switch ($header)
    {
      case( 0 ):
        $fmHeader = '';
        break;
      case( 1 ):
        $fmHeader = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:promptDealPhrase');
        break;
      case( 2 ):
      default:
        $fmHeader = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:promptDealPhrase');
        break;
    }

    switch ($status)
    {
      case( -1 ):
        $fmStatus = null;
        $logStatus = 0;
        break;
      case( 0 ):
        $fmStatus = t3lib_FlashMessage::NOTICE;
        $logStatus = 0;
        break;
      case( 1 ):
        $fmStatus = t3lib_FlashMessage::INFO;
        $logStatus = 0;
        break;
      case( 2 ):
        $fmStatus = t3lib_FlashMessage::OK;
        $logStatus = 0;
        break;
      case( 3 ):
        $fmStatus = t3lib_FlashMessage::WARNING;
        $logStatus = 0;
//        $fmPrompt = $prompt . '<br />
//                      ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:promptDetailsToSyslog');
        break;
      case( 4 ):
        $fmStatus = t3lib_FlashMessage::ERROR;
        $logStatus = 0;
//        $fmPrompt = $prompt . '<br />
//                      ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:promptDetailsToSyslog');
        break;
      default:
        $logStatus = 0;
        break;
    }

    // Language for labels of static templates and page tsConfig
    if ($this->confArr['drsAdmintoolsLogEnabled'])
    {
      $this->reference->log($table, $uid, $action, $pid, $logStatus, '[DRS] ' . $logPrompt);
    }

    // RETURN : Don't prompt to the backend
    if ($status < 0)
    {
      return;
    }
    // RETURN : Don't prompt to the backend

    $flashMessage = t3lib_div::makeInstance('t3lib_FlashMessage', $fmPrompt, $fmHeader, $fmStatus);
    t3lib_FlashMessageQueue::addMessage($flashMessage);
  }

  /*   * *********************************************
   *
   * get
   *
   * ******************************************** */

  /**
   * getDatamapRecord( )  : Returns the datamap record, if $key isn't given, else ist returns the value of the given $key.
   *
   * @param   string    $key              : label of the field of the record, which value should returned (optional)
   * @return	mixed     $arrValues/$value : if key is given $value else $arrValues
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function getDatamapRecord($key = null)
  {
    if (empty($key))
    {
      $arrValues = $this->datamapRecord;
      return $arrValues;
    }

    $this->sqlSetDatamapRecordField($key);
    $value = $this->datamapRecord[$key];
    return $value;
  }

  /**
   * getDatamapTable( )
   *
   * @return	array $datamapTable : global $datamapTable
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function getDatamapTable()
  {
    return $this->datamapTable;
  }

  /**
   * getDatamapRecordUid( )
   *
   * @return	array $datamapRecordUid : global $datamapRecordUid
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function getDatamapRecordUid()
  {
    return $this->datamapRecordUid;
  }

  /**
   * getTcaConf( ) : get the configuration from the TCA of the current table
   *
   * @return	array    $conf :
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function getTcaConf()
  {
    if (empty($this->datamapTable))
    {
      $prompt = __METHOD__ . ' (#' . __LINE__ . '): $processTable is empty.';
      die($prompt);
    }
    $conf = $GLOBALS['TCA'][$this->datamapTable]['ctrl']['tx_deal']['marketplaces']['ebay'];
    return $conf;
  }

  /*   * *********************************************
   *
   * init
   *
   * ******************************************** */

  /**
   * init( )
   *
   * @param	object		$reference  : parent object (reference)
   * @return	boolean $success    :
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function init(&$reference)
  {
    $success = $this->initVars($reference);
    return $success;
  }

  /**
   * initVars( )
   *
   * @param	object		$reference  : parent object (reference)
   * @return	boolean $success    :
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function initVars(&$reference)
  {
    $success = $this->initVarsReference($reference);
    if (!$success)
    {
      return false;
    }
    $this->initVarsConfArr();
    $this->initVarsDatamap();
    return true;
  }

  /**
   * initVarsConfArr( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function initVarsConfArr()
  {
    if ($this->confArr !== null)
    {
      return $this->confArr;
    }

    $this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deal']);
  }

  /**
   * initVarsDatamap( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function initVarsDatamap()
  {
    $this->initVarsDatamapTable();
    $this->initVarsDatamapUid();
    $this->initVarsDatamapRecord();
  }

  /**
   * initVarsDatamapRecord( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function initVarsDatamapRecord()
  {
    $this->datamapRecord = $this->reference->datamap[$this->datamapTable][$this->datamapRecordUid];
  }

  /**
   * initVarsDatamapTable( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function initVarsDatamapTable()
  {
    $tables = array_keys($this->reference->datamap);
    $this->datamapTable = $tables[0];
  }

  /**
   * initVarsDatamapUid( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function initVarsDatamapUid()
  {
    $records = array_keys($this->reference->datamap[$this->datamapTable]);
    $this->datamapRecordUid = $records[0];
  }

  /**
   * initVarsReference( )
   *
   * @param	object		$reference      : parent object (reference)
   * @return	boolean $datamapIsNotEmpty : false, if datamap is empty

   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function initVarsReference(&$reference)
  {
    $datamapIsNotEmpty = false;

    $this->reference = $reference;

    if (empty($this->reference->datamap))
    {
      $prompt = 'Datamap is empty.';
      $this->log($prompt, -1);
      $datamapIsNotEmpty = false;
    }
    else
    {
      $datamapIsNotEmpty = true;
    }

    return $datamapIsNotEmpty;
  }

  /*   * *********************************************
   *
   * set
   *
   * ******************************************** */

  /**
   * setDatamapRecord( )
   *
   * @param   array   $datamapRecord : datamap record
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function setDatamapRecord($datamapRecord)
  {
    $this->reference->datamap[$this->datamapTable][$this->datamapRecordUid] = $datamapRecord;
    $this->datamapRecord = $datamapRecord;
  }

  /**
   * setDatamapRecordFieldPrepend( )
   *
   * @param   string  $key        :
   * @param   string  $value      :
   * @param   string  $boolWiEol  : prepends an end of line (optional)
   * @return	array $datamapRecord : global $datamapRecord
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function setDatamapRecordFieldPrepend($key, $value, $boolWiEol = true)
  {
    if ($boolWiEol)
    {
      $this->datamapRecord[$key] = PHP_EOL . $this->datamapRecord[$key];
    }

    $this->datamapRecord[$key] = $value . $this->datamapRecord[$key];
    $this->setDatamapRecord($this->datamapRecord);
  }

  /**
   * setDatamapRecordFieldUpdate( )
   *
   * @param   string  $key        :
   * @param   string  $value      :
   * @param   string  $boolPrompt : prompt in case of changing the value (optional)
   * @return	array $datamapRecord : global $datamapRecord
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function setDatamapRecordFieldUpdate($key, $newValue, $boolPrompt = true)
  {
    $oldValue = $this->datamapRecord[$key];
//    $oldValue = $this->getDatamapRecord($key);
    if ($oldValue == $newValue)
    {
      return;
    }

    $prompt = $key . ' is updated: "' . $oldValue . '" -> "' . $newValue . '"';
    if ($boolPrompt)
    {
      $this->log($prompt, 3, 2, 1);
    }
    else
    {
      $this->log($prompt, -1, 2, 1);
    }

    $this->datamapRecord[$key] = $newValue;
    $this->setDatamapRecord($this->datamapRecord);
//var_dump(__METHOD__, __LINE__, $this->reference->datamap);
  }

  /*   * *********************************************
   *
   * sql
   *
   * ******************************************** */

  /**
   * sqlSetDatamapRecordField( )  :
   *
   * @param   string    $key  : label of the field of the record, which value should returned
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function sqlSetDatamapRecordField($key)
  {
    if (isset($this->datamapRecord[$key]))
    {
      return true;
    }

    $table = $this->getDatamapTable();
    $uid = $this->getDatamapRecordUid();

    $select_fields = $key;
    $from_table = $table;
    $where_clause = 'uid = ' . $uid;
    $groupBy = null;
    $orderBy = null;
    $limit = null;

    $query = $GLOBALS['TYPO3_DB']->SELECTquery(
            $select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit
    );
    //var_dump(__METHOD__, __LINE__, $query);
    // Set the query
    // Execute the query
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery
            (
            $select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit
    );
    // Execute the query
    // RETURN : ERROR
    $error = $GLOBALS['TYPO3_DB']->sql_error();
    if (!empty($error))
    {
      $prompt = 'ERROR: Unproper SQL query at ' . __METHOD__ . ' (#' . __LINE__ . ')';
      $this->log($prompt, 4, 2, 1);
      $prompt = 'query: ' . $query;
      $this->log($prompt, 0, 2, 1);
      $prompt = 'prompt: ' . $error;
      $this->log($prompt, 4, 2, 1);

      return false;
    }
    // RETURN : ERROR
    // Fetch first row only
    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    // Free the SQL result
    $GLOBALS['TYPO3_DB']->sql_free_result($res);

    $value = $row[$key];

    $boolPrompt = false;
    $this->setDatamapRecordFieldUpdate($key, $value, $boolPrompt);

    return true;
  }

  /**
   * tcaIsWithoutDealMarketplaces( )
   *
   * @return	boolean
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function tcaIsWithoutDealMarketplaces()
  {
    switch (true)
    {
      case(!is_array($GLOBALS['TCA'][$this->datamapTable]['ctrl']['tx_deal']) ):
        $prompt = $this->datamapTable . ' is without any configuration TCA.' . $this->datamapTable . '.ctrl.tx_deal';
        $this->log($prompt, -1, 2, 1);
        // RETURN : current table is without any tx_deal configuration
        return true;
      case(!is_array($GLOBALS['TCA'][$this->datamapTable]['ctrl']['tx_deal']['marketplaces']) ):
        $prompt = $this->datamapTable . ' is without any configuration TCA.' . $this->datamapTable . '.ctrl.tx_deal.marketplaces';
        $this->log($prompt, -1, 2, 1);
        // RETURN : current table is without any tx_deal marketplace configuration
        return true;
    }

    return false;
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/lib/tcemainprocdm/class.tx_deal_tcemainprocdmbeforestart.php'])
{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/lib/tcemainprocdm/class.tx_deal_tcemainprocdmbeforestart.php']);
}
?>