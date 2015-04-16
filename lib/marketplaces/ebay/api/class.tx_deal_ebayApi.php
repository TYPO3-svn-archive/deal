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
 * The class tx_deal_ebayApi bundles methods for evaluating data in backend forms
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
 *   67: class tx_deal_ebayApi
 *
 *              SECTION: main
 *   91:     public function main(  )
 *
 *              SECTION: catalogue
 *  118:     private function catalogue(  )
 *  145:     private function catalogueAdd(  )
 *  161:     private function catalogueRemove(  )
 *  177:     private function catalogueUpdate(  )
 *
 *              SECTION: item
 *  201:     private function item(  )
 *  229:     private function itemAdd(  )
 *  245:     private function itemRemove(  )
 *  261:     private function itemFixedPriceUpdate(  )
 *
 *              SECTION: Setting methods
 *  286:     public function setVarFieldarray( $fieldArray )
 *  326:     public function setVarPobj( $pObj )
 *
 * TOTAL FUNCTIONS: 11
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_deal_ebayApi
{

  public $extKey = 'deal';
  // [String] status of the current process: update, edit, delete, moved
  private $prefixLog = 'tx_deal_ebayApi ';
  // [Object] parent object
  private $pObj = null;
  // [Array] Configuration of the extension manager
  public $confArr = null;
//  // [array] datamap record
//  private $dmRecord = null;
//  // [String] label of the datamap table
//  private $dmTable = null;
//  // [String] uid of the datamap record
//  private $dmUid = null;
  // [String] ebay environment: delete || donothing || end || offer || offeragain
  private $ebayAction = null;
  // [String] ebay environment: sandbox || production
  private $ebayEnvironment = null;
  // [String] ebay marketplace
  private $ebayMarketplace = null;
  // [String] ebay mode: off || live || test
  private $ebayMode = null;
  // [String] ebay mode: off || live || test
  private $ebayUrlSignin = null;
  private $ebayUrlSigninProduction = 'https://signin.ebay.com/';
  private $ebayUrlSigninSandbox = 'https://signin.sandbox.ebay.com/';

  /*   * *********************************************
   *
   * main
   *
   * ******************************************** */

  /**
   * main( )
   *
   * @return	string    $status : isNotOnEbay, isOnEbayEnabled, isOnEbayDisabled, isWoStatus
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function main()
  {
    $status = 'undefined (by ' . __METHOD__ . ' #' . __LINE__ . ')';

    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    $this->initVars();

    if ($this->ebayMode == 'off')
    {
      return;
    }

    $status = $this->action();

    $this->logEbayEnvironment();
    return $status;
  }

  /*   * *********************************************
   *
   * item
   *
   * ******************************************** */

  /**
   * action( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function action()
  {

    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    if (!$this->itemRequirements())
    {
      return false;
    }

    $success = false;
    $this->itemFixedPriceInit();

    switch (true)
    {
      case($this->ebayAction == 'delete'):
        $success = $this->actionDelete();
        break;
      case($this->ebayAction == 'donothing'):
        $success = $this->actionDonothing();
        break;
      case($this->ebayAction == 'disable'):
        $success = $this->actionDisable();
        break;
      case($this->ebayAction == 'enableupdate'):
        $success = $this->actionEnableUpdate();
        break;
      default:
        $prompt = __METHOD__ . ' (#' . __LINE__ . '): Undefined value for ebay action: ' . $this->ebayAction;
        die($prompt);
    }

    if ($success)
    {
      $status = $this->setEbayItemStatus();
    }

    return $status;
  }

  /**
   * actionDelete( )
   *
   * @return	boolean   In every case false
   * @access private
   * @version   0.1.2
   * @since     0.0.3
   */
  private function actionDelete()
  {
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    $status = 'undefined (by ' . __METHOD__ . ' #' . __LINE__ . ')';

    $dontPrompt = true;
    $status = $this->setEbayItemStatus($dontPrompt);

    switch ($status)
    {
      case( 'isNotOnEbay'):
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDeleteWarnStatusIsNotOnEbay');
        $this->log($prompt, 3);
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDeleteInfoStatusIsNotOnEbay');
        $this->log($prompt, 0);
        break;
      case( 'isOnEbayEnabled'):
      case( 'isOnEbayDisabled'):
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDeleteWarnStatusIsOnEbay');
        $this->log($prompt, 3);
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDeleteInfoStatusIsOnEbay01');
        $prompt = str_replace('%URL%', $this->ebayUrlSignin, $prompt);
        $this->log($prompt, 0);
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDeleteInfoStatusIsOnEbay02');
        $this->log($prompt, 0);
        break;
      // #i0014, 141002, dwildt, 6+
      case( 'isWoStatus'):
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDeleteWarnStatusIsWoStatus');
        $this->log($prompt, 3);
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDeleteInfoStatusIsWoStatus');
        $this->log($prompt, 0);
        break;
      default:
        $prompt = __METHOD__ . ' (#' . __LINE__ . '): ebay status is undefined: "' . $status . '"';
        die($prompt);
    }

    return false;
  }

  /**
   * actionDonothing( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function actionDonothing()
  {
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);
  }

  /**
   * actionDisable( )
   *
   * @return	boolean $success  :
   * @access private
   * @version   0.1.2
   * @since     0.0.3
   */
  private function actionDisable()
  {
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    $success = false;
    $status = 'undefined (by ' . __METHOD__ . ' #' . __LINE__ . ')';

    $dontPrompt = true;
    $status = $this->setEbayItemStatus($dontPrompt);

    if ($this->ebayMode == 'test')
    {
      $this->logEbayMode();
      return false;
    }

    switch ($status)
    {
      case( 'isNotOnEbay'):
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDisableInfoStatusIsNotOnEbay');
        $this->log($prompt, 1);
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDisableHelpStatusIsNotOnEbay');
        $this->log($prompt, 0);
        break;
      case( 'isOnEbayEnabled'):
        $status = $this->fixedPriceItem->endFixedPriceItem();
        if ($status == 'isOnEbayDisabled')
        {
          $success = true;
        }
        break;
      case( 'isOnEbayDisabled'):
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDisableInfoStatusIsOnEbayDisabled');
        $this->log($prompt, 3);
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDisableHelpStatusIsOnEbayDisabled');
        $this->log($prompt, 0);
        break;
      // #i0014, 141002, dwildt, 6+
      case( 'isWoStatus'):
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDisableInfoStatusIsWoStatus');
        $this->log($prompt, 1);
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDisableHelpStatusIsWoStatus');
        $this->log($prompt, 0);
        break;
      default:
        $prompt = __METHOD__ . ' (#' . __LINE__ . '): ebay status is undefined: "' . $status . '"';
        die($prompt);
    }

    return $success;
  }

  /**
   * actionEnableUpdate( )
   *
   * @return	boolean $success  :
   * @access private
   * @version   0.1.2
   * @since     0.0.3
   */
  private function actionEnableUpdate()
  {
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    $status = 'undefined (by ' . __METHOD__ . ' #' . __LINE__ . ')';

    $dontPrompt = true;
    $status = $this->setEbayItemStatus($dontPrompt);

    switch ($status)
    {
      case( 'isNotOnEbay'):
        $success = $this->actionEnableUpdateIsNotOnEbay();
        break;
      case( 'isOnEbayDisabled'):
        $success = $this->actionEnableUpdateIsOnEbayDisabled();
        break;
      case( 'isOnEbayEnabled'):
        $success = $this->actionEnableUpdateIsOnEbayEnabled();
        break;
      // #i0014, 141002, dwildt, 3+
      case( 'isWoStatus'):
        $success = $this->actionEnableUpdateIsWoStatus();
        break;
      default:
        $prompt = __METHOD__ . ' (#' . __LINE__ . '): ebay status is undefined: "' . $status . '"';
        die($prompt);
    }

    return $success;
  }

  /**
   * actionEnableUpdateIsNotOnEbay( )
   *
   * @return	boolean $sucess :
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function actionEnableUpdateIsNotOnEbay()
  {
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    if (!$this->fixedPriceItem->verifyItem())
    {
      $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayItemVerfifyFailed');
      $this->log($prompt, 3);
      return false;
    }

    if ($this->ebayMode == 'test')
    {
      $this->logEbayMode();
      return false;
    }

    $success = $this->fixedPriceItem->addFixedPriceItem();
    return $success;
  }

  /**
   * actionEnableUpdateIsOnEbayDisabled( )
   *
   * @return	boolean $sucess :
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function actionEnableUpdateIsOnEbayDisabled()
  {
    $success = false;

    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    if (!$this->fixedPriceItem->verifyItem())
    {
      $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayItemVerfifyFailed');
      $this->log($prompt, 3);
      return false;
    }

    if ($this->ebayMode == 'test')
    {
      $this->logEbayMode();
      return false;
    }

    $success = $this->fixedPriceItem->relistFixedPriceItem();
    return $success;
  }

  /**
   * actionEnableUpdateIsOnEbayEnabled( )
   *
   * @return	boolean $sucess :
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function actionEnableUpdateIsOnEbayEnabled()
  {
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    $isExecuted = false;

//    if (!$this->fixedPriceItem->verifyReviseFixedPriceItem())
//    {
////      return false;
//    }

    if ($this->ebayMode == 'test')
    {
      $this->logEbayMode();
      $this->feesPromptCantEvaluated();
      return false;
    }

    $isExecuted = $this->fixedPriceItem->reviseFixedPriceItem();
    return $isExecuted;
  }

  /**
   * actionEnableUpdateIsWoStatus( )
   *
   * @return	boolean $sucess :
   * @access private
   * @internal: #i0014
   * @version   0.1.2
   * @since     0.1.2
   */
  private function actionEnableUpdateIsWoStatus()
  {
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    if (!$this->fixedPriceItem->verifyItem())
    {
      $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayItemVerfifyFailed');
      $this->log($prompt, 3);
      return false;
    }

    if ($this->ebayMode == 'test')
    {
      $this->logEbayMode();
      return false;
    }

    $success = $this->fixedPriceItem->addFixedPriceItem();
    return $success;
  }

  /**
   * feesPromptCantEvaluated( )  :
   *
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function feesPromptCantEvaluated()
  {
    $prompt = null;

    $strTitle = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayFeesTableTitle');
    $prompt = $strTitle . PHP_EOL
            . '--------------------------------------------------------------------------------------------- ' . PHP_EOL
            . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayFeesTablePromptCantEvaluated') . PHP_EOL
            . '--------------------------------------------------------------------------------------------- ' . PHP_EOL
    ;
    $this->log($prompt, 0);
  }

  /*   * *********************************************
   *
   * Getting methods
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
    $value = $this->pObj->getDatamapRecord($key);

    return $value;
  }

  /**
   * getDatamapTable( )
   *
   * @return	string  $table  : current local table
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function getDatamapTable()
  {
    $table = $this->pObj->getDatamapTable();
    return $table;
  }

//  /**
//   * getDatamapRecordUid( )
//   *
//   * @return	void
//   * @access public
//   * @version   0.0.3
//   * @since     0.0.3
//   */
//  public function getDatamapRecordUid()
//  {
//    return $this->dmUid;
//  }

  /**
   * getEbayAction( )
   *
   * @return	string  $ebayMode  : global $ebayMode
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function getEbayAction()
  {
    return $this->ebayMode;
  }

  /**
   * getEbayMode( )
   *
   * @return	string  $ebayMode  : global $ebayMode
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function getEbayMode()
  {
    return $this->ebayMode;
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
    return $this->pObj->getTcaConf();
  }

  /*   * *********************************************
   *
   * item
   *
   * ******************************************** */

  /**
   * itemFixedPriceInit( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function itemFixedPriceInit()
  {
    require_once(t3lib_extMgm::extPath($this->extKey) . 'lib/marketplaces/ebay/api/class.tx_deal_ebayApi_fixedPriceItem.php');
    $this->fixedPriceItem = new tx_deal_ebayApi_fixedPriceItem( );
    $this->fixedPriceItem->setVarPobj($this);
  }

  /**
   * itemRequirements( )
   *
   * @return	boolean
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function itemRequirements()
  {
    if (!$this->itemRequirementsEbayAction())
    {
      return false;
    }

    if ($this->itemRequirementsEbayCategoriesAreEmpty())
    {
      return false;
    }

    if ($this->itemRequirementsEbayShippingservicecodeAreEmpty())
    {
      return false;
    }

    if (!$this->itemRequirementsEbayCategoryId())
    {
      return false;
    }

    return true;
  }

  /**
   * itemRequirementsEbayAction( )
   *
   * @return	boolean
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function itemRequirementsEbayAction()
  {
    $ebayAction = false;
    switch (true)
    {
      case($this->ebayAction == 'donothing'):
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDonothingInfo');
        $this->log($prompt, 1);
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:actionDonothingHelp');
        $this->log($prompt, 0);
        $ebayAction = false;
        break;
      case($this->ebayAction == 'delete'):
      case($this->ebayAction == 'disable'):
      case($this->ebayAction == 'enableupdate'):
      case($this->ebayAction == 'offeragain'):
        $ebayAction = true;
        break;
      default:
        $ebayAction = true;
        $prompt = __METHOD__ . ' (#' . __LINE__ . '): Undefined value for ebay action: ' . $this->ebayAction;
        die($prompt);
    }

    return $ebayAction;
  }

  /**
   * itemRequirementsEbayCategoriesAreEmpty( ) :
   *
   * @return	boolean   true in case of subcategories, false in case of no subcategory
   * @access private
   * @version   0.1.0
   * @since     0.0.3
   */
  private function itemRequirementsEbayCategoriesAreEmpty()
  {
    // #i0009, 140324, dwildt, 2+
    global $TCA;
    $table = $this->getDatamapTable();

    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);
    $select_fields = "count(uid) AS 'count'";
    // #i0009, 140324, dwildt, ~
    $from_table = $TCA[$table]['columns']['tx_deal_ebaycategoryid']['config']['foreign_table'];
    $where_clause = null;
    $groupBy = null;
    $orderBy = null;
    $limit = 1;

    $query = $GLOBALS['TYPO3_DB']->SELECTquery
            (
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

      return;
    }
    // RETURN : ERROR
    // Fetch first row only
    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    // Free the SQL result
    $GLOBALS['TYPO3_DB']->sql_free_result($res);

    //var_dump(__METHOD__, __LINE__, (int) $row['count']);
    $count =  (int) $row['count'];
    if ($count >= 1)
    {
      return false;
    }

    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayCategoriesAreEmptyError');
    $this->log($prompt, 4);
    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayCategoriesAreEmptyHelp');
    $prompt = str_replace('%marketplace%', $this->ebayMarketplace, $prompt);
    $this->log($prompt, 0);
    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayItemWarnNoUpdate');
    $this->log($prompt, 3);
    return true;
  }

  /**
   * itemRequirementsEbayCategoryId( )
   *
   * @return	boolean
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function itemRequirementsEbayCategoryId()
  {
    $dmRecord = $this->pObj->getDatamapRecord();
    $csvUids = $dmRecord['tx_deal_ebaycategoryid'];
    if (empty($csvUids))
    {
      $arrUids = null;
    }
    else
    {
      $arrUids = explode(',', trim($csvUids, ','));
    }
    //var_dump(__METHOD__, __LINE__, $csvUids, $arrUids);

    if (!$this->itemRequirementsEbayCategoryIdOne($arrUids))
    {
      return false;
    }

    if ($this->itemRequirementsEbayCategoryIdSubCategories($arrUids[0]))
    {
      return false;
    }

    return true;
  }

  /**
   * itemRequirementsEbayCategoryIdOne( ) : Checks if the record is linked with one category exactly.
   *
   * @param array     $arrUids  : Array with the uids of the categories of the current local table
   * @return	boolean   true in case of one category, false in case of no category or more than one category
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function itemRequirementsEbayCategoryIdOne($arrUids)
  {
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    if (count($arrUids) == 1 && !empty($arrUids))
    {
      return true;
    }

    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayCategoriesNotOne');
    $this->log($prompt, 4, 2);
    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayItemWarnNoUpdate');
    $this->log($prompt, 3);
    return false;
  }

  /**
   * itemRequirementsEbayCategoryIdSubCategories( ) : Checks weather the given category has subcategories or not.
   *
   * @param integer     $uid  :
   * @return	boolean   true in case of subcategories, false in case of no subcategory
   * @access private
   * @version   0.1.0
   * @since     0.0.3
   */
  private function itemRequirementsEbayCategoryIdSubCategories($uid)
  {
    // #i0009, 140324, dwildt, 2+
    global $TCA;
    $table = $this->getDatamapTable();

    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);
    $select_fields = '*';
    // #i0009, 140324, dwildt, ~
    $from_table = $TCA[$table]['columns']['tx_deal_ebaycategoryid']['config']['foreign_table'];
    $where_clause = 'uid_parent = ' . $uid;
    $groupBy = null;
    $orderBy = null;
    $limit = null;

    $query = $GLOBALS['TYPO3_DB']->SELECTquery
            (
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

      return;
    }
    // RETURN : ERROR
    // Fetch first row only
    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    //var_dump(__METHOD__, __LINE__, $row);
    // Free the SQL result
    $GLOBALS['TYPO3_DB']->sql_free_result($res);

    if (!$row)
    {
      return false;
    }

    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebaySubCategories');
    $this->log($prompt, 4);
    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayItemWarnNoUpdate');
    $this->log($prompt, 3);
    return true;
  }

  /**
   * itemRequirementsEbayShippingservicecodeAreEmpty( ) :
   *
   * @return	boolean   true in case of subcategories, false in case of no subcategory
   * @access private
   * @version   0.1.0
   * @since     0.0.3
   */
  private function itemRequirementsEbayShippingservicecodeAreEmpty()
  {
    // #i0009, 140324, dwildt, 2+
    global $TCA;
    $table = $this->getDatamapTable();

    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);
    $select_fields = "count(uid) AS 'count'";
    // #i0009, 140324, dwildt, ~
    $from_table = $TCA[$table]['columns']['tx_deal_ebayshippingservicecode']['config']['foreign_table'];
    $where_clause = null;
    $groupBy = null;
    $orderBy = null;
    $limit = 1;

    $query = $GLOBALS['TYPO3_DB']->SELECTquery
            (
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

      return;
    }
    // RETURN : ERROR
    // Fetch first row only
    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    // Free the SQL result
    $GLOBALS['TYPO3_DB']->sql_free_result($res);

    //var_dump(__METHOD__, __LINE__, (int) $row['count']);
    $count =  (int) $row['count'];
    if ($count >= 1)
    {
      return false;
    }

    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayShippingservicecodeAreEmptyError');
    $this->log($prompt, 4);
    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayShippingservicecodeAreEmptyHelp');
    $prompt = str_replace('%marketplace%', $this->ebayMarketplace, $prompt);
    $this->log($prompt, 0);
    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayItemWarnNoUpdate');
    $this->log($prompt, 3);
    return true;
  }

  /*   * *********************************************
   *
   * Setting methods
   *
   * ******************************************** */

  /**
   * log( )
   *
   * @param	string		$prompt : prompt
   * @param	integer		$status : -1 = no flash message, 0 = notice, 1 = info, 3 = OK, 4 = warn, 5 = error
   * @param	string		$action : 0=No category, 1=new record, 2=update record, 3= delete record, 4= move record, 5= Check/evaluate
   * @param	string		$header : 0=No header, 1=Deal! TYPO3 for amazon and ebay, 2=Deal! TYPO3 for amazon and ebay
   * @return	void
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function log($prompt, $status = -1, $action = 2, $header = 2)
  {
    $this->pObj->log($prompt, $status, $action, $header);
  }

  /**
   * logEbayEnvironment( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function logEbayEnvironment()
  {
    if ($this->ebayEnvironment != 'sandbox')
    {
      return;
    }

    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayEnvironmentSandbox');
    $this->log($prompt, 0);
  }

  /**
   * logEbayMode( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function logEbayMode()
  {
    if ($this->ebayMode != 'test')
    {
      return;
    }

    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayModeTest');
    $this->log($prompt, 3);
  }

  /**
   * setEbayItemStatus( )
   *
   * @param   boolean   $dontPrompt : do not prompt to the backend form (optional)
   * @return	string    $status     : isNotOnEbay, isOnEbayEnabled, isOnEbayDisabled, isWoStatus
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function setEbayItemStatus($dontPrompt = false)
  {
    $status = 'undefined (by ' . __METHOD__ . ' #' . __LINE__ . ')';

    if (!is_object($this->fixedPriceItem))
    {
      $this->itemFixedPriceInit();
    }

    $status = $this->fixedPriceItem->setEbayItemStatus($dontPrompt);
    return $status;
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
    $this->pObj->setDatamapRecordFieldPrepend($key, $value, $boolWiEol);
  }

  /**
   * setDatamapRecordFieldUpdate( )
   *
   * @param   string  $key        :
   * @param   string  $value      :
   * @param   string  $boolPrompt : prompt in case of changing the value (optional)
   * @return	array $dmRecord : global $dmRecord
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function setDatamapRecordFieldUpdate($key, $value, $boolPrompt = true)
  {
    $this->pObj->setDatamapRecordFieldUpdate($key, $value, $boolPrompt);
  }

  /**
   * setVarPobj( )  :
   *
   * @param	object		$pObj: ...
   * @return	void
   * @access public
   * @version    0.0.3
   * @since      0.0.3
   */
  public function setVarPobj(&$pObj)
  {
    if (!is_object($pObj))
    {
      $prompt = 'ERROR: no parent object!<br />' . PHP_EOL .
              'Sorry for the trouble.<br />' . PHP_EOL .
              'TYPO3 Deal!<br />' . PHP_EOL .
              __METHOD__ . ' (' . __LINE__ . ')';
      die($prompt);
    }
    $this->pObj = $pObj;

//$prompt = 'debug trail: ' . t3lib_utility_Debug::debugTrail( ) . PHP_EOL .
//          'TYPO3 Caddy<br />' . PHP_EOL .
//        __METHOD__ . ' (' . __LINE__ . ')';
//echo $prompt;
//    if( ! is_object( $pObj->drs ) )
//    {
//      $prompt = 'ERROR: no DRS object!<br />' . PHP_EOL .
//                'Sorry for the trouble.<br />' . PHP_EOL .
////                'debug trail: ' . t3lib_utility_Debug::debugTrail( ) . PHP_EOL .
//                'TYPO3 Caddy<br />' . PHP_EOL .
//              __METHOD__ . ' (' . __LINE__ . ')';
//      die( $prompt );
//
//    }
//
//    $this->drs = $pObj->drs;
  }

  /**
   * initVars( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function initVars()
  {
    $this->initVarsConfArr();
    //$this->initVarsDatamap();
    $this->initVarsEbay();
  }

  /**
   * initVarConfArr( )
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
   * initVarsEbay( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function initVarsEbay()
  {
    $this->initVarsEbayEnvironment();
    $this->initVarsEbayMarketplace();
    $this->initVarsEbayMode();
    $this->initVarsEbayAction();
    $this->initVarsEbayUrlSignin();
  }

  /**
   * initVarsEbayAction( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function initVarsEbayAction()
  {
    $dmRecord = $this->pObj->getDatamapRecord();

    $this->ebayAction = $dmRecord['tx_deal_ebayaction'];

    switch (true)
    {
      case($this->ebayAction == 'delete'):
      case($this->ebayAction == 'donothing'):
      case($this->ebayAction == 'disable'):
      case($this->ebayAction == 'enableupdate'):
      case($this->ebayAction == 'offeragain'):
        $prompt = __METHOD__ . ' (#' . __LINE__ . '): ebay action: ' . $this->ebayAction;
        $this->log($prompt, -1);
        break;
      default:
        // #i0029, 150416, dwildt, 2-/3+
//        $prompt = __METHOD__ . ' (#' . __LINE__ . '): Undefined value for ebay action: "' . $this->ebayAction . '"';
//        die($prompt);
        $prompt = __METHOD__ . ' (#' . __LINE__ . '): Undefined value for ebay action: "' . $this->ebayAction . '". Action is set to "donothing"';
        $this->ebayAction = 'donothing';
        $this->log($prompt, 0);
        break;
    }
  }

  /**
   * initVarsEbayEnvironment( ) :
   *
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function initVarsEbayEnvironment()
  {
    $tcaConf = $this->getTcaConf();
    $this->ebayEnvironment = $tcaConf['environment']['key'];
  }

  /**
   * initVarsEbayMarketplace( ) :
   *
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function initVarsEbayMarketplace()
  {
    $tcaConf = $this->getTcaConf();
    $this->ebayMarketplace = $tcaConf['marketplace'];
  }

  /**
   * initVarsEbayMode( )
   *
   * @return	void
   * @access private
   * @version   6.0.13
   * @since     0.0.3
   */
  private function initVarsEbayMode()
  {
    $dmRecord = $this->pObj->getDatamapRecord();

    $this->ebayMode = $dmRecord['tx_deal_ebaymode'];

    switch (true)
    {
      case($this->ebayMode == 'live'):
      case($this->ebayMode == 'off'):
      case($this->ebayMode == 'test'):
        $prompt = __METHOD__ . ' (#' . __LINE__ . '): ebay mode: ' . $this->ebayMode;
        $this->log($prompt, -1);
        break;
      default:
        // #i0029, 150416, dwildt, 2-/3+
//        $prompt = __METHOD__ . ' (#' . __LINE__ . '): Undefined value for ebay mode: ' . $this->ebayMode;
//        die($prompt);
        $prompt = __METHOD__ . ' (#' . __LINE__ . '): Undefined value for ebay mode: "' . $this->ebayMode . '". mode is set to "off"';
        $this->ebayMode = 'off';
        $this->log($prompt, 0);
        break;
    }
  }

  /**
   * initVarsEbayUrlSignin( ) :
   *
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function initVarsEbayUrlSignin()
  {
    switch ($this->ebayEnvironment)
    {
      case('sandbox'):
        $this->ebayUrlSignin = $this->ebayUrlSigninSandbox;
        break;
      case('production'):
      default:
        $this->ebayUrlSignin = $this->ebayUrlSigninProduction;
        break;
    }
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/lib/marketplaces/ebay/api/class.tx_deal_ebayApi.php'])
{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/lib/marketplaces/ebay/api/class.tx_deal_ebayApi.php']);
}
?>