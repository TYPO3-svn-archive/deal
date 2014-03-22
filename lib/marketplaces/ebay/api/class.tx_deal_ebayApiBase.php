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
 * plugin 'ebay sample start' for the 'deal' extension.
 *
 * @author	Dirk Wildt <http://wildt.at.die-netzmacher.de>
 * @package	TYPO3
 * @subpackage	tx_deal
 * @internal    #i0003
 * @version     0.0.3
 * @since       0.0.3
 */
class tx_deal_ebayApiBase
{

  // ebay API
  protected $ebayApiConfFromTCA = array();  // Configuration from the TCA ctrl section of the current table.
  protected $ebayApiEndpointXml = null;
  protected $ebayApiEndpointXmlSandbox = 'https://api.sandbox.ebay.com/ws/api.dll';
  protected $ebayApiEndpointXmlProduction = 'https://api.ebay.com/ws/api.dll';
  protected $ebayApiToken = '861';   // Trading API version. See: http://developer.ebay.com/webservices/latest/ebaySvc.xsd
  protected $ebayApiVersion = '861';   // Trading API version. See: http://developer.ebay.com/webservices/latest/ebaySvc.xsd
  // ebay environment
  protected $ebayEnvironment = null; // null, sandbox, production
  // ebay error code
  protected $ebayErrorCode = 0;  // 0 (default): no error
  // ebay fields
  private $ebayFieldCategoryId = null; // ebay category id
  // ebay item ID
  protected $ebayItemId = null;
  // ebay marketplace
  protected $ebayMarketplace = null;
  protected $ebayMarketplaceCountry = null;
  protected $ebayMarketplaceCurrency = null;
  protected $ebayMarketplaceGlobalId = null;
  protected $ebayMarketplaceSiteId = null;
  protected $ebayPaypalEmail = null;

  private $init = null;

  /*   * *********************************************
   *
   * Evaluation
   *
   * ******************************************** */

  /**
   * evalResponse( )  :
   *
   * @param   array     $xmlRequest   :
   * @param   object    $xmlResponse  :
   * @param   string    $action       : add || update
   * @param   string    $xmlAction    : addFixedPriceItem, getItem
   * @return	boolean
   * @access protected
   * @version  0.0.3
   * @since    0.0.3
   */
  protected function evalResponse($xmlRequest, $xmlResponse, $action = null, $xmlAction = null)
  {
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    if ($this->evalResponsePromptError($xmlRequest, $xmlResponse, $action, $xmlAction))
    {
      return false;
    }

    $this->evalResponsePromptSuccess($xmlRequest, $xmlResponse, $action, $xmlAction);
    return true;
  }

