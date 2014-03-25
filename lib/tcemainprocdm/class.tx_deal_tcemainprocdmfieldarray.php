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
 * The class tx_deal_tcemainprocdmfieldarray bundles methods for evaluating data in backend forms
 *
 * @author    Dirk Wildt <http://wildt.at.die-netzmacher.de>
 * @package    TYPO3
 * @subpackage  deal
 *
 * @version 0.0.1
 * @since 0.0.1
 */

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   66: class tx_deal_tcemainprocdmfieldarray
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
class tx_deal_tcemainprocdmfieldarray
{

  // [String] status of the current process: update, edit, delete, moved
  private $prefixLog = 'tx_deal ';
  // [String] status of the current process: update, edit, delete, moved
  private $processStatus = null;
  // [String] label of the table of the current process
  private $processTable = null;
  // [String] record uid of the current process
  private $processId = null;
  // [Array] configuration by the extension manager
  private $confArr = null;
  // [Object] ebay API
  private $ebayApi = null;
  // [Object] parent object
  private $pObj = null;
  // [Array] Row of the current record with updated data
  private $rowUpdateAfter = null;
  // [Array] Row of the current record with former data
  private $rowUpdateBefore = null;

  /*   * *********************************************
   *
   * Hook: processDatamap_postProcessFieldArray
   *
   * ******************************************** */

