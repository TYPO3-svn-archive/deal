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
class tx_deal_piMarketplacesEbaySamplesPhpGettingstarted extends tslib_pibase {

  public $prefixId = 'tx_deal_piMarketplacesEbaySamplesPhpGettingstarted';
  public $scriptRelPath = 'plugins/marketplaces/ebay/samples/php/GettingStarted_PHP_XML_XML/class.tx_deal_piMarketplacesEbaySamplesPhpGettingstarted.php';
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
  public function main($content, $conf) {

    $this->conf = $conf;
    $this->pi_loadLL();

    $content = $this->ebaySampleStart();

    return $this->pi_wrapInBaseClass($content);
  }

  /*   * *********************************************
   *
   * ebay
   *
   * ******************************************** */

  /**
   * ebaySampleStart( ) :
   *
   * @return	string		$content  : 
   * @internal   See deal/lib/marketplaces/ebay/samples/php/GettingStarted_PHP_XML_XML/Sample_GettingStarted_PHP_XML_XML.php
   * @access private
   * @version  0.0.1
   * @since    0.0.1
   */
  private function ebaySampleStart() {

    // 140217, dwildt, 1-
    //error_reporting(E_ALL);  // Turn on all errors, warnings and notices for easier debugging
    // API request variables
    $endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';   // URL to call
    $query = $this->conf['constanteditor.']['query'];                       // You may want to supply your own query
    $i = '0';                                                               // Initialize the item filter index to 0
    // Create a PHP array of the item filters you want to use in your request
    $filterarray =
            array(
                array(
                    'name' => 'MaxPrice',
                    'value' => $this->conf['constanteditor.']['currency.']['value'],
                    'paramName' => 'Currency',
                    'paramValue' => $this->conf['constanteditor.']['currency.']['paramValue']),
                array(
                    'name' => 'FreeShippingOnly',
                    'value' => 'true',
                    'paramName' => '',
                    'paramValue' => ''),
                array(
                    'name' => 'ListingType',
                    'value' => array('AuctionWithBIN', 'FixedPrice', 'StoreInventory'),
                    'paramName' => '',
                    'paramValue' => ''),
    );

    // Build the item filter XML code
    $this->buildXMLFilter($filterarray);

    // constructPostCallAndGetResponse
    $resp = simplexml_load_string($this->constructPostCallAndGetResponse($endpoint, $query, $xmlfilter));

// Check to see if the call was successful, else print an error
    if ($resp->ack == "Success") {
      $results = '';  // Initialize the $results variable
      // Parse the desired information from the response
      foreach ($resp->searchResult->item as $item) {
        $pic = $item->galleryURL;
        $link = $item->viewItemURL;
        $title = $item->title;

        // Build the desired HTML code for each searchResult.item node and append it to $results
        $results .= "<tr><td><img src=\"$pic\"></td><td><a href=\"$link\">$title</a></td></tr>";
      }
    } else {
      $results = '<h3>
        ' . $this->pi_getLL('promptErrorAppID') . '
      </h3>';
    }

    $content = '
      <h1>
        ' . $this->pi_getLL('promptSuccessHeader') . ' ' . $query . '
      </h1>
      <table>
        <tr>
          <td>
            ' . $results . '
          </td>
        </tr>
      </table>';

    return $content;
  }

  /**
   * buildXMLFilter( ) : Generates an XML snippet from the array of item filters
   *
   * @param     array           $filterarray  :
   * @return	string		$xmlfilter    : 
   * @access private
   * @version  0.0.1
   * @since    0.0.1
   */
  private function buildXMLFilter($filterarray) {
    $xmlfilter = null;

    // Iterate through each filter in the array
    foreach ($filterarray as $itemfilter) {
      $xmlfilter .= "<itemFilter>\n";
      // Iterate through each key in the filter
      foreach ($itemfilter as $key => $value) {
        if (is_array($value)) {
          // If value is an array, iterate through each array value
          foreach ($value as $arrayval) {
            $xmlfilter .= " <$key>$arrayval</$key>\n";
          }
        } else {
          if ($value != "") {
            $xmlfilter .= " <$key>$value</$key>\n";
          }
        }
      }
      $xmlfilter .= "</itemFilter>\n";
    }
    return "$xmlfilter";
  }

  /**
   * constructPostCallAndGetResponse( ) : Construct the findItemsByKeywords POST call
   *                                      Load the call and capture the response returned by the eBay API
   *                                      the constructCallAndGetResponse function is defined below
   *
   * @param
   * @param
   * @param
   * @return	string		$responsexml  : 
   * @access private
   * @version  0.0.1
   * @since    0.0.1
   */
  private function constructPostCallAndGetResponse($endpoint, $query, $xmlfilter) {
    $xmlrequest = null;

    $version = '1.3.0';                                                     // API version supported by your application
    $appid = $this->conf['constanteditor.']['appid'];                       // Replace with your own AppID
    $globalid = $this->conf['constanteditor.']['globalid'];                 // Global ID of the eBay site you want to search (e.g., EBAY-DE)
    // Create the XML request to be POSTed
    $xmlrequest = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
    $xmlrequest .= "<findItemsByKeywordsRequest xmlns=\"http://www.ebay.com/marketplace/search/v1/services\">\n";
    $xmlrequest .= "<keywords>";
    $xmlrequest .= $query;
    $xmlrequest .= "</keywords>\n";
    $xmlrequest .= $xmlfilter;
    $xmlrequest .= "<paginationInput>\n <entriesPerPage>3</entriesPerPage>\n</paginationInput>\n";
    $xmlrequest .= "</findItemsByKeywordsRequest>";

    // Set up the HTTP headers
    $headers = array(
        'X-EBAY-SOA-OPERATION-NAME: findItemsByKeywords',
        'X-EBAY-SOA-SERVICE-VERSION: ' . $version,
        'X-EBAY-SOA-REQUEST-DATA-FORMAT: XML',
        'X-EBAY-SOA-GLOBAL-ID: ' . $globalid,
        'X-EBAY-SOA-SECURITY-APPNAME: ' . $appid,
        'Content-Type: text/xml;charset=utf-8',
    );

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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/plugins/marketplaces/ebay/samples/php/GettingStarted_PHP_XML_XML/class.tx_deal_piMarketplacesEbaySamplesPhpGettingstarted.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/deal/plugins/marketplaces/ebay/samples/php/GettingStarted_PHP_XML_XML/class.tx_deal_piMarketplacesEbaySamplesPhpGettingstarted.php']);
}