  /**
   * evalResponsePromptError( )  :
   *
   * @param   array     $xmlRequest  :
   * @param   object    $xmlResponse  :
   * @return	boolean
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptError($xmlRequest, $xmlResponse, $action = null, $xmlAction = null)
  {
    switch (true)
    {
      case ( $this->evalResponsePromptErrorBody($xmlRequest, $xmlResponse, $xmlAction)):
      case ( $this->evalResponsePromptErrorErrors($xmlRequest, $xmlResponse, $action, $xmlAction)):
        return true;
    }

    return false;
  }

  /**
   * evalResponsePromptErrorBody( )  :
   *
   * @param   array     $xmlRequest  :
   * @param   object    $xmlResponse  :
   * @return	boolean
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptErrorBody($xmlRequest, $xmlResponse, $xmlAction)
  {
    if (!$xmlResponse->Body)
    {
      $prompt = 'xmlResponse->Body is empty or isn\'t set.';
      $this->log($prompt, -1);
      return false;
    }

    $prompt = 'Environment: ' . $this->ebayEnvironment;
    $this->log($prompt, -1);

    $prompt = 'Request: ' . $xmlAction;
    $this->log($prompt, -1);

    $prompt = 'xmlRequest: ' . PHP_EOL . $xmlRequest;
    $this->log($prompt, 4); // Workaround: 4, because XML code won't prompt to -1 (log only)

    $prompt = 'xmlResponse: ' . $xmlResponse->Body;
    $this->log($prompt, 4);

    return true;
  }

  /**
   * evalResponsePromptErrorErrors( )  :
   *
   * @param   array     $xmlRequest   :
   * @param   object    $xmlResponse  :
   * @return	boolean                 : false in case of no errors or update (duplicate entry), true in case of failure
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptErrorErrors($xmlRequest, $xmlResponse, $action = null, $xmlAction = null)
  {
//    if( !is_object($xmlResponse->errors))
//    {
//      $prompt = 'xmlResponse->errors isn\'t an object.';
    if (!$xmlResponse->Errors)
    {
      $prompt = 'xmlResponse->Errors is empty or isn\'t set.';
      $this->log($prompt, -1);
      return false;
    }

    $ShortMessage = $xmlResponse->Errors->ShortMessage;
    $LongMessage = $xmlResponse->Errors->LongMessage;
    $this->ebayErrorCode = (int) $xmlResponse->Errors->ErrorCode;
    $SeverityCode = $xmlResponse->Errors->SeverityCode;
    $ErrorClassification = $xmlResponse->Errors->ErrorClassification;
    $Version = $xmlResponse->Version;

    switch ($this->ebayErrorCode)
    {
      case(196): // Item cannot be relisted. This item was not relisted because the listing does not exist or is still active.
        $this->evalResponsePromptErrorErrors00000196();
        return true;
      case(291): // Auction ended. You're not allowed to revise ended listings.
        $this->evalResponsePromptErrorErrors00000291();
        return true;
      case(1047): // The auction has already been closed.
        $followTheWorkflow = $this->evalResponsePromptErrorErrors00001047($action);
        if (!$followTheWorkflow)
        {
          return true;
        }
        break;
      case(21919067): // Listing breaches the Duplicate listings policy.
        $this->evalResponsePromptErrorErrors21919067($xmlRequest, $xmlResponse, $action);
        return false;
      default:
        // follow the workflow
        break;
    }

    $prompt = 'Environment: ' . $this->ebayEnvironment;
    $this->log($prompt, -1);

    $prompt = 'Request: ' . $xmlAction;
    $this->log($prompt, -1);

    $prompt = 'ebay API endpoint: ' . $this->ebayApiEndpointXml;
    $this->log($prompt, -1);
    $prompt = 'ebay API token: ' . $this->ebayApiToken;
    $this->log($prompt, -1);
    $prompt = 'TCA configuration: ' . var_export($this->ebayApiConfFromTCA, true);
    $this->log($prompt, -1);
    $prompt = 'xmlRequest: ' . PHP_EOL . $xmlRequest;
    $this->log($prompt, 4); // Workaround: 4, because XML code won't prompt to -1 (log only)
    $prompt = 'xmlResponse: ' . PHP_EOL . var_export($xmlResponse, true);
    $this->log($prompt, -1);

    $promptsByEbay = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:promptsByEbay');
    $promptsByTYPO3 = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:promptsByTYPO3');
    $promptDetailsToSyslog = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:promptDetailsToSyslog');

    $prompt = $promptsByEbay . ': ' . PHP_EOL
            . '* ' . $ShortMessage . PHP_EOL
            . '* ' . $LongMessage . PHP_EOL
            . '* ' . 'Error code: ' . $this->ebayErrorCode . '. Severity code: ' . $SeverityCode . '. Error classification: ' . $ErrorClassification . '. '
            . 'API version: ' . $Version . PHP_EOL
            . $promptsByTYPO3 . ': ' . PHP_EOL
            . '* Environment: ' . $this->ebayEnvironment . PHP_EOL
            . '* ' . $promptDetailsToSyslog
    ;
    $this->log($prompt, 4);

    $xml = str_replace('><', '>' . PHP_EOL . '<', $xmlRequest);
    $this->log($xml, -1);

    $xml = $xmlResponse->asXML();
    $xml = str_replace('><', '>' . PHP_EOL . '<', $xml);
    $this->log($xml, -1);

    return true;
  }

  /**
   * evalResponsePromptErrorErrors00000196(): Item cannot be relisted. This item was not relisted because the listing does not exist or is still active.
   *
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptErrorErrors00000196()
  {
    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayErrorRelistNotPossible');
    $this->log($prompt, 4);
    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayHelpRelistNotPossible');
    $this->log($prompt, 1);
  }

  /**
   * evalResponsePromptErrorErrors00000291(): Auction ended. You're not allowed to revise ended listings
   *
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptErrorErrors00000291()
  {
    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayErrorReviseEndedListings');
    $this->log($prompt, 4);
    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayHelpReviseEndedListings');
    $this->log($prompt, 1);
  }

  /**
   * evalResponsePromptErrorErrors00001047( )  : The auction has already been closed.
   *
   * @param   string    $action             :
   * @return	boolean   $followTheWorkflow  : true, if $action isn't end, false if it is.
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptErrorErrors00001047($action)
  {
    $followTheWorkflow = true;

    if ($action != 'end')
    {
      return $followTheWorkflow;
    }

    $followTheWorkflow = false;

    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayErrorAuctionAlreadyClosed');
    $this->log($prompt, 3);
    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayHelpAuctionAlreadyClosed');
    $this->log($prompt, 1);
    return $followTheWorkflow;
  }

  /**
   * evalResponsePromptErrorErrors21919067( )  :  Listing breaches the Duplicate listings policy.
   *
   * @param   array     $xmlRequest   :
   * @param   object    $xmlResponse  :
   * @return	boolean                 : false in case of no errors or update (duplicate entry), true in case of failure
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptErrorErrors21919067($xmlRequest, $xmlResponse, $action = null)
  {
    if ($action == 'update')
    {
      return;
    }
    foreach ($xmlResponse->Errors->ErrorParameters as $ErrorParameter)
    {
      switch ($ErrorParameter['ParamID'])
      {
        case '0': // 0: title of the item
          // follow the workflow
          break;
        case '1': // 1: ebay item id
          $this->ebayItemId = (string) $ErrorParameter->Value;
          $boolPrompt = true;
          $this->setDatamapRecordFieldUpdate('tx_deal_ebayitemid', $this->ebayItemId, $boolPrompt);
          break;
      }
    }

    $prompt = 'xmlResponse short message: ' . $xmlResponse->Errors->ShortMessage;
    $this->log($prompt, 3);
    $prompt = 'xmlResponse ebay item id: ' . $this->ebayItemId;
    $this->log($prompt, 3);
//    $xml = str_replace('><', '>' . PHP_EOL . '<', $xmlRequest);
//    $this->log($xml, 3);
    unset($xmlRequest);
  }

  /**
   * evalResponsePromptSuccess( )  :
   *
   * @param   array     $xmlRequest  :
   * @param   object    $xmlResponse  :
   * @param   object    $xmlAction  :
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptSuccess($xmlRequest, $xmlResponse, $action = null, $xmlAction = null)
  {
    $prompt = 'Environment: ' . $this->ebayEnvironment;
    $this->log($prompt, -1);

    $prompt = 'Request: ' . $xmlAction;
    $this->log($prompt, -1);

    $xml = 'xmlRequest (success): ' . str_replace('><', '>' . PHP_EOL . '<', $xmlRequest);
    $this->log($xml, -1);

    $xml = 'xmlResponse (success): ' . $xmlResponse->asXML();
    $xml = str_replace('><', '>' . PHP_EOL . '<', $xml);
    $this->log($xml, -1);

    //var_dump(__METHOD__, __LINE__, $xmlAction);
    switch (true)
    {
      case( $action == 'setEbayItemStatus'):
        $this->evalResponseSetEbayItemStatus($xmlResponse);
        break;
      case( $xmlAction == 'AddFixedPriceItem'):
        $this->evalResponsePromptSuccessAddFixedPriceItem($xmlResponse);
        break;
      case( $xmlAction == 'EndFixedPriceItem'):
        $this->evalResponsePromptSuccessEndFixedPriceItem($xmlResponse);
        break;
      case( $xmlAction == 'GetItem'):
        $this->evalResponsePromptSuccessGetItem($xmlResponse);
        break;
      default:
        // do nothing
        break;
    }
  }

  /**
   * evalResponsePromptSuccessAddFixedPriceItem( )  :
   *
   * @param   object    $xmlResponse  :
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptSuccessAddFixedPriceItem($xmlResponse)
  {
    $ItemID = (string) $xmlResponse->ItemID;
    //var_dump(__METHOD__, __LINE__, $xmlResponse);
    if (!empty($ItemID))
    {
      $this->ebayItemId = $ItemID;
      //var_dump(__METHOD__, __LINE__, $this->ebayItemId);
      $boolPrompt = false;
      $this->setDatamapRecordFieldUpdate('tx_deal_ebayitemid', $this->ebayItemId, $boolPrompt);
      return;
    }

    $prompt = __METHOD__ . ' (#' . __LINE__ . '): Fatal error: ItemID is empty!';
    die($prompt);
  }

  /**
   * evalResponsePromptSuccessEndFixedPriceItem( )  :
   *
   * @param   object    $xmlResponse  :
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptSuccessEndFixedPriceItem($xmlResponse)
  {
    // Do nothing
    unset($xmlResponse);
  }

  /**
   * evalResponsePromptSuccessGetItem( )  :
   *
   * @param   object    $xmlResponse  :
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptSuccessGetItem($xmlResponse)
  {
    $url = $xmlResponse->Item->ListingDetails->ViewItemURL;
    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayUpdateSuccess');
    $prompt = str_replace('%url%', $url, $prompt);
    $this->log($prompt, 2);

    $key = 'tx_deal_ebayresponse';

    $value = date('y-m-d H:i: ') . $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayUpdateSuccessShort');
    $this->setDatamapRecordFieldPrepend($key, $value);
    $value = date('y-m-d H:i: ') . $url;
    $this->setDatamapRecordFieldPrepend($key, $value);
  }

  /**
   * evalResponseSetEbayItemStatus( )  :
   *
   * @param   object    $xmlResponse  :
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponseSetEbayItemStatus($xmlResponse)
  {
    $response = array(
      'EndTime' => (string) $xmlResponse->Item->ListingDetails->EndTime,
      'EndingReason' => (string) $xmlResponse->Item->ListingDetails->EndingReason,
      'ViewItemURL' => (string) $xmlResponse->Item->ListingDetails->ViewItemURL,
      'QuantitySold' => (integer) $xmlResponse->Item->SellingStatus->QuantitySold,
      'Quantity' => (integer) $xmlResponse->Item->Quantity
    );

    switch (true)
    {
      case(strtotime($response['EndTime']) <= time()):  // endtime is in the past
        $value = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayItemIsOnEbayDisabled');
        break;
      case(strtotime($response['EndTime']) > time()):  // endtime is in the future
        $value = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayItemIsOnEbayEnabled');
      default:
        break;
    }

    $ebayEnvironment = $this->ebayEnvironment;
    if ($ebayEnvironment == 'sandbox')
    {
      $ebayEnvironment = ' (' . $ebayEnvironment . ')';
    }
    else
    {
      $ebayEnvironment = null;
    }
    $value = str_replace('%ebayEnvironment%', $ebayEnvironment, $value);
    $value = str_replace('%Quantity%', $response['Quantity'], $value);
    $value = str_replace('%QuantitySold%', $response['QuantitySold'], $value);
    $value = str_replace('%ViewItemURL%', $response['ViewItemURL'], $value);

    $key = 'tx_deal_ebayitemstatus';
    $boolPrompt = false;
    $this->setDatamapRecordFieldUpdate($key, $value, $boolPrompt);
  }

  /*   * *********************************************
   *
   * Fees
   *
   * ******************************************** */

