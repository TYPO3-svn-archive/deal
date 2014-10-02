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

require_once(t3lib_extMgm::extPath('deal') . 'lib/marketplaces/ebay/api/class.tx_deal_ebayApiBase.php' );

/**
 * plugin 'ebay 3 items' for the 'deal' extension.
 *
 * @author	Dirk Wildt <http://wildt.at.die-netzmacher.de>
 * @package	TYPO3
 * @subpackage	tx_deal
 * @internal    #i0003
 * @version     0.0.3
 * @since       0.0.3
 */
class tx_deal_ebayApi_fixedPriceItem extends tx_deal_ebayApiBase
{

  public $prefixId = 'tx_deal_ebayApi_fixedPriceItem';
  public $extKey = 'deal';

  /*   * ********************************************
   *
   * addFixedPriceItem
   *
   * ******************************************** */

  /**
   * addFixedPriceItem( ) :
   *
   * @return	mixed     $status : false, 'isNotOnEbay', 'isOnEbayEnabled', 'isOnEbayDisabled', 'isWoStatus'
   * @link http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/AddFixedPriceItem.html#samplebasic
   * @access public
   * @version  0.0.3
   * @since    0.0.3
   */
  public function addFixedPriceItem()
  {
    $status = 'undefined (by ' . __METHOD__ . ' #' . __LINE__ . ')';

    $this->init($this->pObj);

    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    if (!$this->requirements())
    {
      return false;
    }

    $xmlAction = 'AddFixedPriceItem';
    $xmlRequest = $this->xmlRequestAddItem($xmlAction);
    $xmlResponse = $this->xmlResponse($xmlRequest, $xmlAction);

    if (!$this->evalResponse($xmlRequest, $xmlResponse, $xmlAction))
    {
      return false;
    }

    if ($this->getEbayMode() != 'test')
    {
      $this->feesPrompt($xmlResponse);
    }

    $status = $this->getItem();
    return $status;
  }

  /*   * ********************************************
   *
   * endFixedPriceItem
   *
   * ******************************************** */

  /**
   * endFixedPriceItem( ) :
   *
   * @return	boolean
   * @return	mixed     $status : false, 'isNotOnEbay', 'isOnEbayEnabled', 'isOnEbayDisabled', 'isWoStatus'
   * @link http://developer.ebay.com/DevZone/XML/docs/Reference/ebay/EndFixedPriceItem.html
   * @access public
   * @version  0.0.3
   * @since    0.0.3
   */
  public function endFixedPriceItem()
  {
    $status = 'undefined (by ' . __METHOD__ . ' #' . __LINE__ . ')';

    $this->init($this->pObj);

    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    if (!$this->requirements())
    {
      return false;
    }

    $xmlAction = 'EndFixedPriceItem';
    $xmlRequest = $this->xmlRequestEndItem($xmlAction);
    $xmlResponse = $this->xmlResponse($xmlRequest, $xmlAction);

    $status = $this->evalResponse($xmlRequest, $xmlResponse, $xmlAction);

    //$status = $this->getItem($action);
    return $status;
  }

  /**
   * getItem( ) :
   *
   * @param   boolean   $dontPrompt : do not prompt to the backend form (optional)
   * @return	mixed     $status : false, 'isNotOnEbay', 'isOnEbayEnabled', 'isOnEbayDisabled', 'isWoStatus'
   * @link http://developer.ebay.com/DevZone/XML/docs/Reference/ebay/GetItem.html#GetItem
   * @access protected
   * @version  0.0.3
   * @since    0.0.3
   */
  protected function getItem($dontPrompt = false)
  {
    $status = 'undefined (by ' . __METHOD__ . ' #' . __LINE__ . ')';
    $this->init($this->pObj);

    $xmlAction = 'GetItem';
    $xmlRequest = $this->xmlRequestGetItem($xmlAction);
    $xmlResponse = $this->xmlResponse($xmlRequest, $xmlAction);
    $status = $this->evalResponse($xmlRequest, $xmlResponse, $xmlAction, $dontPrompt);
    return $status;
  }

  /*   * ********************************************
   *
   * RelistFixedPriceItem
   *
   * ******************************************** */

