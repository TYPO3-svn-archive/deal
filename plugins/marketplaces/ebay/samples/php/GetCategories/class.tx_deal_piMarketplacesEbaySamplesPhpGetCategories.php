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

require_once( PATH_tslib . 'class.tslib_pibase.php' );

/**
 * plugin 'ebay sample start' for the 'deal' extension.
 *
 * @author	Dirk Wildt <http://wildt.at.die-netzmacher.de>
 * @package	TYPO3
 * @subpackage	tx_deal
 * @version	0.0.1
 * @since       0.0.1
 */
class tx_deal_piMarketplacesEbaySamplesPhpGetCategories extends tslib_pibase
{

  public $prefixId = 'tx_deal_piMarketplacesEbaySamplesPhpGetCategories';
  public $scriptRelPath = 'plugins/marketplaces/ebay/samples/php/GetCategories/class.tx_deal_piMarketplacesEbaySamplesPhpGetCategories.php';
  public $extKey = 'deal';
  public $local_cObj = null;
  public $conf = null;
  public $arr_extConf = null;

  /**
   * main( ) : the main method of the PlugIn
   *
   * @param	string		$content  : plugin content. Usually empty.
   * @param	array		$conf     : plugin configuration.
   * @return	string		$content  : The content that is displayed on the website
   * @version  0.0.1
   * @since    0.0.1
   */
  public function main($content, $conf)
  {

    $this->conf = $conf;
    $this->pi_loadLL();

    $content = $this->getCategories();

    return $this->pi_wrapInBaseClass($content);
  }

  /*   * *********************************************
   *
   * ebay
   *
   * ******************************************** */

  /**
   * getCategories( ) :
   *
   * @return	string		$content  :
   * @internal   See http://developer.ebay.com/DevZone/XML/docs/Reference/ebay/GetCategories.html
   * @access private
   * @version  0.0.1
   * @since    0.0.1
   */
  private function getCategories()
  {

    // 140217, dwildt, 1-
    //error_reporting(E_ALL);  // Turn on all errors, warnings and notices for easier debugging
    // API request variables
    $endpoint = 'https://api.sandbox.ebay.com/ws/api.dll';    // URL to call
    $endpoint = 'https://api.ebay.com/ws/api.dll';            // URL to call
    // constructPostCallAndGetResponse
    $resp = simplexml_load_string($this->constructPostCallAndGetResponse($endpoint));

// Check to see if the call was successful, else print an error
    if ($resp->Ack == "Success")
    {
//      $results = '<tr>'
//              . '<th>AutoPayEnabled</th>'
//              . '<th>BestOfferEnabled</th>'
//              . '<th>CategoryID</th>'
//              . '<th>CategoryLevel</th>'
//              . '<th>CategoryName</th>'
//              . '<th>CategoryParentID</th>'
//              . '</tr>'
//      ;
//      // Parse the desired information from the response
//      foreach ($resp->CategoryArray->Category as $category)
//      {
//        $AutoPayEnabled = $category->AutoPayEnabled;
//        $BestOfferEnabled = $category->BestOfferEnabled;
//        $CategoryID = $category->CategoryID;
//        $CategoryLevel = $category->CategoryLevel;
//        $CategoryName = $category->CategoryName;
//        $CategoryParentID = $category->CategoryParentID;
//
//        // Build the desired HTML code for each searchResult.item node and append it to $results
//        $results = $results
//                . '<tr>'
//                . '<td>' . $AutoPayEnabled . '</td>'
//                . '<td>' . $BestOfferEnabled . '</td>'
//                . '<td>' . $CategoryID . '</td>'
//                . '<td>' . $CategoryLevel . '</td>'
//                . '<td>' . $CategoryName . '</td>'
//                . '<td>' . $CategoryParentID . '</td>'
//                . '</tr>'
//        ;
//      }
      $results = '<tr>'
              . '<th>uid</th>'
              . '<th>uid_parent</th>'
              . '<th>title</th>'
              . '</tr>'
      ;
      // Parse the desired information from the response
      foreach ($resp->CategoryArray->Category as $category)
      {
        $CategoryID = $category->CategoryID;
        $CategoryName = $category->CategoryName;
        $CategoryParentID = $category->CategoryParentID;

        // Build the desired HTML code for each searchResult.item node and append it to $results
        $results = $results
                . '<tr>'
                . '<td>' . $CategoryID . '</td>'
                . '<td>' . $CategoryParentID . '</td>'
                . '<td>' . $CategoryName . '</td>'
                . '</tr>'
        ;
      }
    }
    else
    {
      $results = ''
              . '<h3>'
              . $this->pi_getLL('promptError')
              . '</h3>'
              . '<pre>' . var_export($resp, true) . '</pre>'
      //. '<pre>' . var_export( $resp->Errors, true ) . '</pre>'
      ;
    }

    $content = '
      <h1>
        ' . $this->pi_getLL('promptSuccessHeader') . ' ' . $query . '
      </h1>
      <table style="font-size:.9em;">
        <tr>
          <td>
            ' . $results . '
          </td>
        </tr>
      </table>
      ';
//      <pre>' . var_export( $resp, true ) . '</pre>

    return $content;
  }