  /**
   * feesPrompt( )  :
   *
   * @param   object    $xmlResponse  :
   * @return	void
   * @access protected
   * @version  0.0.3
   * @since    0.0.3
   */
  protected function feesPrompt($xmlResponse)
  {
    $prompt = null;
    $sum = 0.00;
    $currency = $this->ebayMarketplaceCurrency;

    foreach ((array) $xmlResponse->Fees as $Fees)
    {
      foreach ((array) $Fees as $Fee)
      {
        $key = $Fee->Name;
        $value = (double) $Fee->Fee;
        $currency = $Fee->Fee['currencyID'];
        if ($value > 0)
        {
          $prompt = $prompt
                  . '+ ' . sprintf('%01.2f', $value) . ' ' . $currency . ' : ' . $key . PHP_EOL
          ;
          $sum = $sum + $value;
        }
      }
    }

    $strTitle = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayFeesTableTitle');
    $strWoFees = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayFeesTableNoFees');
    $strWoWarranty = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayFeesTableNoWarranty');
    $strSum = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayFeesTableSum');
    if (empty($prompt))
    {
      $prompt = '+ 0.00 ' . $currency . ' : ' . $strWoFees . PHP_EOL;
    }
    $prompt = $strTitle . PHP_EOL
            . '--------------------------------------------------------------------------------------------- ' . PHP_EOL
            . $prompt
            . '--------------------------------------------------------------------------------------------- ' . PHP_EOL
            . '# ' . sprintf('%01.2f', $sum) . ' ' . $currency . ' : ' . $strSum . '*' . PHP_EOL
            . '--------------------------------------------------------------------------------------------- ' . PHP_EOL
            . PHP_EOL
            . '*' . $strWoWarranty
    ;
    $this->log($prompt, 0);

    if ($this->getEbayMode() != 'test')
    {
      $strWoWarrantyShort = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayFeesTableNoWarrantyShort');
      $key = 'tx_deal_ebayresponse';
      $value = date('y-m-d H:i: ') . $strTitle . ': ' . sprintf('%01.2f', $sum) . ' ' . $currency . ' (' . $strWoWarrantyShort . ')';
      $this->setDatamapRecordFieldPrepend($key, $value);
    }
  }

  /*   * *********************************************
   *
   * get action
   *
   * ******************************************** */

  /**
   * getAction( ) :
   *
   * @return	mixed     :
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  public function getAction()
  {
//    $prompt = __METHOD__ . ' #' . __LINE__ . ': ' . $this->ebayItemId;
//    $this->log($prompt, -1);

    $action = 'add';

    switch (true)
    {
      case(!empty($this->ebayItemId)): //
        $action = 'update';
        break;
      case($this->ebayErrorCode == 0): // No error occurs
        $action = 'add';
        break;
      case($this->ebayErrorCode == 21919067): // Listing breaches the Duplicate listings policy.
        $action = 'update';
        break;
      default:
        $action = 'error';
        break;
    }
    return $action;
  }

  /*   * *********************************************
   *
   * Datamap
   *
   * ******************************************** */

  /**
   * getDatamapRecord( )  : Returns the datamap record, if $key isn't given, else ist returns the value of the given $key.
   *
   * @param   string    $key              : label of the field of the record, which value should returned (optional)
   * @return	mixed     $arrValues/$value : if key is given $value else $arrValues
   * @access protected
   * @version   0.0.3
   * @since     0.0.3
   */
  protected function getDatamapRecord($key = null)
  {
    $value = $this->pObj->getDatamapRecord($key);

    return $value;
  }

  /**
   * getDatamapValueByTcaConfField( ) :
   *
   * @param string $field :
   * @return	mixed     :
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function getDatamapValueByTcaConfField($field)
  {
    $key = $this->getTcaConfFields($field);
    $value = $this->pObj->getDatamapRecord($key);

    return $value;
  }

  /*   * *********************************************
   *
   * Request AddFixedPriceItem
   *
   * ******************************************** */