  /**
   * relistFixedPriceItem( ) :
   *
   * @return	mixed     $status : false, 'isNotOnEbay', 'isOnEbayEnabled', 'isOnEbayDisabled', 'isWoStatus'
   * @link http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/AddFixedPriceItem.html#samplebasic
   * @access public
   * @version  0.0.3
   * @since    0.0.3
   */
  public function relistFixedPriceItem()
  {
    $status = 'undefined (by ' . __METHOD__ . ' #' . __LINE__ . ')';

    $this->init($this->pObj);

    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    if (!$this->requirements())
    {
      return false;
    }

    $xmlAction = 'RelistFixedPriceItem';
    $xmlRequest = $this->xmlRequestRelistItem($xmlAction);
    $xmlResponse = $this->xmlResponse($xmlRequest, $xmlAction);

    if (!$this->evalResponse($xmlRequest, $xmlResponse, $xmlAction))
    {
      $this->feesPrompt($xmlResponse);
      return false;
    }

    if ($this->getEbayMode() != 'test')
    {
      $this->feesPrompt($xmlResponse);
    }

    return $status;
  }

  /*   * ********************************************
   *
   * ReviseFixedPriceItem
   *
   * ******************************************** */

  /**
   * reviseFixedPriceItem( ) :
   *
   * @return	mixed     $status : false, 'isNotOnEbay', 'isOnEbayEnabled', 'isOnEbayDisabled', 'isWoStatus'
   * @link http://developer.ebay.com/DevZone/XML/docs/Reference/ebay/ReviseItem.html#ReviseItem
   * @access public
   * @version  0.0.3
   * @since    0.0.3
   */
  public function reviseFixedPriceItem()
  {
    $xmlAction = 'ReviseFixedPriceItem';

    $this->init($this->pObj);

    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    if (!$this->requirements())
    {
      return false;
    }

    $xmlRequest = $this->xmlRequestReviseItem($xmlAction);
    $xmlResponse = $this->xmlResponse($xmlRequest, $xmlAction);

    if (!$this->evalResponse($xmlRequest, $xmlResponse, $xmlAction))
    {
      return false;
    }

    if ($this->getEbayMode() != 'test')
    {
      $this->feesPrompt($xmlResponse);
    }

//    $status = $this->getItem();
//    $prompt = __METHOD__ . ' #' . __LINE__ . ' status: ' . $status;
//    $this->log($prompt, 3);
    return true;
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
   * verifyAddFixedPriceItem( ) :
   *
   * @return	boolean
   * @link http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/VerifyAddFixedPriceItem.html
   * @access public
   * @version  0.0.3
   * @since    0.0.3
   */
  public function verifyAddFixedPriceItem()
  {

    $this->init($this->pObj);

    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    if (!$this->requirements())
    {
      return false;
    }

    $xmlAction = 'VerifyAddFixedPriceItem';
    $xmlRequest = $this->xmlRequestAddItem($xmlAction);
    $xmlResponse = $this->xmlResponse($xmlRequest, $xmlAction);
    if (!$this->evalResponse($xmlRequest, $xmlResponse, $xmlAction))
    {
      return false;
    }

    if ($this->getEbayMode() == 'test')
    {
      $this->feesPrompt($xmlResponse);
    }
    return true;
  }

  /**
   * verifyReviseFixedPriceItem( ) :
   *
   * @return	boolean
   * @link http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/VerifyAddFixedPriceItem.html
   * @access public
   * @version  0.0.3
   * @since    0.0.3
   */
  public function verifyReviseFixedPriceItem()
  {

    $this->init($this->pObj);

    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    if (!$this->requirements())
    {
      return false;
    }

    $xmlAction = 'VerifyAddFixedPriceItem';
    $xmlRequest = $this->xmlRequestReviseItem($xmlAction);
    $xmlResponse = $this->xmlResponse($xmlRequest, $xmlAction);
    if (!$this->evalResponse($xmlRequest, $xmlResponse, $xmlAction))
    {
      return false;
    }

    if ($this->getEbayMode() == 'test')
    {
      $this->feesPrompt($xmlResponse);
    }
    return true;
  }

  /**
   * verifyItem( ) :
   *
   * @return	boolean
   * @access public
   * @version  0.0.3
   * @since    0.0.3
   */
  public function verifyItem()
  {
    $prompt = __METHOD__ . ' #' . __LINE__;
    $this->log($prompt, -1);

    $ebayItemId = $this->pObj->getDatamapRecord('tx_deal_ebayitemid');
//    if (!empty($ebayItemId))
//    {
//      $this->ebayItemId = $ebayItemId;
//      return true;
//    }
    unset($ebayItemId);

    return $this->verifyAddFixedPriceItem();
  }

  /**
   * xmlRequestAddItem( )  : Create the XML request to be POSTed
   *
   * @param   string      $method     : AddFixedPriceItemRequest, VerifyAddFixedPriceItemRequest
   * @return	string      $xmlRequest :
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function xmlRequestAddItem($method)
  {
    $xmlRequest = '<?xml version="1.0" encoding="utf-8"?>
<' . $method . ' xmlns="urn:ebay:apis:eBLBaseComponents">
' . $this->getRequestContentAddItem() . '
</' . $method . '>';
    return $xmlRequest;
  }

  /**
   * xmlRequestEndItem( )  : Create the XML request to be POSTed
   *
   * @param   string      $method     : AddFixedPriceItemRequest, VerifyAddFixedPriceItemRequest
   * @return	string      $xmlRequest :
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function xmlRequestEndItem($method)
  {
    $xmlRequest = '<?xml version="1.0" encoding="utf-8"?>
<' . $method . ' xmlns="urn:ebay:apis:eBLBaseComponents">
' . $this->getRequestContentEndItem() . '
</' . $method . '>';
    return $xmlRequest;
  }

  /**
   * xmlRequestGetItem( )  : Create the XML request to be POSTed
   *
   * @param   string      $method     : AddFixedPriceItemRequest, VerifyAddFixedPriceItemRequest
   * @return	string      $xmlRequest :
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function xmlRequestGetItem($method)
  {
    $xmlRequest = '<?xml version="1.0" encoding="utf-8"?>
<' . $method . ' xmlns="urn:ebay:apis:eBLBaseComponents">
' . $this->getRequestContentGetItem() . '
</' . $method . '>';
    return $xmlRequest;
  }

  /**
   * xmlRequestRelistItem( )  : Create the XML request to be POSTed
   *
   * @param   string      $method     : AddFixedPriceItemRequest, VerifyAddFixedPriceItemRequest
   * @return	string      $xmlRequest :
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function xmlRequestRelistItem($method)
  {
    $xmlRequest = '<?xml version="1.0" encoding="utf-8"?>
<' . $method . ' xmlns="urn:ebay:apis:eBLBaseComponents">
' . $this->getRequestContentRelistItem() . '
</' . $method . '>';
    return $xmlRequest;
  }

  /**
   * xmlRequestReviseItem( )  : Create the XML request to be POSTed
   *
   * @param   string      $method     : AddFixedPriceItemRequest, VerifyAddFixedPriceItemRequest
   * @return	string      $xmlRequest :
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function xmlRequestReviseItem($method)
  {
    $xmlRequest = '<?xml version="1.0" encoding="utf-8"?>
<' . $method . ' xmlns="urn:ebay:apis:eBLBaseComponents">
' . $this->getRequestContentReviseItem() . '
</' . $method . '>';
    return $xmlRequest;
  }

  /**
   * xmlResponse( ) :
   *
   * @param   string      $xmlRequest   : XML request
   * @param   string      $method       : AddFixedPriceItem, VerifyAddFixedPriceItem
   * @return	string      $responsexml  :
   * @access private
   * @version  0.0.3
   * @since    0.0.3
   */
  private function xmlResponse($xmlRequest, $method)
  {

    $headers = $this->getRequestHeader($method, strlen($xmlRequest));

    $session = curl_init($this->ebayApiEndpointXml);                        // create a curl session
    curl_setopt($session, CURLOPT_POST, true);              // POST request type
    curl_setopt($session, CURLOPT_HTTPHEADER, $headers);    // set headers using $headers array
    curl_setopt($session, CURLOPT_POSTFIELDS, $xmlRequest); // set the body of the POST
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);    // return values as a string, not to std out

    $responsexml = curl_exec($session);                     // send the request
    curl_close($session);                                   // close the session

    $responsexml = simplexml_load_string($responsexml);
    return $responsexml;                                    // returns a string
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/lib/marketplaces/ebay/api/class.tx_deal_ebayApi_fixedPriceItem.php'])
{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/lib/marketplaces/ebay/api/class.tx_deal_ebayApi_fixedPriceItem.php']);
}