  /**
   * constructPostCallAndGetResponse( ) : Construct the findItemsByKeywords POST call
   *                                      Load the call and capture the response returned by the eBay API
   *                                      the constructCallAndGetResponse function is defined below
   *
   * @param
   * @return	string		$responsexml  :
   * @access private
   * @link      http://developer.ebay.com/DevZone/XML/docs/Reference/ebay/GetCategories.html
   * @version  0.0.1
   * @since    0.0.1
   */
  private function constructPostCallAndGetResponse($endpoint)
  {
    $xmlrequest = null;

    $version = '859';                                                       // API version supported by your application. See http://developer.ebay.com/webservices/latest/ebaySvc.xsd
    $authToken = $this->conf['constanteditor.']['authToken'];               // Replace with your own auth token
    $categoryParent = $this->conf['constanteditor.']['categoryParent'];     //
    if ((int) $categoryParent > 0)
    {
      $categoryParent = '<CategoryParent>' . $categoryParent . '</CategoryParent>';
    }
    $levelLimit = $this->conf['constanteditor.']['levelLimit'];     //
    if ((int) $levelLimit > 0)
    {
      $levelLimit = '<levelLimit>' . $levelLimit . '</levelLimit>';
    }
    $siteid = $this->conf['constanteditor.']['siteid'];                     // Site ID of the eBay site you want to search (e.g., 77 for EBAY-DE)
    // Create the XML request to be POSTed
    $xmlrequest = '' .
            '<?xml version="1.0" encoding="utf-8"?>
<GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $authToken . '</eBayAuthToken>
  </RequesterCredentials>
  ' . $categoryParent . '
  <CategorySiteID>' . $siteid . '</CategorySiteID>
  <DetailLevel>ReturnAll</DetailLevel>
  ' . $levelLimit . '
</GetCategoriesRequest>';

//    // Set up the HTTP headers
//    $headers = array(
//        'X-EBAY-SOA-OPERATION-NAME: GetCategoriesRequest',
//        'X-EBAY-SOA-SERVICE-VERSION: ' . $version,
//        'X-EBAY-SOA-REQUEST-DATA-FORMAT: XML',
//        'X-EBAY-SOA-GLOBAL-ID: ' . $globalid,
//        'X-EBAY-SOA-SECURITY-APPNAME: ' . $appid,
//        'Content-Type: text/xml;charset=utf-8',
//    );
    // Set up the HTTP headers
    $headers = array(
      'X-EBAY-API-CALL-NAME: ' . 'GetCategories',
      'X-EBAY-API-COMPATIBILITY-LEVEL: ' . $version,
      'X-EBAY-API-SITEID: ' . $siteid, // 0: US, 71: French, 77: Germany. See: http://developer.ebay.com/DevZone/finding/CallRef/Enums/GlobalIdList.html
      'Content-Type: ' . 'text/xml;charset=utf-8',
    );

//var_dump( $xmlrequest );
//var_dump( $headers );
    $session = curl_init($endpoint);                        // create a curl session
    curl_setopt($session, CURLOPT_POST, true);              // POST request type
    curl_setopt($session, CURLOPT_HTTPHEADER, $headers);    // set headers using $headers array
    curl_setopt($session, CURLOPT_POSTFIELDS, $xmlrequest); // set the body of the POST
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);    // return values as a string, not to std out

    $responsexml = curl_exec($session);                     // send the request
    curl_close($session);                                   // close the session
    return $responsexml;                                    // returns a string
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/plugins/marketplaces/ebay/samples/php/GetCategories/class.tx_deal_piMarketplacesEbaySamplesPhpGetCategories.php'])
{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/plugins/marketplaces/ebay/samples/php/GetCategories/class.tx_deal_piMarketplacesEbaySamplesPhpGetCategories.php']);
}