  /**
   * getRequestContentAddItem( )  : Create the XML request to be POSTed
   *
   * @param   string      $forceItemID   :
   * @return	string      $xmlrequest :
   * @access protected
   * @version  0.0.3
   * @since    0.0.3
   */
  protected function getRequestContentAddItem($forceItemID = false)
  {
    //var_dump(__METHOD__, __LINE__, $this->pObj->confArr, $this->pObj->getDatamapRecord(), $this->getTcaConf());
    // fields
    $CategoryID = trim($this->pObj->getDatamapRecord('tx_deal_ebaycategoryid'), ',');
    $ConditionID = $this->pObj->getDatamapRecord('tx_deal_ebayconditionid'); // http://developer.ebay.com/DevZone/finding/CallRef/Enums/conditionIdList.html
    $Country = $this->getRequestContentAddItemFieldsCountry();
    $Currency = $this->ebayMarketplaceCurrency;
    $currencyID = $this->ebayMarketplaceCurrency;
    $Description = $this->getRequestContentAddItemFieldsDescription();
    $DispatchTimeMax = $this->pObj->getDatamapRecord('tx_deal_ebaydispatchtimemax');
    $eBayAuthToken = $this->ebayApiToken;
    $ErrorLanguage = $this->getRequestContentAddItemFieldsErrorLanguage();
    $ItemID = $this->getRequestContentAddItemFieldsItemID($forceItemID);
    $ListingDuration = $this->pObj->getDatamapRecord('tx_deal_ebaylistingduration'); // http://developer.ebay.com/devzone/xml/docs/reference/ebay/types/ListingDurationCodeType.html
    $Location = $this->pObj->getDatamapRecord('tx_deal_ebaylocation');
    $PictureDetails = $this->getRequestContentAddItemFieldsPictureDetails();
    $ProductListingDetails = $this->getRequestContentAddItemFieldsProductListingDetails();
    $Quantity = $this->pObj->getDatamapRecord('tx_deal_ebayquantity');
    $PaymentMethods = $this->getRequestContentAddItemXmlPaymentmethods();
    $ReturnPolicy = $this->getRequestContentAddItemXmlReturnpolicy();
    $SiteCodeType = $this->ebayMarketplaceCountry;
    $ShippingService = $this->getRequestContentAddItemFieldsShippingservicecode();
    $ShippingServiceAdditionalCosts = $this->pObj->getDatamapRecord('tx_deal_ebayshippingserviceadditionalcosts');
    $ShippingServiceCosts = $this->pObj->getDatamapRecord('tx_deal_ebayshippingservicecosts');
    $SKU = $this->getDatamapValueByTcaConfField('sku');
    $StartPrice = $this->getDatamapValueByTcaConfField('gross');
    $Title = '<![CDATA[' . $this->getDatamapValueByTcaConfField('title') . ']]>';
    // XML
    $xmlrequestContent = '  <RequesterCredentials>
  <eBayAuthToken>' . $eBayAuthToken . '</eBayAuthToken>
</RequesterCredentials>
<ErrorLanguage>' . $ErrorLanguage . '</ErrorLanguage>
<WarningLevel>High</WarningLevel>
<Item>
  <CategoryBasedAttributesPrefill>true</CategoryBasedAttributesPrefill>
  <CategoryMappingAllowed>true</CategoryMappingAllowed>
  <ConditionID>' . $ConditionID . '</ConditionID>
  <Country>' . $Country . '</Country>
  <Currency>' . $Currency . '</Currency>
  <DispatchTimeMax>' . $DispatchTimeMax . '</DispatchTimeMax>
  <Description>' . $Description . '</Description>
  ' . $ItemID . '
  <ListingDuration>' . $ListingDuration . '</ListingDuration>
  <ListingType>FixedPriceItem</ListingType>
  <Location>' . $Location . '</Location>
  ' . $PaymentMethods . '
  ' . $PictureDetails . '
  <PrimaryCategory>
    <CategoryID>' . $CategoryID . '</CategoryID>
  </PrimaryCategory>
  ' . $ProductListingDetails . '
  <Quantity>' . $Quantity . '</Quantity>
  ' . $ReturnPolicy . '
  <Site>' . $SiteCodeType . '</Site>
  <ShippingDetails>
    <ShippingType>Flat</ShippingType>
    <ShippingServiceOptions>
      <ShippingServicePriority>1</ShippingServicePriority>
      <ShippingService>' . $ShippingService . '</ShippingService>
      <ShippingServiceAdditionalCost>' . $ShippingServiceAdditionalCosts . '</ShippingServiceAdditionalCost>
      <ShippingServiceCost>' . $ShippingServiceCosts . '</ShippingServiceCost>
    </ShippingServiceOptions>
  </ShippingDetails>
  <SKU>' . $SKU . '</SKU>
  <StartPrice currencyID="' . $currencyID . '">' . $StartPrice . '</StartPrice>
  <Title>' . $Title . '</Title>
</Item>';
    return $xmlrequestContent;
  }

