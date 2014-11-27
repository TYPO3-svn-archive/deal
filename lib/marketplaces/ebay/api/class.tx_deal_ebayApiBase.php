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
 * plugin 'ebay 3 items' for the 'deal' extension.
 *
 * @author	Dirk Wildt <http://wildt.at.die-netzmacher.de>
 * @package	TYPO3
 * @subpackage	tx_deal
 * @internal    #i0003
 * @version     1.0.0
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
   * @param   string    $xmlAction    : addFixedPriceItem, getItem
   * @param   boolean   $dontPrompt : do not prompt to the backend form (optional)
   * @return	mixed     $status : false, 'isNotOnEbay', 'isOnEbayEnabled', 'isOnEbayDisabled', 'isWoStatus'
   * @access protected
   * @version  0.0.3
   * @since    0.0.3
   */
  protected function evalResponse( $xmlRequest, $xmlResponse, $xmlAction = null, $dontPrompt = false )
  {
    $status = 'undefined (by ' . __METHOD__ . ' #' . __LINE__ . ')';
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log( $prompt, -1 );

    if ( $this->evalResponsePromptError( $xmlRequest, $xmlResponse, $xmlAction ) )
    {
      return false;
    }

    $status = $this->evalResponsePromptSuccess( $xmlRequest, $xmlResponse, $xmlAction, $dontPrompt );
    return $status;
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
  private function evalResponsePromptError( $xmlRequest, $xmlResponse, $xmlAction )
  {
    switch ( true )
    {
      case ( $this->evalResponsePromptErrorBody( $xmlRequest, $xmlResponse, $xmlAction )):
      case ( $this->evalResponsePromptErrorErrors( $xmlRequest, $xmlResponse, $xmlAction )):
        return true;
    }

    return false;
  }

  /**
   * evalResponsePromptErrorBody( )  :
   *
   * @param   array     $xmlRequest  :
   * @param   object    $xmlResponse  :
   * @param   string    $xmlAction    : Request method
   * @return	boolean
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptErrorBody( $xmlRequest, $xmlResponse, $xmlAction )
  {
    if ( !$xmlResponse->Body )
    {
      $prompt = 'xmlResponse->Body is empty or isn\'t set.';
      $this->log( $prompt, -1 );
      return false;
    }

    $prompt = 'Environment: ' . $this->ebayEnvironment;
    $this->log( $prompt, -1 );

    $prompt = 'Request: ' . $xmlAction;
    $this->log( $prompt, -1 );

    $prompt = 'xmlRequest: ' . PHP_EOL . $xmlRequest;
    $this->log( $prompt, 4 ); // Workaround: 4, because XML code won't prompt to -1 (log only)

    $prompt = 'xmlResponse: ' . $xmlResponse->Body;
    $this->log( $prompt, 4 );

    return true;
  }

  /**
   * evalResponsePromptErrorErrors( )  :
   *
   * @param   array     $xmlRequest   :
   * @param   object    $xmlResponse  :
   * @param   string    $xmlAction    : Request method
   * @return	boolean                 : false in case of no errors or update (duplicate entry), true in case of failure
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptErrorErrors( $xmlRequest, $xmlResponse, $xmlAction )
  {
//    if( !is_object($xmlResponse->errors))
//    {
//      $prompt = 'xmlResponse->errors isn\'t an object.';
    if ( !$xmlResponse->Errors )
    {
      $prompt = 'xmlResponse->Errors is empty or isn\'t set.';
      $this->log( $prompt, -1 );
      return false;
    }

    $ShortMessage = $xmlResponse->Errors->ShortMessage;
    $LongMessage = $xmlResponse->Errors->LongMessage;
    $this->ebayErrorCode = ( int ) $xmlResponse->Errors->ErrorCode;
    $SeverityCode = $xmlResponse->Errors->SeverityCode;
    $ErrorClassification = $xmlResponse->Errors->ErrorClassification;
    $Version = $xmlResponse->Version;

    switch ( $this->ebayErrorCode )
    {
      case(37): // Unproper ShippingServiceCost
        $this->evalResponsePromptErrorErrors00000037();
        return true;
      case(73): // Unproper starting price
        $this->evalResponsePromptErrorErrors00000073();
        return true;
      case(196): // Item cannot be relisted. This item was not relisted because the listing does not exist or is still active.
        $this->evalResponsePromptErrorErrors00000196();
        return true;
      case(290): // You're not the owner of the item.
        $this->evalResponsePromptErrorErrors00000290();
        return true;
      case(291): // Auction ended. You're not allowed to revise ended listings.
        $this->evalResponsePromptErrorErrors00000291();
        return true;
      case(1047): // The auction has already been closed.
        $followTheWorkflow = $this->evalResponsePromptErrorErrors00001047();
        if ( !$followTheWorkflow )
        {
          return true;
        }
        break;
      case(21919067): // Listing breaches the Duplicate listings policy.
        $this->evalResponsePromptErrorErrors21919067( $xmlRequest, $xmlResponse, $xmlAction );
        return false;
      default:
        // follow the workflow
        break;
    }

    $prompt = 'Environment: ' . $this->ebayEnvironment;
    $this->log( $prompt, -1 );

    $prompt = 'Request: ' . $xmlAction;
    $this->log( $prompt, -1 );

    $prompt = 'ebay API endpoint: ' . $this->ebayApiEndpointXml;
    $this->log( $prompt, -1 );
    $prompt = 'ebay API token: ' . $this->ebayApiToken;
    $this->log( $prompt, -1 );
    $prompt = 'TCA configuration: ' . var_export( $this->ebayApiConfFromTCA, true );
    $this->log( $prompt, -1 );
    $prompt = 'xmlRequest: ' . PHP_EOL . $xmlRequest;
    $this->log( $prompt, 4 ); // Workaround: 4, because XML code won't prompt to -1 (log only)
    $prompt = 'xmlResponse: ' . PHP_EOL . var_export( $xmlResponse, true );
    $this->log( $prompt, -1 );

    $promptsByEbay = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:promptsByEbay' );
    $promptsByTYPO3 = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:promptsByTYPO3' );
    $promptDetailsToSyslog = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:promptDetailsToSyslog' );

    $prompt = $promptsByEbay . ': ' . PHP_EOL
            . '* ' . $ShortMessage . PHP_EOL
            . '* ' . $LongMessage . PHP_EOL
            . '* ' . 'Error code: ' . $this->ebayErrorCode . '. Severity code: ' . $SeverityCode . '. Error classification: ' . $ErrorClassification . '. '
            . 'API version: ' . $Version . PHP_EOL
            . $promptsByTYPO3 . ': ' . PHP_EOL
            . '* Environment: ' . $this->ebayEnvironment . PHP_EOL
            . '* ' . $promptDetailsToSyslog
    ;
    $this->log( $prompt, 4 );

    $xml = str_replace( '><', '>' . PHP_EOL . '<', $xmlRequest );
    $this->log( $xml, -1 );

    $xml = $xmlResponse->asXML();
    $xml = str_replace( '><', '>' . PHP_EOL . '<', $xml );
    $this->log( $xml, -1 );

    return true;
  }

  /**
   * evalResponsePromptErrorErrors00000037(): Unproper shipping costs
   *
   * @return	void
   * @access private
   * @version  1.0.1
   * @since    1.0.1
   */
  private function evalResponsePromptErrorErrors00000037()
  {
    $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayErrorShippingServiceCost' );
    $this->log( $prompt, 4 );
    $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayHelpShippingServiceCost' );
    $this->log( $prompt, 1 );
  }

  /**
   * evalResponsePromptErrorErrors00000073(): Unproper starting price
   *
   * @return	void
   * @access private
   * @version  1.0.1
   * @since    1.0.1
   */
  private function evalResponsePromptErrorErrors00000073()
  {
    $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayErrorStartingPrice' );
    $this->log( $prompt, 4 );
    $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayHelpStartingPrice' );
    $this->log( $prompt, 1 );
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
    $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayErrorRelistNotPossible' );
    $this->log( $prompt, 4 );
    $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayHelpRelistNotPossible' );
    $this->log( $prompt, 1 );
  }

  /**
   * evalResponsePromptErrorErrors00000290(): You aren't the owner of the item
   *
   * @return	void
   * @access private
   * @internal #i0018
   * @version  0.1.2
   * @since    0.1.2
   */
  private function evalResponsePromptErrorErrors00000290()
  {
    $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayErrorNotTheOwner' );
    $this->log( $prompt, 4 );
    $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayHelpNotTheOwner' );
    $this->log( $prompt, 1 );
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
    $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayErrorReviseEndedListings' );
    $this->log( $prompt, 4 );
    $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayHelpReviseEndedListings' );
    $this->log( $prompt, 1 );
  }

  /**
   * evalResponsePromptErrorErrors00001047( )  : The auction has already been closed.
   *
   * @return	boolean   $followTheWorkflow  : true, if $action isn't end, false if it is.
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptErrorErrors00001047()
  {
    $followTheWorkflow = true;

//    if ($action != 'disable')
//    {
//      return $followTheWorkflow;
//    }
//
    $followTheWorkflow = false;

    $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayErrorAuctionAlreadyClosed' );
    $this->log( $prompt, 3 );
    $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayHelpAuctionAlreadyClosed' );
    $this->log( $prompt, 1 );
    return $followTheWorkflow;
  }

  /**
   * evalResponsePromptErrorErrors21919067( )  :  Listing breaches the Duplicate listings policy.
   *
   * @param   array     $xmlRequest   :
   * @param   object    $xmlResponse  :
   * @param   string    $xmlAction    : Request method
   * @return	boolean                 : false in case of no errors or update (duplicate entry), true in case of failure
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptErrorErrors21919067( $xmlRequest, $xmlResponse, $xmlAction )
  {
//    switch (true)
//    {
//      case($xmlAction == 'RelistFixedPriceItem'):
//      case($xmlAction == 'ReviseFixedPriceItem'):
//        return;
//      default:
//        // Follow the workflow
//        break;
//    }
    foreach ( $xmlResponse->Errors->ErrorParameters as $ErrorParameter )
    {
      switch ( $ErrorParameter[ 'ParamID' ] )
      {
        case '0': // 0: title of the item
          // follow the workflow
          break;
        case '1': // 1: ebay item id
          $this->ebayItemId = ( string ) $ErrorParameter->Value;
          $boolPrompt = true;
          $this->setDatamapRecordFieldUpdate( 'tx_deal_ebayitemid', $this->ebayItemId, $boolPrompt );
          break;
      }
    }

    $prompt = 'xmlAction: ' . $xmlAction;
    $this->log( $prompt, 3 );
    $prompt = 'xmlResponse short message: ' . $xmlResponse->Errors->ShortMessage;
    $this->log( $prompt, 3 );
    $prompt = 'xmlResponse ebay item id: ' . $this->ebayItemId;
    $this->log( $prompt, 3 );
//    $xml = str_replace('><', '>' . PHP_EOL . '<', $xmlRequest);
//    $this->log($xml, 3);
    unset( $xmlRequest );
  }

  /**
   * evalResponsePromptSuccess( )  :
   *
   * @param   array     $xmlRequest   :
   * @param   object    $xmlResponse  :
   * @param   object    $xmlAction    :
   * @param   boolean   $dontPrompt   : do not prompt to the backend form (optional)
   * @return	string    $status : isNotOnEbay, isOnEbayEnabled, isOnEbayDisabled, isWoStatus
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptSuccess( $xmlRequest, $xmlResponse, $xmlAction, $dontPrompt = false )
  {
    $status = 'undefined (by ' . __METHOD__ . ' #' . __LINE__ . ')';
    $prompt = 'Environment: ' . $this->ebayEnvironment;
    $this->log( $prompt, -1 );

    $prompt = 'Request: ' . $xmlAction;
    $this->log( $prompt, -1 );

    $xml = 'xmlRequest (success): ' . str_replace( '><', '>' . PHP_EOL . '<', $xmlRequest );
    $this->log( $xml, -1 );

    $xml = 'xmlResponse (success): ' . $xmlResponse->asXML();
    $xml = str_replace( '><', '>' . PHP_EOL . '<', $xml );
    $this->log( $xml, -1 );

    //var_dump(__METHOD__, __LINE__, $xmlAction);
    switch ( true )
    {
//      case( $action == 'setEbayItemStatus'):
//        $status = $this->evalResponseSetFieldEbayItemStatus($xmlResponse);
//        break;
      case( $xmlAction == 'AddFixedPriceItem'):
      case( $xmlAction == 'RelistFixedPriceItem'):
      case( $xmlAction == 'ReviseFixedPriceItem'):
        $status = $this->evalResponsePromptSuccessAddFixedPriceItem( $xmlResponse );
        break;
      case( $xmlAction == 'EndFixedPriceItem'):
        $status = $this->evalResponsePromptSuccessEndFixedPriceItem( $xmlResponse );
        break;
      case( $xmlAction == 'GetItem'):
        $status = $this->evalResponsePromptSuccessGetItem( $xmlResponse, $dontPrompt );
        break;
      case( $xmlAction == 'VerifyAddFixedPriceItem'):
        // follow the workflow
        break;
      default:
        $prompt = __METHOD__ . ' (#' . __LINE__ . '): xmlAction is undefined "' . $xmlAction . '"';
        die( $prompt );
    }

    return $status;
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
  private function evalResponsePromptSuccessAddFixedPriceItem( $xmlResponse )
  {
    $ItemID = ( string ) $xmlResponse->ItemID;
    //var_dump(__METHOD__, __LINE__, $xmlResponse);
    if ( !empty( $ItemID ) )
    {
      $this->ebayItemId = $ItemID;
      //var_dump(__METHOD__, __LINE__, $this->ebayItemId);
      $boolPrompt = false;
      $this->setDatamapRecordFieldUpdate( 'tx_deal_ebayitemid', $this->ebayItemId, $boolPrompt );
      $status = 'isOnEbayEnabled';
      return $status;
    }

    $prompt = __METHOD__ . ' (#' . __LINE__ . '): Fatal error: ItemID is empty!';
    die( $prompt );
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
  private function evalResponsePromptSuccessEndFixedPriceItem( $xmlResponse )
  {
    $status = 'isOnEbayDisabled';
    return $status;
  }

  /**
   * evalResponsePromptSuccessGetItem( )  :
   *
   * @param   object    $xmlResponse  :
   * @param   boolean   $dontPrompt   : do not prompt to the backend form (optional)
   * @return	string    $status : isNotOnEbay, isOnEbayEnabled, isOnEbayDisabled, isNotOnEbay
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponsePromptSuccessGetItem( $xmlResponse, $dontPrompt = false )
  {
    $status = null;

    $status = $this->evalResponseGetEbayStatus( $xmlResponse );
    $this->evalResponseSetFieldEbayItemStatus( $xmlResponse, $status );
    $this->evalResponseSetFieldEbayResponse( $xmlResponse, $status, $dontPrompt );

    return $status;
  }

  /**
   * evalResponseSetFieldEbayItemStatus( )  :
   *
   * @param   object    $xmlResponse  :
   * @param   string    $status : isWoStatus, isOnEbayEnabled, isOnEbayDisabled
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponseSetFieldEbayItemStatus( $xmlResponse, $status )
  {
    $response = array(
      'EndTime' => ( string ) $xmlResponse->Item->ListingDetails->EndTime,
      'EndingReason' => ( string ) $xmlResponse->Item->ListingDetails->EndingReason,
      'Quantity' => ( integer ) $xmlResponse->Item->Quantity,
      'QuantitySold' => ( integer ) $xmlResponse->Item->SellingStatus->QuantitySold,
      'ViewItemURL' => ( string ) $xmlResponse->Item->ListingDetails->ViewItemURL
    );

    switch ( $status )
    {
      case('isOnEbayDisabled'):  // endtime is in the past
        $value = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayItemIsOnEbayDisabled' );
        break;
      case('isOnEbayEnabled'):  // endtime is in the future
        $value = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayItemIsOnEbayEnabled' );
        break;
      default:
        $value = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayItemError' );
        $prompt = 'Ebay status is "' . $status . '" at ' . __METHOD__ . ' (#' . __LINE__ . ')';
        $this->log( $prompt, 3 );
        break;
    }

    $ebayEnvironment = $this->ebayEnvironment;
    if ( $ebayEnvironment == 'sandbox' )
    {
      $ebayEnvironment = ' (' . $ebayEnvironment . ')';
    }
    else
    {
      $ebayEnvironment = null;
    }
    $value = str_replace( '%ebayEnvironment%', $ebayEnvironment, $value );
    $value = str_replace( '%Quantity%', $response[ 'Quantity' ], $value );
    $value = str_replace( '%QuantitySold%', $response[ 'QuantitySold' ], $value );
    $value = str_replace( '%ViewItemURL%', $response[ 'ViewItemURL' ], $value );

    $key = 'tx_deal_ebayitemstatus';
    $boolPrompt = false;
    $this->setDatamapRecordFieldUpdate( $key, $value, $boolPrompt );
  }

  /**
   * evalResponseGetEbayStatus( )  :
   *
   * @param   object    $xmlResponse  :
   * @return	string    $status : isNotOnEbay, isOnEbayEnabled, isOnEbayDisabled, isWoStatus
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponseGetEbayStatus( $xmlResponse )
  {
    $status = 'undefined (by ' . __METHOD__ . ' #' . __LINE__ . ')';
    $response = array(
      'EndTime' => ( string ) $xmlResponse->Item->ListingDetails->EndTime,
      'EndingReason' => ( string ) $xmlResponse->Item->ListingDetails->EndingReason,
      'Quantity' => ( integer ) $xmlResponse->Item->Quantity,
      'QuantitySold' => ( integer ) $xmlResponse->Item->SellingStatus->QuantitySold,
      'ViewItemURL' => ( string ) $xmlResponse->Item->ListingDetails->ViewItemURL
    );

    $oneMinute = 60;
    switch ( true )
    {
      case(!empty( $response[ 'EndingReason' ] )):  // endtime is in the past
        $prompt = 'isOnEbayDisabled because: EndingReason = ' . $response[ 'EndingReason' ];
        $this->log( $prompt, -1 );
        $status = 'isOnEbayDisabled';
        break;
      case(strtotime( $response[ 'EndTime' ] ) <= ( time() + $oneMinute )):  // endtime is in the past
        $prompt = 'isOnEbayDisabled because: EndTime is smaller than now';
        $this->log( $prompt, -1 );
        $status = 'isOnEbayDisabled';
        break;
      case(strtotime( $response[ 'EndTime' ] ) > ( time() + $oneMinute )):  // endtime is in the future
        $prompt = 'isOnEbayEnabled because: EndTime is greater than now';
        $this->log( $prompt, -1 );
        $status = 'isOnEbayEnabled';
        break;
      default:
        $status = 'undefined (by ' . __METHOD__ . ' #' . __LINE__ . ')';
        $prompt = 'Ebay status is ' . $status;
        $this->log( $prompt, 3 );
        break;
    }

    return $status;
  }

  /**
   * evalResponseSetFieldEbayResponse( )  :
   *
   * @param   object    $xmlResponse  :
   * @param   string    $status       : isNotOnEbay, isOnEbayEnabled, isOnEbayDisabled, isWoStatus
   * @param   boolean   $dontPrompt   : do not prompt to the backend form (optional)
   * @return	void
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function evalResponseSetFieldEbayResponse( $xmlResponse, $status, $dontPrompt = false )
  {
    $key = 'tx_deal_ebaylog';
    $this->log( $status, -1 );
    switch ( $status )
    {
      case( 'isNotOnEbay'):
        $value = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayStatusIsNotOnEbay' );
        if ( !$dontPrompt )
        {
          $this->log( $value, 1 );
        }
        break;
      case( 'isOnEbayDisabled'):
        $URL = ( string ) $xmlResponse->Item->ListingDetails->ViewItemURL;
        $value = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayStatusIsOnEbayDisabled' );
        $value = str_replace( '%URL%', $URL, $value );
        if ( !$dontPrompt )
        {
          $this->log( $value, 1 );
        }
        break;
      case( 'isOnEbayEnabled'):
        $URL = ( string ) $xmlResponse->Item->ListingDetails->ViewItemURL;
        $value = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayStatusIsOnEbayEnabled' );
        $value = str_replace( '%URL%', $URL, $value );
        if ( !$dontPrompt )
        {
          $this->log( $value, 1 );
        }
        break;
      case( 'isWoStatus'):
        $value = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayStatusIsWoStatus' );
        if ( !$dontPrompt )
        {
          $this->log( $value, 1 );
        }
        break;
      default:
        $value = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayStatusError' );
        $value = str_replace( '%status%', $status, $value );
        $this->log( $value, 3 );
        break;
    }

    $value = date( 'y-m-d H:i: ' ) . $value;
    $this->setDatamapRecordFieldPrepend( $key, $value );
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
  protected function feesPrompt( $xmlResponse )
  {
    $prompt = null;
    $sum = 0.00;
    $currency = $this->ebayMarketplaceCurrency;

    foreach ( ( array ) $xmlResponse->Fees as $Fees )
    {
      foreach ( ( array ) $Fees as $Fee )
      {
        $key = $Fee->Name;
        $value = ( double ) $Fee->Fee;
        $currency = $Fee->Fee[ 'currencyID' ];
        if ( $value > 0 )
        {
          $prompt = $prompt
                  . '+ ' . sprintf( '%01.2f', $value ) . ' ' . $currency . ' : ' . $key . PHP_EOL
          ;
          $sum = $sum + $value;
        }
      }
    }

    $strTitle = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayFeesTableTitle' );
    $strWoFees = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayFeesTableNoFees' );
    $strWoWarranty = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayFeesTableNoWarranty' );
    $strSum = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayFeesTableSum' );
    if ( empty( $prompt ) )
    {
      $prompt = '+ 0.00 ' . $currency . ' : ' . $strWoFees . PHP_EOL;
    }
    $prompt = $strTitle . '*' . PHP_EOL
            . '--------------------------------------------------------------------------------------------- ' . PHP_EOL
            . $prompt
            . '--------------------------------------------------------------------------------------------- ' . PHP_EOL
            . '# ' . sprintf( '%01.2f', $sum ) . ' ' . $currency . ' : ' . $strSum . PHP_EOL
            . '--------------------------------------------------------------------------------------------- ' . PHP_EOL
            . PHP_EOL
            . '*' . $strWoWarranty
    ;
    $this->log( $prompt, 0 );

    if ( $this->getEbayMode() != 'test' )
    {
      $strWoWarrantyShort = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayFeesTableNoWarrantyShort' );
      $key = 'tx_deal_ebaylog';
      $value = date( 'y-m-d H:i: ' ) . $strTitle . ': ' . sprintf( '%01.2f', $sum ) . ' ' . $currency . ' (' . $strWoWarrantyShort . ')';
      $this->setDatamapRecordFieldPrepend( $key, $value );
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

    $action = 'enableupdate';

    switch ( true )
    {
      case(!empty( $this->ebayItemId )): //
        $action = 'enableupdate';
        break;
      case($this->ebayErrorCode == 0): // No error occurs
        $action = 'enableupdate';
        break;
      case($this->ebayErrorCode == 21919067): // Listing breaches the Duplicate listings policy.
        $action = 'enableupdate';
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
  protected function getDatamapRecord( $key = null )
  {
    $value = $this->pObj->getDatamapRecord( $key );

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
  private function getDatamapValueByTcaConfField( $field )
  {
    $key = $this->getTcaConfFields( $field );
    $value = $this->pObj->getDatamapRecord( $key );

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
  protected function getRequestContentAddItem( $forceItemID = false )
  {
    //var_dump(__METHOD__, __LINE__, $this->pObj->confArr, $this->pObj->getDatamapRecord(), $this->getTcaConf());
    // fields
    $CategoryID = trim( $this->pObj->getDatamapRecord( 'tx_deal_ebaycategoryid' ), ',' );
    $ConditionID = $this->pObj->getDatamapRecord( 'tx_deal_ebayconditionid' ); // http://developer.ebay.com/DevZone/finding/CallRef/Enums/conditionIdList.html
    $Country = $this->getRequestContentAddItemFieldsCountry();
    $Currency = $this->ebayMarketplaceCurrency;
    $currencyID = $this->ebayMarketplaceCurrency;
    $Description = $this->getRequestContentAddItemFieldsDescription();
    $DispatchTimeMax = $this->pObj->getDatamapRecord( 'tx_deal_ebaydispatchtimemax' );
    $eBayAuthToken = $this->ebayApiToken;
    $ErrorLanguage = $this->getRequestContentAddItemFieldsErrorLanguage();
    $ItemID = $this->getRequestContentAddItemFieldsItemID( $forceItemID );
    $ListingDuration = $this->pObj->getDatamapRecord( 'tx_deal_ebaylistingduration' ); // http://developer.ebay.com/devzone/xml/docs/reference/ebay/types/ListingDurationCodeType.html
    $Location = $this->pObj->getDatamapRecord( 'tx_deal_ebaylocation' );
    $PictureDetails = $this->getRequestContentAddItemFieldsPictureDetails();
    $ProductListingDetails = $this->getRequestContentAddItemFieldsProductListingDetails();
    $Quantity = $this->pObj->getDatamapRecord( 'tx_deal_ebayquantity' );
    $PaymentMethods = $this->getRequestContentAddItemXmlPaymentmethods();
    $ReturnPolicy = $this->getRequestContentAddItemXmlReturnpolicy();
    $SiteCodeType = $this->ebayMarketplaceCountry;
    $SalesTaxPercent = $this->getRequestContentAddItemFieldsSalestaxpercent( 'tax' );
    $ShippingService = $this->getRequestContentAddItemFieldsShippingservicecode();
    $ShippingServiceAdditionalCosts = $this->pObj->getDatamapRecord( 'tx_deal_ebayshippingserviceadditionalcosts' );
    $ShippingServiceCosts = $this->pObj->getDatamapRecord( 'tx_deal_ebayshippingservicecosts' );
    $SKU = $this->getDatamapValueByTcaConfField( 'sku' );
    $StartPrice = $this->getDatamapValueByTcaConfField( 'gross' );
    $Title = '<![CDATA[' . $this->getDatamapValueByTcaConfField( 'title' ) . ']]>';
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
  <SalesTax>
    <SalesTaxPercent>' . $SalesTaxPercent . '</SalesTaxPercent>
  </SalesTax>
  <ShippingDetails>
    <ShippingType>Flat</ShippingType>
    <ShippingServiceOptions>
      <ShippingServicePriority>1</ShippingServicePriority>
      <ShippingService>' . $ShippingService . '</ShippingService>
      <ShippingServiceAdditionalCost>' . $ShippingServiceAdditionalCosts . '</ShippingServiceAdditionalCost>
      <ShippingServiceCost>' . $ShippingServiceCosts . '</ShippingServiceCost>
    </ShippingServiceOptions>
  </ShippingDetails>
  <Site>' . $SiteCodeType . '</Site>
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
    list( $ebay, $countryCode) = explode( '-', $this->ebayMarketplaceGlobalId );
    unset( $ebay );
    //var_dump(__METHOD__, __LINE__, $this->ebayMarketplaceCountry, $this->ebayMarketplaceGlobalId, $this->ebayMarketplaceSiteId);
    if ( empty( $countryCode ) )
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
    $arrDescription = $this->getTcaConfFields( 'description' );
    $field = $arrDescription[ 'datasheet' ];
    if ( empty( $field ) )
    {
      return null;
    }
    $datasheet = $this->pObj->getDatamapRecord( $field );
    if ( empty( $datasheet ) )
    {
      return null;
    }
    $lines = explode( PHP_EOL, $datasheet );
    foreach ( $lines as $key => $line )
    {
      $line = str_replace( ' |', ':', $line );
      $line = '<li>' . $line . '</li>';
      $lines[ $key ] = $line;
    }
    $header = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:headerDatasheet' );
    $datasheet = implode( PHP_EOL, $lines );
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
    $arrDescription = $this->getTcaConfFields( 'description' );
    $description = $this->pObj->getDatamapRecord( $arrDescription[ 'description' ] );
    if ( empty( $description ) )
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
    $paymentMethodsDescription = $this->pObj->getDatamapRecord( 'tx_deal_ebaypaymentmethodsdescription' );
    if ( empty( $paymentMethodsDescription ) )
    {
      return null;
    }
    $header = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:headerPaymentMethodsDescription' );
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
    $arrFilter = $this->getTcaConfFields( 'filter' );
    $field = $arrFilter[ 'category' ];
    if ( empty( $field ) )
    {
      return null;
    }

    $labels = $this->sqlGetLabelsFromForeignTables( $field );
    if ( empty( $labels ) )
    {
      return null;
    }

    $header = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:headerFilterCategories' );
    $categories = null
            . '<ul>' . PHP_EOL
            . '<li>' . implode( '</li>' . PHP_EOL . '<li>', $labels ) . '</li>' . PHP_EOL
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
    $arrFilter = $this->getTcaConfFields( 'filter' );
    $field = $arrFilter[ 'dimension' ];
    if ( empty( $field ) )
    {
      return null;
    }

    $labels = $this->sqlGetLabelsFromForeignTables( $field );
    if ( empty( $labels ) )
    {
      return null;
    }

    $header = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:headerFilterDimensions' );
    $dimensions = null
            . '<ul>' . PHP_EOL
            . '<li>' . implode( '</li>' . PHP_EOL . '<li>', $labels ) . '</li>' . PHP_EOL
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
    $arrFilter = $this->getTcaConfFields( 'filter' );
    $field = $arrFilter[ 'material' ];
    if ( empty( $field ) )
    {
      return null;
    }

    $labels = $this->sqlGetLabelsFromForeignTables( $field );
    if ( empty( $labels ) )
    {
      return null;
    }

    $header = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:headerFilterMaterial' );
    $material = null
            . '<ul>' . PHP_EOL
            . '<li>' . implode( '</li>' . PHP_EOL . '<li>', $labels ) . '</li>' . PHP_EOL
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
    $arrDescription = $this->getTcaConfFields( 'description' );
    $field = $arrDescription[ 'short' ];
    if ( empty( $field ) )
    {
      return null;
    }
    $short = $this->pObj->getDatamapRecord( $field );
    if ( empty( $short ) )
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
    $title = '<h1>' . $this->getDatamapValueByTcaConfField( 'title' ) . '</h1>' . PHP_EOL;
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
    $errorLanguage = $this->pObj->confArr[ 'ebayErrorLanguage' ];
    if ( empty( $errorLanguage ) )
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
  private function getRequestContentAddItemFieldsItemID( $forceItemID = false )
  {
    if ( $forceItemID )
    {
      if ( empty( $this->ebayItemId ) )
      {
        $this->ebayItemId = $this->getDatamapRecord( 'tx_deal_ebayitemid' );
      }
    }

    if ( empty( $this->ebayItemId ) )
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
   * @version  1.0.0
   * @since    0.0.3
   */
  private function getRequestContentAddItemFieldsPictureDetails()
  {
    global $TCA;
    $pictureDetails = null;
    // uploadfolder
    $table = $this->pObj->getDatamapTable();
    $tcaColumn = $this->getTcaConfFields( 'pictures' );
    $uploadFolder = $TCA[ $table ][ 'columns' ][ $tcaColumn ][ 'config' ][ 'uploadfolder' ];

    $urlToPicture = TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv( 'TYPO3_SITE_URL' ) . $uploadFolder . '/';

    // #i0015, 141012, dwildt, 1+
    $urlToPicture = str_replace( 'https://', 'http://', $urlToPicture );
//    var_dump(__METHOD__, __LINE__, $tcaColumn, $pictures, $uploadFolder, $GLOBALS['TYPO3_SITE_URL'],
//            TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), $url);
    $csvPictures = trim( $this->getDatamapValueByTcaConfField( 'pictures' ), ',' );
    $arrPictures = explode( ',', $csvPictures );

    $pictureUrl = null;
    foreach ( $arrPictures as $picture )
    {
      $pictureUrl = $pictureUrl
              . '    <PictureURL>' . $urlToPicture . $picture . '</PictureURL>' . PHP_EOL;
    }

    if ( empty( $pictureUrl ) )
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
    $ean = $this->getDatamapValueByTcaConfField( 'ean' );
    if ( !empty( $ean ) )
    {
      $ean = '  <EAN>' . $ean . '</EAN>';
    }

    switch ( true )
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
   * getRequestContentAddItemFieldsSalestaxpercent( )  : i.e. DE_Paket, DE_HermesPaket, USPSMedia
   *
   * @return	string  ebay shipping service code
   * @access private
   * @version  1.0.3
   * @since    1.0.3
   */
  private function getRequestContentAddItemFieldsSalestaxpercent()
  {
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log( $prompt, -1 );

    $SalesTaxPercent = $this->pObj->getDatamapRecord( 'tax' );

    switch ( true )
    {
      case( $SalesTaxPercent === NULL ):
        break;
      case( $SalesTaxPercent < 1 ):
        return $SalesTaxPercent;
      case( $SalesTaxPercent == 1 ):
        return 0.07;
      case( $SalesTaxPercent == 2 ):
        return 0.19;
    }

    $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebaySalestaxpercentUndefined' );
    $prompt = str_replace( '%tax%', $SalesTaxPercent, $prompt );
    $this->log( $prompt, 4 );
    return false;
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
    $this->log( $prompt, -1 );

    // #i0009, 140324, dwildt, 2+
    global $TCA;
    $table = $this->pObj->getDatamapTable();

    // #i0009, 140324, dwildt, 1-
    //$uid = $this->pObj->getDatamapRecord('tx_deal_ebayshippingservicecode');
    // #i0009, 140324, dwildt, 1+
    $uid = $this->pObj->getDatamapRecord( tx_deal_ebayshippingservicecode );

    if ( empty( $uid ) )
    {
      $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayShippingcostsEmpty' );
      $this->log( $prompt, 4 );
      return false;
    }

    $select_fields = 'code';
    // #i0009, 140324, dwildt, 1-
    //$from_table = 'tx_deal_ebayshippingservicecode';
    // #i0009, 140324, dwildt, 1+
    $from_table = $TCA[ $table ][ 'columns' ][ 'tx_deal_ebayshippingservicecode' ][ 'config' ][ 'foreign_table' ];
    $where_clause = 'uid = ' . $uid;
    $groupBy = null;
    $orderBy = null;
    $limit = null;

    $query = $GLOBALS[ 'TYPO3_DB' ]->SELECTquery
            (
            $select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit
    );
    //var_dump(__METHOD__, __LINE__, $query);
    // Set the query
    // Execute the query
    $res = $GLOBALS[ 'TYPO3_DB' ]->exec_SELECTquery
            (
            $select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit
    );
    // Execute the query
    // RETURN : ERROR
    $error = $GLOBALS[ 'TYPO3_DB' ]->sql_error();
    if ( !empty( $error ) )
    {
      $prompt = 'ERROR: Unproper SQL query at ' . __METHOD__ . ' (#' . __LINE__ . ')';
      $this->log( $prompt, 4, 2, 1 );
      $prompt = 'query: ' . $query;
      $this->log( $prompt, 0, 2, 1 );
      $prompt = 'prompt: ' . $error;
      $this->log( $prompt, 4, 2, 1 );

      return;
    }
    // RETURN : ERROR
    // Fetch first row only
    $row = $GLOBALS[ 'TYPO3_DB' ]->sql_fetch_assoc( $res );
    //var_dump(__METHOD__, __LINE__, $row);
    // Free the SQL result
    $GLOBALS[ 'TYPO3_DB' ]->sql_free_result( $res );

    if ( !$row )
    {
      return false;
    }

    return $row[ 'code' ];
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
    $csvPaymentMethods = trim( $this->pObj->getDatamapRecord( 'tx_deal_ebaypaymentmethods' ), ',' );
    $arrPaymentMethods = explode( ',', $csvPaymentMethods );

    $xmlTagPaymentMethod = null;
    $boolTagPaymentMethodPaypal = false;
    foreach ( ( array ) $arrPaymentMethods as $arrPaymentMethod )
    {
      if ( $arrPaymentMethod == 'PayPal' )
      {
        $boolTagPaymentMethodPaypal = true;
      }
      $xmlTagPaymentMethod = $xmlTagPaymentMethod . PHP_EOL
              . '  <PaymentMethods>' . $arrPaymentMethod . '</PaymentMethods>' . PHP_EOL
      ;
    }
    $xmlTag = $xmlTagPaymentMethod;
    if ( $boolTagPaymentMethodPaypal )
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
    $ReturnsAcceptedOption = $this->pObj->getDatamapRecord( 'tx_deal_ebayreturnsacceptoption' );
    $ReturnPolicyDescription = $this->pObj->getDatamapRecord( 'tx_deal_ebayreturnpolicydescription' );

    $xmlTagDescription = null;
    if ( !empty( $ReturnPolicyDescription ) )
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
    $ItemID = $this->getDatamapRecord( 'tx_deal_ebayitemid' );
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
    $ItemID = $this->getDatamapRecord( 'tx_deal_ebayitemid' );
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
    $xmlrequest = $this->getRequestContentAddItem( $forceItemID );
    return $xmlrequest;
  }

  /*   * *********************************************
   *
   * Request ReviseFixedPriceItem
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
  protected function getRequestContentReviseItem()
  {
    $forceItemID = true;
    $xmlrequest = $this->getRequestContentAddItem( $forceItemID );
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
  protected function getRequestHeader( $callName, $length )
  {
    $headers = array(
      'X-EBAY-API-CALL-NAME: ' . $callName,
      'X-EBAY-API-COMPATIBILITY-LEVEL: ' . $this->ebayApiVersion,
      'X-EBAY-API-SITEID: ' . $this->ebayMarketplaceSiteId,
      'Content-Type: text/xml;charset=utf-8',
      'Content-Length: ' . $length,
    );

    $prompt = 'headers: ' . PHP_EOL . var_export( $headers, true );
    $this->log( $prompt, -1 );

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
  private function getTcaConfFields( $key = null )
  {
    $tcaConf = $this->getTcaConf();
    $fields = $tcaConf[ 'fields' ];

    if ( empty( $key ) )
    {
      return $fields;
    }
    return $fields[ $key ];
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
  protected function init( $pObj )
  {
    if ( $this->init !== null )
    {
      return;
    }
    $this->init = true;

    $this->initVarsPobj( $pObj );
    $this->initConf();
    $this->initVarsEbay();

    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log( $prompt, -1 );
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
  private function initVars( $pObj )
  {
    $this->initVarsPobj( $pObj );
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
    switch ( true )
    {
      case( $this->ebayEnvironment == 'production' ):
        $this->ebayApiEndpointXml = $this->ebayApiEndpointXmlProduction;
        break;
      case( $this->ebayEnvironment == 'sandbox' ):
        $this->ebayApiEndpointXml = $this->ebayApiEndpointXmlSandbox;
        break;
      case( empty( $this->ebayEnvironment ) ):
// RETURN : ebay isn't enabled
        $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayEnvironmentFalse' );
        $this->log( $prompt, 0, 2 );
        break;
      default:
// RETURN : ebay isn't enabled
        $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayEnvironmentError' );
        $prompt = str_replace( '%ebayEnvironment%', $this->ebayEnvironment, $prompt );
        $this->log( $prompt, 4, 2 );
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
    switch ( true )
    {
      case( $this->ebayEnvironment == 'production' ):
        $this->ebayApiToken = $this->ebayApiConfFromTCA[ 'environment' ][ 'production' ][ 'token' ];
        break;
      case( $this->ebayEnvironment == 'sandbox' ):
        $this->ebayApiToken = $this->ebayApiConfFromTCA[ 'environment' ][ 'sandbox' ][ 'token' ];
        break;
      case( empty( $this->ebayEnvironment ) ):
        $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayEnvironmentFalse' );
        $this->log( $prompt, 4, 2 );
        break;
      default:
        $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayEnvironmentError' );
        $prompt = str_replace( '%ebayEnvironment%', $this->ebayEnvironment, $prompt );
        $this->log( $prompt, 4, 2 );
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
    $this->ebayEnvironment = $this->ebayApiConfFromTCA[ 'environment' ][ 'key' ];
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
    $this->ebayMarketplace = $this->ebayApiConfFromTCA[ 'marketplace' ];
    if ( empty( $this->ebayMarketplace ) )
    {
      $this->ebayMarketplace = 'US - EBAY-US/USD (0)';
    }

    list( $country, $ebayCode ) = explode( ' - ', $this->ebayMarketplace );
    list( $globalId, $currencyAndSiteid) = explode( '/', $ebayCode );
    list( $currency, $siteId) = explode( ' ', $currencyAndSiteid );

    $this->ebayMarketplaceCountry = $country;
    $this->ebayMarketplaceCurrency = $currency;
    $this->ebayMarketplaceGlobalId = $globalId;
    $this->ebayMarketplaceSiteId = trim( $siteId, '()' );
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
    switch ( true )
    {
      case( $this->ebayEnvironment == 'production' ):
        $this->ebayPaypalEmail = $this->ebayApiConfFromTCA[ 'paypal' ][ 'production' ][ 'email' ];
        break;
      case( $this->ebayEnvironment == 'sandbox' ):
        $this->ebayPaypalEmail = $this->ebayApiConfFromTCA[ 'paypal' ][ 'sandbox' ][ 'email' ];
        break;
      case( empty( $this->ebayEnvironment ) ):
// RETURN : ebay isn't enabled
        $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayEnvironmentFalse' );
        $this->log( $prompt, 0, 2 );
        break;
      default:
// RETURN : ebay isn't enabled
        $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayEnvironmentError' );
        $prompt = str_replace( '%ebayEnvironment%', $this->ebayEnvironment, $prompt );
        $this->log( $prompt, 4, 2 );
        break;
    }

    if ( $this->ebayPaypalEmail )
    {
      return;
    }

    $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayPaypalEmailEmpty' );
    $this->log( $prompt, 3 );
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
  private function initVarsPobj( $pObj )
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
  protected function log( $prompt, $status = -1, $action = 2, $header = 2 )
  {
    $this->pObj->log( $prompt, $status, $action, $header );
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
    switch ( true )
    {
      case( empty( $this->ebayApiEndpointXml )):
        $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayApiEndpointEmpty' );
        $this->log( $prompt, 4, 2 );
        return false;
      case( empty( $this->ebayApiToken )):
        $prompt = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayApiTokenEmpty' );
        $this->log( $prompt, 4, 2 );
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
  private function setDatamapRecordFieldPrepend( $key, $value, $boolWiEol = true )
  {
    $this->pObj->setDatamapRecordFieldPrepend( $key, $value, $boolWiEol );
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
  private function setDatamapRecordFieldUpdate( $key, $value, $boolPrompt = true )
  {
    $this->pObj->setDatamapRecordFieldUpdate( $key, $value, $boolPrompt );
  }

  /**
   * setEbayItemStatus( )
   *
   * @param   boolean   $dontPrompt : do not prompt to the backend form (optional)
   * @return	string    $status : isNotOnEbay, isOnEbayEnabled, isOnEbayDisabled, isWoStatus
   * @access public
   * @version   0.1.2
   * @since     0.0.3
   */
  public function setEbayItemStatus( $dontPrompt = false )
  {
    $status = 'undefined (by ' . __METHOD__ . ' #' . __LINE__ . ')';
    $ebayItemID = $this->getDatamapRecord( 'tx_deal_ebayitemid' );
    switch ( true )
    {
      case(!empty( $ebayItemID )):
        $status = $this->setEbayItemStatusWiItemID( $dontPrompt );
        // #i0014, 141002, dwildt, 4+
        if ( empty( $status ) )
        {
          $status = 'isWoStatus';
        }
        break;
      case(empty( $ebayItemID )):
      default:
        $status = $this->setEbayItemStatusWoItemID();
        break;
    }

    switch ( $status )
    {
      case( 'isNotOnEbay'):
      case( 'isOnEbayEnabled'):
      case( 'isOnEbayDisabled'):
      case( 'isWoStatus'):
        return $status;
      default:
        $prompt = __METHOD__ . ' (#' . __LINE__ . '): ebay status is undefined: "' . $status . '"';
        die( $prompt );
    }
    return $status;
  }

  /**
   * setEbayItemStatusWiItemID( )
   *
   * @param   boolean   $dontPrompt : do not prompt to the backend form (optional)
   * @return	string    $status : isNotOnEbay, isOnEbayEnabled, isOnEbayDisabled, isWoStatus
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function setEbayItemStatusWiItemID( $dontPrompt = false )
  {
    $status = null;
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log( $prompt, -1 );

    $status = $this->getItem( $dontPrompt );

    return $status;
  }

  /**
   * setEbayItemStatusWoItemID( )
   *
   * @return	string    $status : isNotOnEbay
   * @access private
   * @version   0.0.3
   * @since     0.0.3
   */
  private function setEbayItemStatusWoItemID()
  {
    $status = 'isNotOnEbay';
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log( $prompt, -1 );

    $key = 'tx_deal_ebayitemstatus';
    $value = $GLOBALS[ 'LANG' ]->sL( 'LLL:EXT:deal/lib/marketplaces/ebay/api/locallang.xml:ebayItemIsNotOnEbay' );
    $boolPrompt = false;
    $this->setDatamapRecordFieldUpdate( $key, $value, $boolPrompt );

    return $status;
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
  private function sqlGetLabelsFromForeignTables( $tcaField )
  {
    global $TCA;

    $arrFilter = $this->getTcaConfFields( 'filter' );
    $field = $arrFilter[ $tcaField ];
    if ( empty( $field ) )
    {
      return null;
    }
    $uids = $this->pObj->getDatamapRecord( $field );
    $table = $this->pObj->getDatamapTable();

    if ( empty( $uids ) )
    {
      return null;
    }

    $uids = trim( $uids, ',' );
    $foreign_table = $TCA[ $table ][ 'columns' ][ $tcaField ][ 'config' ][ 'foreign_table' ];
    $label = $TCA[ $foreign_table ][ 'ctrl' ][ 'label' ];

    $select_fields = $label;
    $from_table = $foreign_table;
    $where_clause = 'uid IN (' . $uids . ')';
    $groupBy = null;
    $orderBy = null;
    $limit = null;

    $query = $GLOBALS[ 'TYPO3_DB' ]->SELECTquery(
            $select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit
    );
    //var_dump(__METHOD__, __LINE__, $query);
    // Set the query
    // Execute the query
    $res = $GLOBALS[ 'TYPO3_DB' ]->exec_SELECTquery
            (
            $select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit
    );
    // Execute the query
    // RETURN : ERROR
    $error = $GLOBALS[ 'TYPO3_DB' ]->sql_error();
    if ( !empty( $error ) )
    {
      $prompt = 'ERROR: Unproper SQL query at ' . __METHOD__ . ' (#' . __LINE__ . ')';
      $this->log( $prompt, 4, 2, 1 );
      $prompt = 'query: ' . $query;
      $this->log( $prompt, 0, 2, 1 );
      $prompt = 'prompt: ' . $error;
      $this->log( $prompt, 4, 2, 1 );

      return;
    }
    // RETURN : ERROR
    // Fetch first row only
    $lines = array();
    while ( $row = $GLOBALS[ 'TYPO3_DB' ]->sql_fetch_assoc( $res ) )
    {
      //var_dump(__METHOD__, __LINE__, $row);
      //$lines[] = '<li>' . $row[$label] . '</li>';
      $lines[] = $row[ $label ];
    }
    // Free the SQL result
    $GLOBALS[ 'TYPO3_DB' ]->sql_free_result( $res );

    return $lines;
  }

}

if ( defined( 'TYPO3_MODE' ) && $TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/deal/lib/marketplaces/ebay/api/class.tx_deal_ebayApiBase.php' ] )
{
  include_once($TYPO3_CONF_VARS[ TYPO3_MODE ][ 'XCLASS' ][ 'ext/deal/lib/marketplaces/ebay/api/class.tx_deal_ebayApiBase.php' ]);
}