  /**
   * processDatamap_postProcessFieldArray( )
   *
   * @param	string		$status     : update, edit, delete, moved
   * @param	string		$table      : label of the current table
   * @param	integer		$id         : uid of the current record
   * @param	array		$fieldArray : modified fields - reference!
   * @param	object		$reference  : parent object - reference!
   * @return	void
   * @access public
   * @version   0.0.1
   * @since     0.0.1
   */
  public function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$reference)
  {
    $this->initVarsPobj($reference);

    if( $this->tcaIsWithoutDealMarketplaces( $table ))
    {
      return; // RETURN : current table is without any tx_deal configuration
    }

    $this->init($status, $table, $id, $fieldArray, $reference);

    // marketplace amazon
    if (is_array($GLOBALS['TCA'][$table]['ctrl']['tx_deal']['marketplaces']['amazon']))
    {
      $this->amazon($fieldArray, $reference);
    }

    // marketplace ebay
    if (is_array($GLOBALS['TCA'][$table]['ctrl']['tx_deal']['marketplaces']['ebay']))
    {
      $this->ebay($fieldArray, $reference);
    }

    return;
  }

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
   * @version   0.0.1
   * @since     0.0.1
   */
  private function amazon(&$fieldArray)
  {
    switch (true)
    {
      case(!$GLOBALS['TCA'][$this->processTable]['ctrl']['tx_deal']['marketplaces']['amazon']['enabled'] ):
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
   * @version   0.0.1
   * @since     0.0.1
   */
  private function ebay(&$fieldArray)
  {
    $ebayEnvironment = $GLOBALS['TCA'][$this->processTable]['ctrl']['tx_deal']['marketplaces']['ebay']['environment']['key'];

    switch (true)
    {
      case( $ebayEnvironment == 'production' ):
      case( $ebayEnvironment == 'sandbox' ):
        $this->ebayApi($fieldArray);
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
   * @version   0.0.1
   * @since     0.0.1
   */
  private function ebayApi(&$fieldArray)
  {
    $this->ebayApiInit();

    $this->ebayApi->setVarPobj($this);
    $this->ebayApi->setVarFieldarray($fieldArray);

    $result = $this->ebayApi->main();

    return $result;
  }

  /**
   * ebayApiInit( )
   *
   * @return	void
   * @access private
   * @version   0.0.1
   * @since     0.0.1
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
   * Get
   *
   * ******************************************** */

  /**
   * getConf( ) : get the configuration from the TCA of the current table
   *
   * @return	array    $conf :
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function getConf()
  {
    if (empty($this->processTable))
    {
      $prompt = __METHOD__ . ' (#' . __LINE__ . '): $processTable is empty.';
      die($prompt);
    }
    $conf = $GLOBALS['TCA'][$this->processTable]['ctrl']['tx_deal']['marketplaces']['ebay'];
    return $conf;
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
   * @version   0.0.1
   * @since     0.0.1
   */
  public function log($prompt, $status = -1, $action = 2, $header = 2)
  {
    $this->initVarConfarray( );

    $table = $this->processTable;
    $uid = $this->processId;
    $pid = null;

    $logPrompt = '[' . $this->prefixLog . ' (' . $table . ':' . $uid . ')] ' . $prompt . PHP_EOL;
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
        $fmPrompt = $prompt . '<br />
                      ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:promptDetailsToSyslog');
        $fmStatus = t3lib_FlashMessage::WARNING;
        $logStatus = 0;
        break;
      case( 4 ):
        $fmPrompt = $prompt . '<br />
                      ' . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/tcemainprocdm/locallang.xml:promptDetailsToSyslog');
        $fmStatus = t3lib_FlashMessage::ERROR;
        $logStatus = 0;
        break;
      default:
        $logStatus = 0;
        break;
    }

    // Language for labels of static templates and page tsConfig
    if ($this->confArr['drsAdmintoolsLogEnabled'])
    {
      $this->pObj->log($table, $uid, $action, $pid, $logStatus, '[DRS] ' . $logPrompt);
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
   * init
   *
   * ******************************************** */

  /**
   * init( )
   *
   * @param	string		$status     : update, edit, delete, moved
   * @param	string		$table      : label of the current table
   * @param	integer		$id         : uid of the current record
   * @param	array     $fieldArray : modified fields
   * @param	object		$reference  : parent object
   * @return	void
   * @access private
   * @version   0.0.1
   * @since     0.0.1
   */
  private function init($status, $table, $id, $fieldArray, $reference)
  {
    $this->initVarConfarray();
    $this->initVars($status, $table, $id, $reference);
    $this->initLog($fieldArray);
  }

  /**
   * initLog( )
   *
   * @param	array     $fieldArray : modified fields
   * @return	void
   * @access private
   * @version   0.0.1
   * @since     0.0.1
   */
  private function initLog($fieldArray)
  {
    $prompt = $this->processStatus . ': ' . $this->processTable . ': ' . $this->processId . ': ' . var_export($fieldArray, true);
    $this->log($prompt, -1, 2, 1);

    $prompt = 'row before update: ' . $this->processTable . ': ' . $this->processId . ': ' . var_export($this->getRowUpdateBefore(), true);
    $this->log($prompt, -1, 2, 1);

    $prompt = 'row after update: ' . $this->processTable . ': ' . $this->processId . ': ' . var_export($this->getRowUpdateAfter($fieldArray), true);
    $this->log($prompt, -1, 2, 1);
  }

  /**
   * initVarConfarray( )
   *
   * @return	void
   * @access private
   * @version   0.0.1
   * @since     0.0.1
   */
  private function initVarConfarray()
  {
    if ($this->confArr !== null)
    {
      return $this->confArr;
    }

    $this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deal']);
  }

  /**
   * initVars( )
   *
   * @param	string		$status     : update, edit, delete, moved
   * @param	string		$table      : label of the current table
   * @param	integer		$id         : uid of the current record
   * @param	object		$reference  : parent object
   * @return	void
   * @access private
   * @version   0.0.1
   * @since     0.0.1
   */
  private function initVars($status, $table, $id, $reference)
  {
    //$this->initVarsPobj($reference);
    $this->initVarsProcess($status, $table, $id);
  }

  /**
   * initVarsPobj( )
   *
   * @param	object		$reference  : parent object
   * @return	void
   * @access private
   * @version   0.0.1
   * @since     0.0.1
   */
  private function initVarsPobj($reference)
  {
    $this->pObj = $reference;
  }

  /**
   * initVarsProcess( )
   *
   * @param	string		$status     : update, edit, delete, moved
   * @param	string		$table      : label of the current table
   * @param	integer		$id         : uid of the current record
   * @return	void
   * @access private
   * @version   0.0.1
   * @since     0.0.1
   */
  private function initVarsProcess($status, $table, $id)
  {
    // Initial global variables
    $this->processStatus = $status;
    $this->processTable = $table;
    $this->processId = $id;
  }

  /*   * *********************************************
   *
   * row
   *
   * ******************************************** */

  /**
   * getRowUpdateAfter( )
   *
   * @param	array		$fieldArray     : Array of modified fields
   * @return	array		$rowUpdateAfter :
   * @access private
   * @version   0.0.1
   * @since     0.0.1
   */
  private function getRowUpdateAfter($fieldArray)
  {
    if ($this->rowUpdateAfter !== null)
    {
      return $this->rowUpdateAfter;
    }

    $rowUpdateBefore = $this->getRowUpdateBefore();

    foreach ($fieldArray as $key => $value)
    {
      $rowUpdateBefore[$key] = $value;
    }

    $this->rowUpdateAfter = $rowUpdateBefore;
    return $this->rowUpdateAfter;
  }

  /**
   * getRowUpdateBefore( ) : The method select the values of the given table and select and
   *                         returns the values as a marker array
   *
   * @return	array		$rowUpdateBefore :  Array with field-value pairs
   * @access private
   * @version  0.0.1
   * @since    0.0.1
   */
  private function getRowUpdateBefore()
  {
    // RETURN null  : action is new record
    if (( (int) $this->processId ) !== $this->processId)
    {
      // f.e: uid = 'NEW52248e41babcf'
      return null;
    }
    // RETURN null  : action is new record
    // RETURN : row is set before
    if ($this->rowUpdateBefore != null)
    {
      return $this->rowUpdateBefore;
    }
    // RETURN : row is set before

    $columns = array_keys($GLOBALS['TCA'][$this->processTable]['columns']);

    $select_fields = implode(', ', $columns);

    // RETURN : select fields are empty
    if (empty($select_fields))
    {
      return null;
    }
    // RETURN : select fields are empty
    // Set the query
    $from_table = $this->processTable;
    $where_clause = 'uid = ' . $this->processId;
    $groupBy = null;
    $orderBy = null;
    $limit = null;

    $query = $GLOBALS['TYPO3_DB']->SELECTquery
            (
            $select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit
    );
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

      return;
    }
    // RETURN : ERROR
    // Fetch first row only
    $this->rowUpdateBefore = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    // Free the SQL result
    $GLOBALS['TYPO3_DB']->sql_free_result($res);

    return $this->rowUpdateBefore;
  }

  /**
   * tcaIsWithoutDealMarketplaces( )
   *
   * @param	string		$table      : label of the current table
   * @return	boolean
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function tcaIsWithoutDealMarketplaces($table)
  {
    switch (true)
    {
      case(!is_array($GLOBALS['TCA'][$table]['ctrl']['tx_deal']) ):
        $prompt = $table . ' is without any configuration TCA.' . $table . '.ctrl.tx_deal';
        $this->log($prompt, -1, 2, 1);
        // RETURN : current table is without any tx_deal configuration
        return true;
      case(!is_array($GLOBALS['TCA'][$table]['ctrl']['tx_deal']['marketplaces']) ):
        $prompt = $table . ' is without any configuration TCA.' . $table . '.ctrl.tx_deal.marketplaces';
        $this->log($prompt, -1, 2, 1);
        // RETURN : current table is without any tx_deal marketplace configuration
        return true;
    }

    return false;
  }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/lib/tcemainprocdm/class.tx_deal_tcemainprocdmfieldarray.php'])
{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/lib/tcemainprocdm/class.tx_deal_tcemainprocdmfieldarray.php']);
}
?>