  /**
   * getRequestContentAddItemFieldsCountry( )  :
   *
   * @return	string      $country :
   * @link    http://developer.ebay.com/DevZone/XML/docs/reference/ebay/types/CountryCodeType.html
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsCountry()
  {
    list( $ebay, $countryCode) = explode('-', $this->ebayMarketplaceGlobalId);
    unset($ebay);
    //var_dump(__METHOD__, __LINE__, $this->ebayMarketplaceCountry, $this->ebayMarketplaceGlobalId, $this->ebayMarketplaceSiteId);
    if (empty($countryCode))
    {
      $countryCode = 'US';
    }
    return $countryCode;
  }

  /**
   * getRequestContentAddItemFieldsDescription( )  :
   *
   * @return	string      $description : item description in HTML format
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsDescription()
  {
    $description = null
            . '<div style="font-family:verdana,arial;font-size:.9em;">'
            . $this->getRequestContentAddItemFieldsDescriptionTitle()
            . $this->getRequestContentAddItemFieldsDescriptionShort()
            . $this->getRequestContentAddItemFieldsDescriptionDescription()
            . $this->getRequestContentAddItemFieldsDescriptionDatasheet()
            . $this->getRequestContentAddItemFieldsDescriptionFilterCategories()
            . $this->getRequestContentAddItemFieldsDescriptionFilterDimensions()
            . $this->getRequestContentAddItemFieldsDescriptionFilterMaterial()
            . $this->getRequestContentAddItemFieldsDescriptionEbaypaymentmethodsdescription()
            . '</div>'
    ;

    return '<![CDATA[' . $description . ']]>';
  }

  /**
   * getRequestContentAddItemFieldsDescriptionDatasheet( )  : obligate
   *
   * @return	string      $datasheet : datasheet in HTML format (unordered list)
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsDescriptionDatasheet()
  {
    $arrDescription = $this->getTcaConfFields('description');
    $field = $arrDescription['datasheet'];
    if (empty($field))
    {
      return null;
    }
    $datasheet = $this->pObj->getDatamapRecord($field);
    if (empty($datasheet))
    {
      return null;
    }
    $lines = explode(PHP_EOL, $datasheet);
    foreach ($lines as $key => $line)
    {
      $line = str_replace(' |', ':', $line);
      $line = '<li>' . $line . '</li>';
      $lines[$key] = $line;
    }
    $header = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:headerDatasheet');
    $datasheet = implode(PHP_EOL, $lines);
    $datasheet = null
            . '<h2>' . $header . '</h2>' . PHP_EOL
            . '<ul>' . PHP_EOL
            . $datasheet . PHP_EOL
            . '</ul>' . PHP_EOL;
    return $datasheet;
  }

  /**
   * getRequestContentAddItemFieldsDescriptionDescription( )  :
   *
   * @return	string      $description : description in HTML format
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsDescriptionDescription()
  {
    $arrDescription = $this->getTcaConfFields('description');
    $description = $this->pObj->getDatamapRecord($arrDescription['description']);
    if (empty($description))
    {
      return null;
    }
    $description = $description . PHP_EOL; // rich text field
    return $description;
  }

  /**
   * getRequestContentAddItemFieldsDescriptionEbaypaymentmethodsdescription( )  :
   *
   * @return	string      $paymentMethodsDescription : payment methods description in HTML format
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsDescriptionEbaypaymentmethodsdescription()
  {
    $paymentMethodsDescription = $this->pObj->getDatamapRecord('tx_deal_ebaypaymentmethodsdescription');
    if (empty($paymentMethodsDescription))
    {
      return null;
    }
    $header = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:headerPaymentMethodsDescription');
    $paymentMethodsDescription = null
            . '<h2>' . $header . '</h2>' . PHP_EOL
            . '<p>' . $paymentMethodsDescription . '</p>' . PHP_EOL
    ;
    return $paymentMethodsDescription;
  }

  /**
   * getRequestContentAddItemFieldsDescriptionFilterCategories( )  :
   *
   * @return	string      $categories : categories in HTML format (unordered list)
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsDescriptionFilterCategories()
  {
    $arrFilter = $this->getTcaConfFields('filter');
    $field = $arrFilter['category'];
    if (empty($field))
    {
      return null;
    }

    $labels = $this->sqlGetLabelsFromForeignTables($field);
    if (empty($labels))
    {
      return null;
    }

    $header = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:headerFilterCategories');
    $categories = null
            . '<ul>' . PHP_EOL
            . '<li>' . implode('</li>' . PHP_EOL . '<li>', $labels) . '</li>' . PHP_EOL
            . '</ul>' . PHP_EOL
    ;
    $categories = null
            . '<h2>' . $header . '</h2>' . PHP_EOL
            . $categories
    ;
    return $categories;
  }

  /**
   * getRequestContentAddItemFieldsDescriptionFilterDimensions( )  :
   *
   * @return	string      $dimensions : dimensions in HTML format (unordered list)
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsDescriptionFilterDimensions()
  {
    $arrFilter = $this->getTcaConfFields('filter');
    $field = $arrFilter['dimension'];
    if (empty($field))
    {
      return null;
    }

    $labels = $this->sqlGetLabelsFromForeignTables($field);
    if (empty($labels))
    {
      return null;
    }

    $header = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:headerFilterDimensions');
    $dimensions = null
            . '<ul>' . PHP_EOL
            . '<li>' . implode('</li>' . PHP_EOL . '<li>', $labels) . '</li>' . PHP_EOL
            . '</ul>' . PHP_EOL
    ;
    $dimensions = null
            . '<h2>' . $header . '</h2>' . PHP_EOL
            . $dimensions
    ;
    return $dimensions;
  }

  /**
   * getRequestContentAddItemFieldsDescriptionFilterMaterial( )  :
   *
   * @return	string      $material : material in HTML format (unordered list)
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsDescriptionFilterMaterial()
  {
    $arrFilter = $this->getTcaConfFields('filter');
    $field = $arrFilter['material'];
    if (empty($field))
    {
      return null;
    }

    $labels = $this->sqlGetLabelsFromForeignTables($field);
    if (empty($labels))
    {
      return null;
    }

    $header = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:headerFilterMaterial');
    $material = null
            . '<ul>' . PHP_EOL
            . '<li>' . implode('</li>' . PHP_EOL . '<li>', $labels) . '</li>' . PHP_EOL
            . '</ul>' . PHP_EOL
    ;
    $material = null
            . '<h2>' . $header . '</h2>' . PHP_EOL
            . $material
    ;
    return $material;
  }

  /**
   * getRequestContentAddItemFieldsDescriptionShort( )  : obligate
   *
   * @return	string      $short : short in HTML format
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsDescriptionShort()
  {
    $arrDescription = $this->getTcaConfFields('description');
    $field = $arrDescription['short'];
    if (empty($field))
    {
      return null;
    }
    $short = $this->pObj->getDatamapRecord($field);
    if (empty($short))
    {
      return null;
    }
    $short = '<h2>' . $short . '</h2>' . PHP_EOL;
    return $short;
  }

  /**
   * getRequestContentAddItemFieldsDescriptionTitle( )  : obligate
   *
   * @return	string      $title : title in HTML format
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsDescriptionTitle()
  {
    $title = '<h1>' . $this->getDatamapValueByTcaConfField('title') . '</h1>' . PHP_EOL;
    return $title;
  }

  /**
   * getRequestContentAddItemFieldsErrorLanguage( )  :
   *
   * @return	string  $errorLanguage : error language like de_DE, en_GB, ...
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsErrorLanguage()
  {
    $errorLanguage = $this->pObj->confArr['ebayErrorLanguage'];
    if (empty($errorLanguage))
    {
      $errorLanguage = 'en_GB';
    }
    return $errorLanguage;
  }

  /**
   * getRequestContentAddItemFieldsItemID( )  :
   *
   * @param   string      $forceItemID   :
   * @return	string      $itemID : XML tag with the ebay item id
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsItemID($forceItemID = false)
  {
    if ($forceItemID)
    {
      if (empty($this->ebayItemId))
      {
        $this->ebayItemId = $this->getDatamapRecord('tx_deal_ebayitemid');
      }
    }

    if (empty($this->ebayItemId))
    {
      return null;
    }

    $itemID = '<ItemID>' . $this->ebayItemId . '</ItemID>';
    return $itemID;
  }

  /**
   * getRequestContentAddItemFieldsPictureDetails( )  :
   *
   * @return	string  $pictureDetails : XML tag with the picture details
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsPictureDetails()
  {
    global $TCA;
    $pictureDetails = null;
    // uploadfolder
    $table = $this->pObj->getDatamapTable();
    $tcaColumn = $this->getTcaConfFields('pictures');
    $uploadFolder = $TCA[$table]['columns'][$tcaColumn]['config']['uploadfolder'];

    $urlToPicture = TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $uploadFolder . '/';
//    var_dump(__METHOD__, __LINE__, $tcaColumn, $pictures, $uploadFolder, $GLOBALS['TYPO3_SITE_URL'],
//            TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), $url);
    $csvPictures = trim($this->getDatamapValueByTcaConfField('pictures'), ',');
    $arrPictures = explode(',', $csvPictures);

    $pictureUrl = null;
    foreach ($arrPictures as $picture)
    {
      $pictureUrl = $pictureUrl
              . '    <PictureURL>' . $urlToPicture . $picture . '</PictureURL>' . PHP_EOL;
    }

    if (empty($pictureUrl))
    {
      return;
    }

    $pictureDetails = '  <PictureDetails>
' . $pictureUrl . '
  </PictureDetails>';

    return $pictureDetails;
  }

  /**
   * getRequestContentAddItemFieldsProductListingDetails( )  :
   *
   * @return	string  $productListingDetails : XML tag with the product listing details
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsProductListingDetails()
  {
    $productListingDetails = null;
    $ean = $this->getDatamapValueByTcaConfField('ean');
    if (!empty($ean))
    {
      $ean = '  <EAN>' . $ean . '</EAN>';
    }

    switch (true)
    {
      case($ean):
        // follow the workflow
        break;
      default:
        return null;
    }

    $productListingDetails = '  <ProductListingDetails>
' . $ean . '
  </ProductListingDetails>';

    return $productListingDetails;
  }

  /**
   * getRequestContentAddItemFieldsShippingservicecode( )  : i.e. DE_Paket, DE_HermesPaket, USPSMedia
   *
   * @return	string  ebay shipping service code
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsShippingservicecode()
  {
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    $uid = $this->pObj->getDatamapRecord('tx_deal_ebayshippingservicecode');

    if (empty($uid))
    {
      $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayShippingcostsEmpty');
      $this->log($prompt, 4);
      return false;
    }

    $select_fields = 'code';
    $from_table = 'tx_deal_ebayshippingservicecode';
    $where_clause = 'uid = ' . $uid;
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
      $prompt = 'ERROR: Unproper SQL query';
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

    return $row['code'];
  }

  /**
   * getRequestContentAddItemXmlPaymentmethods( )  :
   *
   * @return	string      $xmlTag : payment methods and paypal email address
   * @link   http://developer.ebay.com/devzone/xml/docs/reference/ebay/types/BuyerPaymentMethodCodeType.html
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemXmlPaymentmethods()
  {
    //var_dump(__METHOD__, __LINE__, $this->pObj->getDatamapRecord('tx_deal_ebaypaymentmethods'));
    $csvPaymentMethods = trim($this->pObj->getDatamapRecord('tx_deal_ebaypaymentmethods'), ',');
    $arrPaymentMethods = explode(',', $csvPaymentMethods);

    $xmlTagPaymentMethod = null;
    $boolTagPaymentMethodPaypal = false;
    foreach ((array) $arrPaymentMethods as $arrPaymentMethod)
    {
      if ($arrPaymentMethod == 'PayPal')
      {
        $boolTagPaymentMethodPaypal = true;
      }
      $xmlTagPaymentMethod = $xmlTagPaymentMethod . PHP_EOL
              . '  <PaymentMethods>' . $arrPaymentMethod . '</PaymentMethods>' . PHP_EOL
      ;
    }
    $xmlTag = $xmlTagPaymentMethod;
    if ($boolTagPaymentMethodPaypal)
    {
      $xmlTag = $xmlTag . PHP_EOL
              . '  <PayPalEmailAddress>' . $this->ebayPaypalEmail . '</PayPalEmailAddress>' . PHP_EOL
      ;
    }
    return $xmlTag;
  }

  /**
   * getRequestContentAddItemXmlReturnpolicy( )  :
   *
   * @return	string      $xmlTag : Return policy
   * @link  http://developer.ebay.com/devzone/xml/docs/reference/ebay/types/ReturnsAcceptedOptionsCodeType.html
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function getRequestContentAddItemXmlReturnpolicy()
  {
    //var_dump(__METHOD__, __LINE__, $this->pObj->confArr, $this->pObj->getDatamapRecord(), $this->getTcaConf());
    $ReturnsAcceptedOption = $this->pObj->getDatamapRecord('tx_deal_ebayreturnsacceptoption');
    $ReturnPolicyDescription = $this->pObj->getDatamapRecord('tx_deal_ebayreturnpolicydescription');

    $xmlTagDescription = null;
    if (!empty($ReturnPolicyDescription))
    {
      $xmlTagDescription = '<Description><![CDATA[' . $ReturnPolicyDescription . ']]></Description>';
    }

    $xmlTag = '' .
            '<ReturnPolicy>
    <ReturnsAcceptedOption>' . $ReturnsAcceptedOption . '</ReturnsAcceptedOption>
    ' . $xmlTagDescription . '
  </ReturnPolicy>
';

    return $xmlTag;
  }

  /*   * *********************************************
   *
   * Request EndFixedPriceItem
   *
   * ******************************************** */

  /**
   * getRequestContentEndItem( )  : Create the XML request to be POSTed
   *
   * @return	string      $xmlrequest :
   * @access protected
   * @version  0.0.3
   * @since    0.0.3
   */
  protected function getRequestContentEndItem()
  {
    //var_dump(__METHOD__, __LINE__, $this->pObj->confArr, $this->pObj->getDatamapRecord(), $this->getTcaConf());
    // fields
    $eBayAuthToken = $this->ebayApiToken;
    $EndingReason = 'NotAvailable'; // http://developer.ebay.com/DevZone/XML/docs/Reference/ebay/types/EndReasonCodeType.html
    $ErrorLanguage = $this->getRequestContentAddItemFieldsErrorLanguage();
    $ItemID = $this->getDatamapRecord('tx_deal_ebayitemid');
    // XML
    $xmlrequestContent = '  <RequesterCredentials>
  <eBayAuthToken>' . $eBayAuthToken . '</eBayAuthToken>
</RequesterCredentials>
<ErrorLanguage>' . $ErrorLanguage . '</ErrorLanguage>
<WarningLevel>High</WarningLevel>
<ItemID>' . $ItemID . '</ItemID>
<EndingReason>' . $EndingReason . '</EndingReason>';
    return $xmlrequestContent;
  }

  /*   * *********************************************
   *
   * Request GetItem
   *
   * ******************************************** */

  /**
   * getRequestContentGetItem( )  : Create the XML request to be POSTed
   *
   * @return	string      $xmlrequest :
   * @access protected
   * @version  0.0.3
   * @since    0.0.3
   */
  protected function getRequestContentGetItem()
  {
    //var_dump(__METHOD__, __LINE__, $this->pObj->confArr, $this->pObj->getDatamapRecord(), $this->getTcaConf());
    // fields
    $eBayAuthToken = $this->ebayApiToken;
    $ErrorLanguage = $this->getRequestContentAddItemFieldsErrorLanguage();
    $ItemID = $this->getDatamapRecord('tx_deal_ebayitemid');
    // XML
    $xmlrequestContent = '  <RequesterCredentials>
  <eBayAuthToken>' . $eBayAuthToken . '</eBayAuthToken>
</RequesterCredentials>
<ErrorLanguage>' . $ErrorLanguage . '</ErrorLanguage>
<WarningLevel>High</WarningLevel>
<ItemID>' . $ItemID . '</ItemID>';
    return $xmlrequestContent;
  }

  /*   * *********************************************
   *
   * Request RelistFixedPriceItem
   *
   * ******************************************** */

  /**
   * getRequestContentrelistItem( )  : Create the XML request to be POSTed
   *
   * @return	string      $xmlrequest :
   * @access protected
   * @version  0.0.3
   * @since    0.0.3
   */
  protected function getRequestContentRelistItem()
  {
    $forceItemID = true;
    $xmlrequest = $this->getRequestContentAddItem($forceItemID);
    return $xmlrequest;
  }

  /*   * *********************************************
   *
   * Request Header
   *
   * ******************************************** */

  /**
   * getRequestHeader( )  :
   *
   * @param string      $callName :
   * @param integer     $length   :
   * @return	array     $headers  : headers for a xmlrequest.
   * @access protected
   * @version  0.0.3
   * @since    0.0.3
   */
  protected function getRequestHeader($callName, $length)
  {
    $headers = array(
      'X-EBAY-API-CALL-NAME: ' . $callName,
      'X-EBAY-API-COMPATIBILITY-LEVEL: ' . $this->ebayApiVersion,
      'X-EBAY-API-SITEID: ' . $this->ebayMarketplaceSiteId,
      'Content-Type: text/xml;charset=utf-8',
      'Content-Length: ' . $length,
    );

    $prompt = 'headers: ' . PHP_EOL . var_export($headers, true);
    $this->log($prompt, -1);

    return $headers;
  }

  /*   * *********************************************
   *
   * Get methods
   *
   * ******************************************** */

  /**
   * getEbayMode( )
   *
   * @return	string  $ebayMode  : global $ebayMode
   * @access protected
   * @version   0.0.3
   * @since     0.0.3
   */
  protected function getEbayMode()
  {
    return $this->pObj->getEbayMode();
  }

  /**
   * getItemId( ) :
   *
   * @return	integer    $ebayItemId : global $ebayItemId
   * @access protected
   * @version   0.0.3
   * @since     0.0.3
   */
  protected function getItemId()
  {
    return $this->ebayItemId;
  }

  /**
   * getTcaConf( ) : get the configuration from the TCA of the current table
   *
   * @return	array    $ebayApiConfFromTCA :
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function getTcaConf()
  {
    return $this->pObj->getTcaConf();
  }

  /**
   * getTcaConfFields( ) :
   *
   * @param string $key : optional
   * @return	mixed     :
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function getTcaConfFields($key = null)
  {
    $tcaConf = $this->getTcaConf();
    $fields = $tcaConf['fields'];

    if (empty($key))
    {
      return $fields;
    }
    return $fields[$key];
  }

  /*   * *********************************************
   *
   * Init
   *
   * ******************************************** */

  /**
   * init( ) :
   *
   * @param	objct     $pObj : Parent object
   * @return	void
   * @access protected
   * @version  0.0.3
   * @since    0.0.3
   */
  protected function init($pObj)
  {
    if($this->init !== null )
    {
      return;
    }
    $this->init = true;

    $this->initVarsPobj($pObj);
    $this->initConf();
    $this->initVarsEbay();

    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);
  }

  /**
   * initConf( ) : Configuration from the TCA ctrl section of the current table.
   *
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function initConf()
  {
    $this->ebayApiConfFromTCA = $this->getTcaConf();
  }

  /**
   * initVars( ) :
   *
   * @param	objct     $pObj : Parent object
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function initVars($pObj)
  {
    $this->initVarsPobj($pObj);
    $this->initVarsEbay();
  }

  /**
   * initVarsEbay( ) :
   *
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function initVarsEbay()
  {
    $this->initVarsEbayEnvironment();
    $this->initVarsEbayMarketplace();
    $this->initVarsEbayPaypalEmail();
    $this->initVarsEbayApi();
  }

  /**
   * initVarsEbayApi( ) :
   *
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function initVarsEbayApi()
  {
    $this->initVarsEbayApiEndpoint();
    $this->initVarsEbayApiToken();
  }

  /**
   * initVarsEbayApiEndpoint( ) :
   *
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function initVarsEbayApiEndpoint()
  {
    switch (true)
    {
      case( $this->ebayEnvironment == 'production' ):
        $this->ebayApiEndpointXml = $this->ebayApiEndpointXmlProduction;
        break;
      case( $this->ebayEnvironment == 'sandbox' ):
        $this->ebayApiEndpointXml = $this->ebayApiEndpointXmlSandbox;
        break;
      case( empty($this->ebayEnvironment) ):
// RETURN : ebay isn't enabled
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayEnvironmentFalse');
        $this->log($prompt, 0, 2);
        break;
      default:
// RETURN : ebay isn't enabled
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayEnvironmentError');
        $prompt = str_replace('%ebayEnvironment%', $this->ebayEnvironment, $prompt);
        $this->log($prompt, 4, 2);
        break;
    }
  }

  /**
   * initVarsEbayApiToken( ) :
   *
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function initVarsEbayApiToken()
  {
    switch (true)
    {
      case( $this->ebayEnvironment == 'production' ):
        $this->ebayApiToken = $this->ebayApiConfFromTCA['environment']['production']['token'];
        break;
      case( $this->ebayEnvironment == 'sandbox' ):
        $this->ebayApiToken = $this->ebayApiConfFromTCA['environment']['sandbox']['token'];
        break;
      case( empty($this->ebayEnvironment) ):
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayEnvironmentFalse');
        $this->log($prompt, 4, 2);
        break;
      default:
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayEnvironmentError');
        $prompt = str_replace('%ebayEnvironment%', $this->ebayEnvironment, $prompt);
        $this->log($prompt, 4, 2);
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
    $this->ebayEnvironment = $this->ebayApiConfFromTCA['environment']['key'];
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
    $this->ebayMarketplace = $this->ebayApiConfFromTCA['marketplace'];
    if (empty($this->ebayMarketplace))
    {
      $this->ebayMarketplace = 'United States - EBAY-US/USD (0)';
    }

    list( $country, $ebayCode ) = explode(' - ', $this->ebayMarketplace);
    list( $globalId, $currencyAndSiteid) = explode('/', $ebayCode);
    list( $currency, $siteId) = explode(' ', $currencyAndSiteid);

    $this->ebayMarketplaceCountry = $country;
    $this->ebayMarketplaceCurrency = $currency;
    $this->ebayMarketplaceGlobalId = $globalId;
    $this->ebayMarketplaceSiteId = trim($siteId, '()');
  }

  /**
   * initVarsEbayPaypalEmail( ) :
   *
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function initVarsEbayPaypalEmail()
  {
    switch (true)
    {
      case( $this->ebayEnvironment == 'production' ):
        $this->ebayPaypalEmail = $this->ebayApiConfFromTCA['paypal']['production']['email'];
        break;
      case( $this->ebayEnvironment == 'sandbox' ):
        $this->ebayPaypalEmail = $this->ebayApiConfFromTCA['paypal']['sandbox']['email'];
        break;
      case( empty($this->ebayEnvironment) ):
// RETURN : ebay isn't enabled
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayEnvironmentFalse');
        $this->log($prompt, 0, 2);
        break;
      default:
// RETURN : ebay isn't enabled
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayEnvironmentError');
        $prompt = str_replace('%ebayEnvironment%', $this->ebayEnvironment, $prompt);
        $this->log($prompt, 4, 2);
        break;
    }

    if ($this->ebayPaypalEmail)
    {
      return;
    }

    $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayPaypalEmailEmpty');
    $this->log($prompt, 3);
  }

  /**
   * initVarsPobj( ) :
   *
   * @param	objct     $pObj : Parent object
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function initVarsPobj($pObj)
  {
    $this->pObj = $pObj;
  }

  /*   * *********************************************
   *
   * log
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
   * @access protected
   * @version   0.0.3
   * @since     0.0.3
   */
  protected function log($prompt, $status = -1, $action = 2, $header = 2)
  {
    $this->pObj->log($prompt, $status, $action, $header);
  }

  /*   * *********************************************
   *
   * Requirements
   *
   * ******************************************** */

  /**
   * requirements( ) :
   *
   * @return	boolean
   * @access protected
   * @version  0.0.3
   * @since    0.0.3
   */
  protected function requirements()
  {
    return $this->requirementsVarsEbay();
  }

  /**
   * requirementsVarsEbay( ) :
   *
   * @return	boolean
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function requirementsVarsEbay()
  {
    switch (true)
    {
      case( empty($this->ebayApiEndpointXml)):
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayApiEndpointEmpty');
        $this->log($prompt, 4, 2);
        return false;
      case( empty($this->ebayApiToken)):
        $prompt = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayApiTokenEmpty');
        $this->log($prompt, 4, 2);
        return false;
    }

//    $prompt = 'ebay API endpoint: ' . $this->ebayApiEndpointXml;
//    $this->log($prompt, -1);
//    $prompt = 'ebay API token: ' . $this->ebayApiToken;
//    $this->log($prompt, -1);
//    $prompt = var_export($this->ebayApiConfFromTCA, true);
//    $this->log($prompt, -1);

    return true;
  }

  /*   * *********************************************
   *
   * Set methods
   *
   * ******************************************** */

  /**
   * setDatamapRecordFieldPrepend( )
   *
   * @param   string  $key        :
   * @param   string  $value      :
   * @param   string  $boolWiEol  : prepends an end of line (optional)
   * @return	array $datamapRecord : global $datamapRecord
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function setDatamapRecordFieldPrepend($key, $value, $boolWiEol = true)
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
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function setDatamapRecordFieldUpdate($key, $value, $boolPrompt = true)
  {
    $this->pObj->setDatamapRecordFieldUpdate($key, $value, $boolPrompt);
  }

  /**
   * setEbayItemStatus( )
   *
   * @return	void
   * @access public
   * @version   0.0.3
   * @since     0.0.3
   */
  public function setEbayItemStatus()
  {
    $ebayItemID = $this->getDatamapRecord('tx_deal_ebayitemid');
    switch (true)
    {
      case(!empty($ebayItemID)):
        $this->setEbayItemStatusWiItemID();
        break;
      case(empty($ebayItemID)):
      default:
        $this->setEbayItemStatusWoItemID();
        break;
    }
  }

  /**
   * setEbayItemStatusWiItemID( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function setEbayItemStatusWiItemID()
  {
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    $action = 'setEbayItemStatus';
    $this->getItem($action);
  }

  /**
   * setEbayItemStatusWoItemID( )
   *
   * @return	void
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function setEbayItemStatusWoItemID()
  {
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    $key = 'tx_deal_ebayitemstatus';
    $value = $GLOBALS['LANG']->sL('LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayItemIsNotOnEbay');
    $boolPrompt = false;
    $this->setDatamapRecordFieldUpdate($key, $value, $boolPrompt);
  }

  /*   * *********************************************
   *
   * SQL
   *
   * ******************************************** */

  /**
   * sqlGetLabelsFromForeignTables( )  :
   *
   * @param   string  $tcaField  : TCA field of the local table which links to the foreign table
   * @return	array   $lables
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function sqlGetLabelsFromForeignTables($tcaField)
  {
    global $TCA;

    $arrFilter = $this->getTcaConfFields('filter');
    $field = $arrFilter[$tcaField];
    if (empty($field))
    {
      return null;
    }
    $uids = $this->pObj->getDatamapRecord($field);
    $table = $this->pObj->getDatamapTable();

    if (empty($uids))
    {
      return null;
    }

    $uids = trim($uids, ',');
    $foreign_table = $TCA[$table]['columns'][$tcaField]['config']['foreign_table'];
    $label = $TCA[$foreign_table]['ctrl']['label'];

    $select_fields = $label;
    $from_table = $foreign_table;
    $where_clause = 'uid IN (' . $uids . ')';
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
      $prompt = 'ERROR: Unproper SQL query';
      $this->log($prompt, 4, 2, 1);
      $prompt = 'query: ' . $query;
      $this->log($prompt, 0, 2, 1);
      $prompt = 'prompt: ' . $error;
      $this->log($prompt, 4, 2, 1);

      return;
    }
    // RETURN : ERROR
    // Fetch first row only
    $lines = array();
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
    {
      //var_dump(__METHOD__, __LINE__, $row);
      //$lines[] = '<li>' . $row[$label] . '</li>';
      $lines[] = $row[$label];
    }
    // Free the SQL result
    $GLOBALS['TYPO3_DB']->sql_free_result($res);

    return $lines;
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/lib/marketplaces/ebay/api/class.tx_deal_ebayApiBase.php'])
{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/lib/marketplaces/ebay/api/class.tx_deal_ebayApiBase.php']);
}
